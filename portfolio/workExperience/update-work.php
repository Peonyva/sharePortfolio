<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// รับข้อมูลจาก AJAX
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$companyName = isset($_POST['companyName']) ? trim($_POST['companyName']) : '';
$employeeType = isset($_POST['employeeType']) ? trim($_POST['employeeType']) : '';
$position = isset($_POST['position']) ? trim($_POST['position']) : '';
$startDate = isset($_POST['startDate']) ? trim($_POST['startDate']) : '';
$endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : null;
$isCurrent = isset($_POST['isCurrent']) ? intval($_POST['isCurrent']) : 0;
$jobDescription = isset($_POST['jobDescription']) ? trim($_POST['jobDescription']) : '';
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

// Validation
if ($id <= 0) {
    echo json_encode(['status' => 0, 'message' => 'Invalid ID']);
    exit;
}

if (empty($companyName) || empty($employeeType) || empty($position) || empty($startDate) || empty($jobDescription)) {
    echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields']);
    exit;
}

// ถ้าเลือก "Currently working here" ให้ endDate เป็น NULL
if ($isCurrent == 1) {
    $endDate = null;
}

// ตรวจสอบว่า endDate มากกว่า startDate หรือไม่
if (!$isCurrent && !empty($endDate) && strtotime($endDate) < strtotime($startDate)) {
    echo json_encode(['status' => 0, 'message' => 'End Date must be after Start Date']);
    exit;
}

try {
    // SQL Update
    $sql = "UPDATE workexperience SET 
            companyName = :companyName,
            employeeType = :employeeType,
            position = :position,
            startDate = :startDate,
            endDate = :endDate,
            isCurrent = :isCurrent,
            jobDescription = :jobDescription,
            remarks = :remarks,
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':companyName', $companyName);
    $stmt->bindParam(':employeeType', $employeeType);
    $stmt->bindParam(':position', $position);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->bindParam(':isCurrent', $isCurrent);
    $stmt->bindParam(':jobDescription', $jobDescription);
    $stmt->bindParam(':remarks', $remarks);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 1,
            'message' => 'Work Experience updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 0,
            'message' => 'Failed to update Work Experience'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}

$conn = null;
?>