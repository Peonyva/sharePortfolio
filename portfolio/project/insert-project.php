<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userID = isset($_POST['userID']) ? intval($_POST['userID']) : 0;
    $projectTitle = isset($_POST['projectTitle']) ? trim($_POST['projectTitle']) : '';
    $keyPoint = isset($_POST['keyPoint']) ? trim($_POST['keyPoint']) : '';
    $myProjectSkills = isset($_POST['myProjectSkills']) ? $_POST['myProjectSkills'] : '';

    // Validation
    if (empty($userID) || empty($projectTitle) || empty($keyPoint) || empty($myProjectSkills)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }

    if (!is_array($myProjectSkills)) {
        $decoded = json_decode($myProjectSkills, true);
        if ($decoded) {
            $myProjectSkills = $decoded;
        } else {
            $myProjectSkills = explode(',', $myProjectSkills);
        }
    }

    if (!isset($_FILES['projectImage']) || $_FILES['projectImage']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 0,
            'message' => 'Project image is required.'
        ]);
        exit;
    }

    $file = $_FILES['projectImage'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 10485760; // 10MB

    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Only JPG, PNG, and GIF images are allowed.'
        ]);
        exit;
    }

    if ($file['size'] > $maxSize) {
        echo json_encode([
            'status' => 0,
            'message' => 'Image size must not exceed 10MB.'
        ]);
        exit;
    }

    // ✅ สร้างชื่อไฟล์ใหม่
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'project_' . $userID . '_' . time() . '.' . $extension;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $uploadPath = $uploadDir . $newFileName;
    move_uploaded_file($file['tmp_name'], $uploadPath);

    $projectImagePath = '/uploads/projects/' . $newFileName;

    try {
        // หา sortOrder สูงสุด
        $sqlMaxSort = "SELECT COALESCE(MAX(sortOrder), 0) + 1 AS newSort FROM education WHERE userID = :userID";
        $stmtMaxSort = $conn->prepare($sqlMaxSort);
        $stmtMaxSort->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtMaxSort->execute();
        $newSort = $stmtMaxSort->fetch(PDO::FETCH_ASSOC)['newSort'];

        // ✅ Insert ข้อมูล project
        $sqlProject = "INSERT INTO project (userID, projectTitle, projectImage, keyPoint)
                       VALUES (:userID, :projectTitle, :projectImage, :keyPoint)";
        $stmtProject = $conn->prepare($sqlProject);
        $stmtProject->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtProject->bindParam(':projectTitle', $projectTitle);
        $stmtProject->bindParam(':projectImage', $projectImagePath);
        $stmtProject->bindParam(':keyPoint', $keyPoint);
        $stmtProject->execute();

        $projectID = $conn->lastInsertId();

        // ✅ Insert ข้อมูล skills
        $sqlSkill = "INSERT INTO projectSkill (projectID, skillsID) VALUES (:projectID, :skillsID)";
        $stmtSkill = $conn->prepare($sqlSkill);

        foreach ($myProjectSkills as $skillID) {
            $stmtSkill->bindParam(':projectID', $projectID, PDO::PARAM_INT);
            $stmtSkill->bindParam(':skillsID', $skillID, PDO::PARAM_INT);
            $stmtSkill->execute();
        }

        echo json_encode([
            'status' => 1,
            'message' => 'Project saved successfully!'
        ]);
    } catch (PDOException $e) {
        // ถ้า insert skill ล้มเหลว อาจจะมี project อยู่แล้ว
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
