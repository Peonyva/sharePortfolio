<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ตรวจสอบ ID
    if (empty($_POST['id']) || empty($_POST['userID'])) {
        echo json_encode([
            'status' => 0,
            'message' => 'Missing Project ID or User ID.'
        ]);
        exit;
    }
    
    $id = intval($_POST['id']);
    $userID = intval($_POST['userID']);
    
    try {
        // ✅ ดึงข้อมูลเก่าจากฐานข้อมูล
        $sqlSelect = "SELECT * FROM projects WHERE id = :id AND userID = :userID";
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
        
        // ✅ ใช้ข้อมูลเก่าถ้าไม่ได้ส่งค่ามา
        $projectTitle = !empty($_POST['projectTitle']) ? trim($_POST['projectTitle']) : $oldData['projectTitle'];
        $keyPoint = !empty($_POST['keyPoint']) ? trim($_POST['keyPoint']) : $oldData['keyPoint'];
        $skills = !empty($_POST['myProjectSkills']) ? trim($_POST['myProjectSkills']) : $oldData['skills'];
        $projectImagePath = $oldData['projectImagePath']; // เริ่มต้นใช้รูปเก่า
        
        // ✅ ตรวจสอบว่ามีการอัพโหลดรูปใหม่หรือไม่
        if (isset($_FILES['projectImage']) && $_FILES['projectImage']['error'] === UPLOAD_ERR_OK) {
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
            
            // สร้างชื่อไฟล์ใหม่
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'project_' . $userID . '_' . time() . '.' . $extension;
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . $newFileName;
            
            // อัพโหลดไฟล์ใหม่
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // ลบไฟล์เก่า
                $oldFilePath = $_SERVER['DOCUMENT_ROOT'] . $oldData['projectImagePath'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
                
                // อัพเดท path ใหม่
                $projectImagePath = '/uploads/project/' . $newFileName;
            } else {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Failed to upload new image.'
                ]);
                exit;
            }
        }
        
        // Validation
        if (empty($projectTitle) || empty($keyPoint) || empty($skills)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }
        
        // ✅ Update ข้อมูล
        $sql = "UPDATE project SET 
                projectTitle = :projectTitle,
                projectImagePath = :projectImagePath,
                keyPoint = :keyPoint,
                skills = :skills,
                updatedAt = NOW()
                WHERE id = :id AND userID = :userID";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':projectTitle', $projectTitle);
        $stmt->bindParam(':projectImagePath', $projectImagePath);
        $stmt->bindParam(':keyPoint', $keyPoint);
        $stmt->bindParam(':skills', $skills);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'status' => 1,
                    'message' => 'Project updated successfully!'
                ]);
            } else {
                echo json_encode([
                    'status' => 1,
                    'message' => 'No changes detected. Data remains the same.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to update project.'
            ]);
        }
        
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