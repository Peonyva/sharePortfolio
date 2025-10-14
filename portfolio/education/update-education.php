<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (empty($_POST['id']) || empty($_POST['userID'])) {
        echo json_encode(['status' => 0, 'message' => 'Missing Education ID or User ID.']);
        exit;
    }
    
    $id = intval($_POST['id']);
    $userID = intval($_POST['userID']);
    
    try {
        // ✅ ดึงข้อมูลเก่า
        $sqlSelect = "SELECT * FROM education WHERE id = :id AND userID = :userID";
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtSelect->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtSelect->execute();
        
        $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        
        if (!$oldData) {
            echo json_encode(['status' => 0, 'message' => 'Education not found.']);
            exit;
        }
        
        // ✅ ใช้ข้อมูลเก่าถ้าไม่ได้ส่งมา
        $educationName = !empty($_POST['educationName']) ? trim($_POST['educationName']) : $oldData['educationName'];
        $degree = !empty($_POST['degree']) ? trim($_POST['degree']) : $oldData['degree'];
        $facultyName = !empty($_POST['facultyName']) ? trim($_POST['facultyName']) : $oldData['facultyName'];
        $majorName = !empty($_POST['majorName']) ? trim($_POST['majorName']) : $oldData['majorName'];
        $startDate = !empty($_POST['startDate']) ? trim($_POST['startDate']) : $oldData['startDate'];
        $isCurrent = isset($_POST['isCurrent']) ? intval($_POST['isCurrent']) : intval($oldData['isCurrent']);
        
        if ($isCurrent == 1) {
            $endDate = null;
        } else {
            $endDate = isset($_POST['endDate']) && !empty($_POST['endDate']) 
                       ? trim($_POST['endDate']) 
                       : $oldData['endDate'];
        }
        
        $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : $oldData['remarks'];
        
        // Validation
        if (empty($educationName) || empty($degree) || empty($facultyName) || empty($majorName) || empty($startDate)) {
            echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields.']);
            exit;
        }
        
        if ($isCurrent == 0 && !empty($endDate)) {
            if (strtotime($endDate) <= strtotime($startDate)) {
                echo json_encode(['status' => 0, 'message' => 'End Date must be after Start Date.']);
                exit;
            }
            
            if (strtotime($endDate) > time()) {
                echo json_encode(['status' => 0, 'message' => 'End Date cannot be in the future.']);
                exit;
            }
        }
        
        if (strtotime($startDate) > time()) {
            echo json_encode(['status' => 0, 'message' => 'Start Date cannot be in the future.']);
            exit;
        }
        
        // ✅ Update
        $sql = "UPDATE education SET 
                educationName = :educationName,
                degree = :degree,
                facultyName = :facultyName,
                majorName = :majorName,
                startDate = :startDate,
                endDate = :endDate,
                isCurrent = :isCurrent,
                remarks = :remarks,
                updatedAt = NOW()
                WHERE id = :id AND userID = :userID";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':educationName', $educationName);
        $stmt->bindParam(':degree', $degree);
        $stmt->bindParam(':facultyName', $facultyName);
        $stmt->bindParam(':majorName', $majorName);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':isCurrent', $isCurrent, PDO::PARAM_INT);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 1, 'message' => 'Education updated successfully!']);
            } else {
                echo json_encode(['status' => 1, 'message' => 'No changes detected.']);
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to update education.']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 0, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
}

$conn = null;
?>