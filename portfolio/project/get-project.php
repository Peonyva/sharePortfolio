<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if (!isset($_GET['userID'])) {
    echo json_encode([
        'status' => 0,
        'message' => 'User ID is required.'
    ]);
    exit;
}

$userID = intval($_GET['userID']);

try {
    $sql = "SELECT * FROM project WHERE userID = :userID ORDER BY sortOrder ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    
    $project = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 1,
        'data' => $project
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}

$conn = null;
?>