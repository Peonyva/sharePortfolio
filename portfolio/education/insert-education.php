<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $userID = isset($_POST['userID']) ? intval($_POST['userID']) : 0;
    $educationName = isset($_POST['educationName']) ? trim($_POST['educationName']) : '';
    $degree = isset($_POST['degree']) ? trim($_POST['degree']) : '';
    $facultyName = isset($_POST['facultyName']) ? trim($_POST['facultyName']) : '';
    $majorName = isset($_POST['majorName']) ? trim($_POST['majorName']) : '';
    $startDate = isset($_POST['startDate']) ? trim($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : null;
    $isCurrent = isset($_POST['isCurrent']) ? intval($_POST['isCurrent']) : 0;
    $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
    
    // Validation
    if (empty($userID) || empty($educationName) || empty($degree) || empty($facultyName) || empty($majorName) || empty($startDate)) {
        echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    
    if ($isCurrent == 1) {
        $endDate = null;
    }
    
    if ($isCurrent == 0 && empty($endDate)) {
        echo json_encode(['status' => 0, 'message' => 'Please select an End Date or check "Currently studying here".']);
        exit;
    }
    
    if (!$isCurrent && !empty($endDate) && strtotime($endDate) <= strtotime($startDate)) {
        echo json_encode(['status' => 0, 'message' => 'End Date must be after Start Date.']);
        exit;
    }
    
    try {
        // หา sortOrder สูงสุด
        $sqlMaxSort = "SELECT COALESCE(MAX(sortOrder), 0) + 1 AS newSort FROM education WHERE userID = :userID";
        $stmtMaxSort = $conn->prepare($sqlMaxSort);
        $stmtMaxSort->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtMaxSort->execute();
        $newSort = $stmtMaxSort->fetch(PDO::FETCH_ASSOC)['newSort'];
        
        // Insert ข้อมูล
        $sql = "INSERT INTO education (userID, educationName, degree, facultyName, majorName, startDate, endDate, isCurrent, remark, sortOrder) 
                VALUES (:userID, :educationName, :degree, :facultyName, :majorName, :startDate, :endDate, :isCurrent, :remark, :sortOrder)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':educationName', $educationName);
        $stmt->bindParam(':degree', $degree);
        $stmt->bindParam(':facultyName', $facultyName);
        $stmt->bindParam(':majorName', $majorName);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':isCurrent', $isCurrent, PDO::PARAM_INT);
        $stmt->bindParam(':remark', $remark);
        $stmt->bindParam(':sortOrder', $newSort, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 1, 'message' => 'Education saved successfully!']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to save education.']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 0, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
}

$conn = null;
?>