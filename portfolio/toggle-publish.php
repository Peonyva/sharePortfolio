<?php
header('Content-Type: application/json');
session_start(); // เพิ่มการ start session
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// ตรวจสอบ Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
    exit;
}

// ตรวจสอบ Required Data
if (!isset($_POST['isPublic']) || !isset($_POST['userID'])) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Missing required data.']);
    exit;
}

$isPublic = intval($_POST['isPublic']);
$userID = intval($_POST['userID']);


try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ตรวจสอบ Profile
    $sqlCheck = "SELECT isEverPublic FROM profile WHERE userID = :userID";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmtCheck->execute();
    $profile = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        http_response_code(404);
        echo json_encode(['status' => 0, 'message' => 'Profile not found.']);
        exit;
    }

    // กำหนด updateFields
    $updateFields = ['isPublic = :isPublic'];
    
    if ($isPublic === 1 && intval($profile['isEverPublic']) === 0) {
        $updateFields[] = 'isEverPublic = 1';
    }

    // UPDATE
    $sqlUpdate = "UPDATE profile SET " . implode(', ', $updateFields) . " WHERE userID = :userID";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':isPublic', $isPublic, PDO::PARAM_INT);
    $stmtUpdate->bindParam(':userID', $userID, PDO::PARAM_INT);

    if ($stmtUpdate->execute()) {
        echo json_encode([
            'status' => 1,
            'message' => 'Status updated successfully',
            'newStatus' => $isPublic
        ]);
    } else {
        throw new Exception('Failed to update database');
    }

} catch (PDOException $e) {
    error_log($e->getMessage()); // บันทึก log
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Database error']);
}

$conn = null;
?>
