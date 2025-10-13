<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');

try {
    $userID = $_GET['userID'];

    $stmt = $conn->prepare("SELECT * FROM workexperience WHERE userID = :userID ORDER BY sortOrder ASC");
    $stmt->execute([':userID' => $userID]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => 1,
        "data" => $rows
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => 0,
        "message" => $e->getMessage()
    ]);
}

?>
