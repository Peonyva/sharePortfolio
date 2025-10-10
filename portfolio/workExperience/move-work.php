<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $currentId = intval($_POST['currentId']);
    $currentSort = intval($_POST['currentSort']);
    $newSort = intval($_POST['newSort']);
    $userID = intval($_POST['userID']);

    if ($currentId <= 0 || $userID <= 0) {
        throw new Exception("Invalid parameters.");
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

    $swapId = $swapItem['id'];

    // สลับ sortOrder
    $stmt = $conn->prepare("UPDATE workexperience SET sortOrder = :currentSort WHERE id = :swapId");
    $stmt->execute([
        ':currentSort' => $currentSort,
        ':swapId' => $swapId
    ]);

    $stmt = $conn->prepare("UPDATE workexperience SET sortOrder = :newSort WHERE id = :currentId");
    $stmt->execute([
        ':newSort' => $newSort,
        ':currentId' => $currentId
    ]);

    echo json_encode([
        "status" => 1,
        "message" => "Work experience order updated successfully."
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => 0,
        "message" => $e->getMessage()
    ]);
}
?>