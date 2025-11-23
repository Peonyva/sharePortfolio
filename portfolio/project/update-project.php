<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. ตรวจสอบ Input หลัก
if (empty($_POST['id']) || empty($_POST['userID'])) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Missing Project ID or User ID.']);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    // 3. ดึงข้อมูลเก่า (เพื่อเช็คสิทธิ์ และเอารูปเก่ามาเตรียมลบ)
    $sqlSelect = "SELECT projectImage FROM project WHERE projectID = :id AND userID = :userID";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->execute([':id' => $id, ':userID' => $userID]);
    $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        http_response_code(404);
        echo json_encode(['status' => 0, 'message' => 'Project not found or access denied.']);
        exit;
    }

    // 4. Validate Data Fields
    $projectTitle = !empty($_POST['projectTitle']) ? trim($_POST['projectTitle']) : '';
    $keyPoint = !empty($_POST['keyPoint']) ? trim($_POST['keyPoint']) : '';

    if (empty($projectTitle) || empty($keyPoint)) {
        echo json_encode(['status' => 0, 'message' => 'Project title and Key Point are required.']);
        exit;
    }

    // 5. Validate Skills
    $skillIds = [];
    if (isset($_POST['myProjectSkills'])) {
        $inputSkills = $_POST['myProjectSkills'];
        // รองรับทั้ง Array และ JSON String
        if (!is_array($inputSkills)) {
            $decoded = json_decode($inputSkills, true);
            $inputSkills = $decoded ?: (strpos($inputSkills, ',') !== false ? explode(',', $inputSkills) : [$inputSkills]);
        }
        
        // กรองเอาเฉพาะตัวเลข
        foreach ($inputSkills as $s) {
            $val = intval($s);
            if ($val > 0) $skillIds[] = $val;
        }
    }

    if (empty($skillIds)) {
        echo json_encode(['status' => 0, 'message' => 'At least one skill is required.']);
        exit;
    }

    // เช็คว่า Skills มีอยู่จริงใน DB (Optional: แต่ทำไว้ก็ดีครับ)
    $placeholders = implode(',', array_fill(0, count($skillIds), '?'));
    $sqlCheck = "SELECT COUNT(*) as count FROM skills WHERE skillsID IN ($placeholders)";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute($skillIds);
    $checkResult = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($checkResult['count'] != count($skillIds)) {
        echo json_encode(['status' => 0, 'message' => 'Invalid Skill IDs detected.']);
        exit;
    }

    // 6. จัดการรูปภาพ (Image Handling)
    $projectImagePath = $oldData['projectImage']; // ค่าเริ่มต้นคือรูปเดิม

    if (isset($_FILES['projectImage']) && $_FILES['projectImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['projectImage'];
        
        // Validate Size & Type
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['status' => 0, 'message' => 'Image size must not exceed 10MB.']);
            exit;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            echo json_encode(['status' => 0, 'message' => 'Invalid image format.']);
            exit;
        }

        // Upload New Image
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'proj_' . $userID . '_' . uniqid() . '.' . $extension; // ใช้ uniqid
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';
        
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // อัปโหลดสำเร็จ -> อัปเดต path
            $projectImagePath = '/uploads/projects/' . $newFileName;

            // ลบรูปเก่า (Secure Delete)
            if (!empty($oldData['projectImage'])) {
                $oldFilePath = realpath($_SERVER['DOCUMENT_ROOT'] . $oldData['projectImage']);
                $baseDir = realpath($uploadDir); // ต้องอยู่ในโฟลเดอร์ uploads/projects เท่านั้น

                if ($oldFilePath && $baseDir && strpos($oldFilePath, $baseDir) === 0 && file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to upload image.']);
            exit;
        }
    }

    // 7. Update Database
    // 7.1 Update Table project
    $sqlUpdate = "UPDATE project SET 
                  projectTitle = :projectTitle,
                  projectImage = :projectImage,
                  keyPoint = :keyPoint
                  WHERE projectID = :id AND userID = :userID";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ':projectTitle' => $projectTitle,
        ':projectImage' => $projectImagePath,
        ':keyPoint' => $keyPoint,
        ':id' => $id,
        ':userID' => $userID
    ]);

    // 7.2 Reset Skills (ลบเก่า -> ลงใหม่)
    $sqlDeleteSkill = "DELETE FROM projectSkill WHERE projectID = :projectID";
    $stmtDelete = $conn->prepare($sqlDeleteSkill);
    $stmtDelete->execute([':projectID' => $id]);

    $sqlInsertSkill = "INSERT INTO projectSkill (projectID, skillsID) VALUES (:projectID, :skillsID)";
    $stmtInsert = $conn->prepare($sqlInsertSkill);

    foreach ($skillIds as $sid) {
        $stmtInsert->execute([
            ':projectID' => $id,
            ':skillsID' => $sid
        ]);
    }

    echo json_encode(['status' => 1, 'message' => 'Project updated successfully!']);

} catch (PDOException $e) {
    // บันทึก Error ลง Log Server ไม่ใช่ส่งให้ User
    error_log("Update Project Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Database Error occurred.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'System Error occurred.']);
}

$conn = null;
?>