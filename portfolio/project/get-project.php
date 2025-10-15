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
 $sql = "SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, p.sortOrder, JSON_ARRAYAGG(s.skillsName) AS skills
        FROM project AS p
        LEFT JOIN projectSkill ps ON p.projectID = ps.projectID
        LEFT JOIN skills s ON ps.skillsID = s.skillsID
        WHERE p.userID = :userID
        GROUP BY p.projectID
        ORDER BY p.sortOrder ASC;";
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