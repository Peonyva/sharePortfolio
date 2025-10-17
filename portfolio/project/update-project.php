<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

try {
    // ✅ ตรวจสอบ Method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(400);
        echo json_encode([
            'status' => 0,
            'message' => 'Invalid request method.'
        ]);
        exit;
    }

    // ✅ ตรวจสอบ Input
    if (empty($_POST['id']) || empty($_POST['userID'])) {
        http_response_code(400);
        echo json_encode([
            'status' => 0,
            'message' => 'Missing Project ID or User ID.'
        ]);
        exit;
    }

    $id = intval($_POST['id']);
    $userID = intval($_POST['userID']);

    // ========================================
    // ✅ ดึงข้อมูลเก่า
    // ========================================
    $sqlSelect = "SELECT * FROM project WHERE projectID = :id AND userID = :userID";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtSelect->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmtSelect->execute();

    $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        http_response_code(404);
        echo json_encode([
            'status' => 0,
            'message' => 'Project not found or does not belong to this user.'
        ]);
        exit;
    }

    // ========================================
    // ✅ Validate Input
    // ========================================
    $projectTitle = !empty($_POST['projectTitle']) ? trim($_POST['projectTitle']) : '';
    $keyPoint = !empty($_POST['keyPoint']) ? trim($_POST['keyPoint']) : '';

    if (empty($projectTitle)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Project title is required.'
        ]);
        exit;
    }

    if (empty($keyPoint)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Job description is required.'
        ]);
        exit;
    }

    // ========================================
    // ✅ ประมวลผล Skills IDs
    // ========================================
    $skillIds = [];
    if (isset($_POST['myProjectSkills'])) {
        $inputSkills = $_POST['myProjectSkills'];

        if (is_array($inputSkills)) {
            $skillIds = array_map('intval', $inputSkills);
        } else {
            $decoded = json_decode($inputSkills, true);
            if (is_array($decoded)) {
                $skillIds = array_map('intval', $decoded);
            }
        }
    }

    if (empty($skillIds)) {
        echo json_encode(['status' => 0, 'message' => 'At least one skill is required.']);
        exit;
    }

    // ✅ ตรวจสอบว่า skill IDs มีอยู่จริง
    // ⚠️ ตรวจสอบให้แน่ใจว่าชื่อ table และ column ถูกต้อง
    $placeholders = implode(',', array_fill(0, count($skillIds), '?'));
    $sqlCheckSkills = "SELECT COUNT(*) as count FROM skills WHERE skillsID IN ($placeholders)";
    $stmtCheck = $conn->prepare($sqlCheckSkills);
    $stmtCheck->execute($skillIds);
    $checkResult = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($checkResult['count'] != count($skillIds)) {
        echo json_encode(['status' => 0, 'message' => 'Some skill IDs are invalid.']);
        exit;
    }

    // ========================================
    // ✅ จัดการรูปภาพ
    // ========================================
    $projectImagePath = $oldData['projectImage'];

    if (isset($_FILES['projectImage']) && $_FILES['projectImage']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['projectImage'];
        $maxSize = 10 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            echo json_encode([
                'status' => 0,
                'message' => 'Image size must not exceed 10MB.'
            ]);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mimeType, $allowedMimeTypes)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Only JPG, PNG, and GIF images are allowed.'
            ]);
            exit;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = 'project_' . $userID . '_' . time() . '.' . $extension;
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to upload new image.'
            ]);
            exit;
        }

        // ✅ ลบรูปเก่า
        $oldImagePath = $oldData['projectImage'];
        if (!empty($oldImagePath)) {
            $oldFilePath = $_SERVER['DOCUMENT_ROOT'] . $oldImagePath;
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
        }

        $projectImagePath = '/uploads/projects/' . $newFileName;
    }

    // ========================================
    // ✅ UPDATE DATABASE
    // ========================================

    // 1. Update project
    $sqlUpdate = "UPDATE project 
                  SET projectTitle = :projectTitle,
                      projectImage = :projectImage,
                      keyPoint = :keyPoint
                  WHERE projectID = :id AND userID = :userID";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':projectTitle', $projectTitle);
    $stmtUpdate->bindParam(':projectImage', $projectImagePath);
    $stmtUpdate->bindParam(':keyPoint', $keyPoint);
    $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtUpdate->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmtUpdate->execute();

    // 2. Delete old skills 
    $sqlDeleteSkill = "DELETE FROM projectSkill WHERE projectID = :projectID";
    $stmtDelete = $conn->prepare($sqlDeleteSkill);
    $stmtDelete->bindParam(':projectID', $id, PDO::PARAM_INT);
    $stmtDelete->execute();

    // 3. Insert new skills
    $sqlInsertSkill = "INSERT INTO projectSkill (projectID, skillsID) VALUES (:projectID, :skillsID)";
    $stmtInsert = $conn->prepare($sqlInsertSkill);

    foreach ($skillIds as $skillId) {
        $stmtInsert->execute([
            ':projectID' => $id,
            ':skillsID' => intval($skillId)
        ]);
    }

    // ✅ Success
    echo json_encode([
        'status' => 1,
        'message' => 'Project updated successfully!'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn = null;
?>