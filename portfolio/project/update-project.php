<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ ตรวจสอบค่าที่ส่งมา
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $userID = isset($_POST['userID']) ? intval($_POST['userID']) : 0;

    if (empty($id) || empty($userID)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Missing Project ID or User ID.'
        ]);
        exit;
    }

    try {
        // ✅ ดึงข้อมูลเก่าจากฐานข้อมูล
        $sqlSelect = "SELECT * FROM project WHERE projectID = :id AND userID = :userID";
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtSelect->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtSelect->execute();

        $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if (!$oldData) {
            echo json_encode([
                'status' => 0,
                'message' => 'Project not found or does not belong to this user.'
            ]);
            exit;
        }

        // ✅ ใช้ข้อมูลเก่า ถ้าไม่มีการส่งค่ามาใหม่
        $projectTitle = !empty($_POST['projectTitle']) ? trim($_POST['projectTitle']) : $oldData['projectTitle'];
        $keyPoint = !empty($_POST['keyPoint']) ? trim($_POST['keyPoint']) : $oldData['keyPoint'];
        $myProjectSkills = isset($_POST['myProjectSkills']) ? $_POST['myProjectSkills'] : [];

        if (!is_array($myProjectSkills)) {
            $decoded = json_decode($myProjectSkills, true);
            $myProjectSkills = $decoded ?: explode(',', $myProjectSkills);
        }

        $projectImagePath = $oldData['projectImage']; // เริ่มต้นใช้รูปเดิม

        // ✅ ตรวจสอบว่ามีการอัปโหลดรูปใหม่หรือไม่
        if (isset($_FILES['projectImage']) && $_FILES['projectImage']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['projectImage'];
            $maxSize = 10 * 1024 * 1024; // 10MB

            // ตรวจขนาดไฟล์
            if ($file['size'] > $maxSize) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Image size must not exceed 10MB.'
                ]);
                exit;
            }

            // ตรวจ MIME type จริง
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

            // สร้างชื่อไฟล์ใหม่
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = 'project_' . $userID . '_' . time() . '.' . $extension;
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $uploadPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // ลบไฟล์เก่า
                $oldFilePath = $_SERVER['DOCUMENT_ROOT'] . $oldData['projectImage'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                $projectImagePath = '/uploads/projects/' . $newFileName;
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Failed to upload new image.'
                ]);
                exit;
            }
        }

        // ✅ ตรวจสอบค่าที่จำเป็น
        if (empty($projectTitle) || empty($keyPoint)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        // ✅ อัปเดตข้อมูล project
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

        // ✅ อัปเดต skills (ลบเก่าก่อน)
        $sqlDeleteSkill = "DELETE FROM projectSkill WHERE projectID = :projectID";
        $stmtDelete = $conn->prepare($sqlDeleteSkill);
        $stmtDelete->bindParam(':projectID', $id, PDO::PARAM_INT);
        $stmtDelete->execute();

        $sqlInsertSkill = "INSERT INTO projectSkill (projectID, skillsID) VALUES (:projectID, :skillsID)";
        $stmtInsert = $conn->prepare($sqlInsertSkill);
        foreach ($myProjectSkills as $skillID) {
            $skillID = intval($skillID);
            if ($skillID > 0) {
                $stmtInsert->bindParam(':projectID', $id, PDO::PARAM_INT);
                $stmtInsert->bindParam(':skillsID', $skillID, PDO::PARAM_INT);
                $stmtInsert->execute();
            }
        }

        echo json_encode([
            'status' => 1,
            'message' => 'Project updated successfully!'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 0,
            'message' => 'Database Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid request method.'
    ]);
}

$conn = null;
?>
