<?php
// update-public-status.php
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$userID = $_POST['userID'] ?? null;
$isPublic = isset($_POST['isPublic']) ? intval($_POST['isPublic']) : 0;

if (empty($userID) || !is_numeric($userID)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid user ID'
    ]);
    exit;
}

try {
    // ตรวจสอบว่าเคยเผยแพร่หรือยัง จากตาราง profile
    $stmt = $conn->prepare("SELECT isEverPublic FROM profile WHERE userID = :userID");
    $stmt->execute(['userID' => $userID]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        echo json_encode([
            'status' => 0,
            'message' => 'Profile not found'
        ]);
        exit;
    }

    $currentIsEverPublic = intval($userData['isEverPublic']);
    $justPublished = false;
    
    // ถ้าเปิดเผยแพร่ (isPublic = 1) และยังไม่เคยเผยแพร่มาก่อน (isEverPublic = 0)
    // ให้อัปเดต isEverPublic เป็น 1 และตั้งค่า justPublished = true
    $newIsEverPublic = $currentIsEverPublic;
    if ($isPublic === 1 && $currentIsEverPublic === 0) {
        $newIsEverPublic = 1;
        $justPublished = true;
    }

    // อัปเดตสถานะในตาราง profile
    $updateStmt = $conn->prepare("
        UPDATE profile 
        SET isPublic = :isPublic, 
            isEverPublic = :isEverPublic 
        WHERE userID = :userID
    ");
    
    $updateStmt->execute([
        'isPublic' => $isPublic,
        'isEverPublic' => $newIsEverPublic,
        'userID' => $userID
    ]);

    echo json_encode([
        'status' => 1,
        'message' => 'Status updated successfully',
        'isPublic' => $isPublic,
        'isEverPublic' => $newIsEverPublic,
        'justPublished' => $justPublished
    ]);

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode([
        'status' => 0,
        'message' => 'Database error occurred'
    ]);
}
?>