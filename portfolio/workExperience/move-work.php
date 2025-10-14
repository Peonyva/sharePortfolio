<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $currentId   = intval($_POST['currentId'] ?? 0);
    $currentSort = intval($_POST['currentSort'] ?? 0);
    $newSort     = intval($_POST['newSort'] ?? 0);
    $userID      = intval($_POST['userID'] ?? 0);

    if ($currentId <= 0 || $userID <= 0) {
        throw new Exception("Invalid parameters.");
    }

    if ($currentSort <= 0 || $newSort <= 0) {
        throw new Exception("Invalid sort order values.");
    }

    if ($currentSort === $newSort) {
        throw new Exception("No changes detected.");
    }

    // หา record ที่ sortOrder = newSort
    $stmt = $conn->prepare("SELECT id FROM workexperience WHERE userID = :userID AND sortOrder = :newSort LIMIT 1");
    $stmt->execute([
        ':userID' => $userID,
        ':newSort' => $newSort
    ]);
    $swapItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$swapItem) {
        throw new Exception("Target position not found.");
    }

    $swapId = intval($swapItem['id']);

    if ($swapId === $currentId) {
        throw new Exception("Cannot swap with the same record.");
    }

    // 🔹 สลับ sortOrder ระหว่างสอง record
    // 1) ตั้งค่าของ record ปลายทางเป็น sort เดิมของ current
    $stmt = $conn->prepare("UPDATE workexperience SET sortOrder = :currentSort WHERE id = :swapId AND userID = :userID");
    $stmt->execute([
        ':currentSort' => $currentSort,
        ':swapId' => $swapId,
        ':userID' => $userID
    ]);

    // 2) ตั้งค่าของ record ปัจจุบันเป็น sort ใหม่
    $stmt = $conn->prepare("UPDATE workexperience SET sortOrder = :newSort WHERE id = :currentId AND userID = :userID");
    $stmt->execute([
        ':newSort' => $newSort,
        ':currentId' => $currentId,
        ':userID' => $userID
    ]);

    // ตรวจสอบว่ามีการอัปเดตสำเร็จหรือไม่
    if ($stmt->rowCount() === 0) {
        throw new Exception("No record updated. Please check your data.");
    }

    echo json_encode([
        "status" => 1,
        "message" => "Work Experience order updated successfully."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => 0,
        "message" => $e->getMessage()
    ]);
}
?>
