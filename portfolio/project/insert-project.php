<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userID = isset($_POST['userID']) ? intval($_POST['userID']) : 0;
    $projectTitle = isset($_POST['projectTitle']) ? trim($_POST['projectTitle']) : '';
    $keyPoint = isset($_POST['keyPoint']) ? trim($_POST['keyPoint']) : '';
    $myProjectSkills = isset($_POST['myProjectSkills']) ? $_POST['myProjectSkills'] : '';

    // ✅ Validation
    if (empty($userID) || empty($projectTitle) || empty($keyPoint) || empty($myProjectSkills)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }

    // ✅ แปลง skill ให้เป็น array
    if (!is_array($myProjectSkills)) {
        $decoded = json_decode($myProjectSkills, true);
        $myProjectSkills = $decoded ?: explode(',', $myProjectSkills);
    }

    // ✅ ตรวจสอบไฟล์อัปโหลด
    if (!isset($_FILES['projectImage']) || $_FILES['projectImage']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 0,
            'message' => 'Project image is required.'
        ]);
        exit;
    }

    $file = $_FILES['projectImage'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    // ✅ ตรวจขนาดไฟล์
    if ($file['size'] > $maxSize) {
        echo json_encode([
            'status' => 0,
            'message' => 'Image size must not exceed 10MB.'
        ]);
        exit;
    }

    // ✅ ตรวจชนิดไฟล์จริง (ปลอดภัยกว่า)
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

    // ✅ สร้างชื่อไฟล์ใหม่
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'project_' . $userID . '_' . time() . '.' . $extension;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $uploadPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Failed to upload image.'
        ]);
        exit;
    }

    $projectImagePath = '/uploads/projects/' . $newFileName;

    try {
        // ✅ หา sortOrder ล่าสุดจาก project table (ไม่ใช่ education)
        $sqlMaxSort = "SELECT COALESCE(MAX(sortOrder), 0) + 1 AS newSort FROM project WHERE userID = :userID";
        $stmtMaxSort = $conn->prepare($sqlMaxSort);
        $stmtMaxSort->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtMaxSort->execute();
        $newSort = $stmtMaxSort->fetch(PDO::FETCH_ASSOC)['newSort'];

        // ✅ Insert project
        $sqlProject = "INSERT INTO project (userID, projectTitle, projectImage, keyPoint, sortOrder)
                       VALUES (:userID, :projectTitle, :projectImage, :keyPoint, :sortOrder)";
        $stmtProject = $conn->prepare($sqlProject);
        $stmtProject->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtProject->bindParam(':projectTitle', $projectTitle);
        $stmtProject->bindParam(':projectImage', $projectImagePath);
        $stmtProject->bindParam(':keyPoint', $keyPoint);
        $stmtProject->bindParam(':sortOrder', $newSort, PDO::PARAM_INT);
        $stmtProject->execute();

        $projectID = $conn->lastInsertId();

        // ✅ Insert project skills
        $sqlSkill = "INSERT INTO projectSkill (projectID, skillsID) VALUES (:projectID, :skillsID)";
        $stmtSkill = $conn->prepare($sqlSkill);

        foreach ($myProjectSkills as $skillID) {
            $skillID = intval($skillID);
            if ($skillID > 0) {
                $stmtSkill->bindParam(':projectID', $projectID, PDO::PARAM_INT);
                $stmtSkill->bindParam(':skillsID', $skillID, PDO::PARAM_INT);
                $stmtSkill->execute();
            }
        }

        echo json_encode([
            'status' => 1,
            'message' => 'Project saved successfully!'
        ]);
    } catch (PDOException $e) {
        // ลบไฟล์ออกหาก insert ล้มเหลว
        if (isset($uploadPath) && file_exists($uploadPath)) unlink($uploadPath);
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
