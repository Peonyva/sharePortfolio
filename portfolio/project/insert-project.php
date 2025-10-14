<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $userID = isset($_POST['userID']) ? intval($_POST['userID']) : 0;
    $projectTitle = isset($_POST['projectTitle']) ? trim($_POST['projectTitle']) : '';
    $keyPoint = isset($_POST['keyPoint']) ? trim($_POST['keyPoint']) : '';
    $myProjectSkills = isset($_POST['myProjectSkills']) ? trim($_POST['myProjectSkills']) : '';
    
    // Validation
    if (empty($userID) || empty($projectTitle) || empty($keyPoint) || empty($myProjectSkills)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    // ตรวจสอบไฟล์รูปภาพ
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
    
    // ตรวจสอบประเภทไฟล์
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode([
            'status' => 0,
            'message' => 'Only JPG, PNG, and GIF images are allowed.'
        ]);
        exit;
    }
    
    // ตรวจสอบขนาดไฟล์
    if ($file['size'] > $maxSize) {
        echo json_encode([
            'status' => 0,
            'message' => 'Image size must not exceed 10MB.'
        ]);
        exit;
    }
    
    try {
        // หา sortOrder สูงสุด
        $sqlMaxSort = "SELECT COALESCE(MAX(sortOrder), 0) + 1 AS newSort FROM projects WHERE userID = :userID";
        $stmtMaxSort = $conn->prepare($sqlMaxSort);
        $stmtMaxSort->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtMaxSort->execute();
        $newSort = $stmtMaxSort->fetch(PDO::FETCH_ASSOC)['newSort'];
        
        // สร้างชื่อไฟล์ใหม่
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'project_' . $userID . '_' . time() . '.' . $extension;
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $newFileName;
        
        // อัพโหลดไฟล์
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to upload image.'
            ]);
            exit;
        }
        
        // เก็บ path สำหรับบันทึกในฐานข้อมูล
        $projectImagePath = '/uploads/project/' . $newFileName;
        
        // Insert ข้อมูล
        $sql = "INSERT INTO project (userID, projectTitle, projectImagePath, keyPoint, skills, sortOrder) 
                VALUES (:userID, :projectTitle, :projectImagePath, :keyPoint, :skills, :sortOrder)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':projectTitle', $projectTitle);
        $stmt->bindParam(':projectImagePath', $projectImagePath);
        $stmt->bindParam(':keyPoint', $keyPoint);
        $stmt->bindParam(':skills', $myProjectSkills);
        $stmt->bindParam(':sortOrder', $newSort, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 1,
                'message' => 'Project saved successfully!'
            ]);
        } else {
            // ลบไฟล์ถ้า insert ไม่สำเร็จ
            unlink($uploadPath);
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to save project.'
            ]);
        }
        
    } catch (PDOException $e) {
        // ลบไฟล์ถ้าเกิด error
        if (isset($uploadPath) && file_exists($uploadPath)) {
            unlink($uploadPath);
        }
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