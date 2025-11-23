<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$userID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
if ($userID <= 0) {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid User ID.'
    ]);
    exit;
}

try {
    $sql = "SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, p.sortOrder, 
                   JSON_ARRAYAGG(s.skillsName) AS skills
            FROM project AS p
            LEFT JOIN projectSkill ps ON p.projectID = ps.projectID
            LEFT JOIN skills s ON ps.skillsID = s.skillsID
            WHERE p.userID = :userID
            GROUP BY p.projectID
            ORDER BY p.sortOrder ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['userID' => $userID]);

    $project = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // full path ให้รูป project
    foreach ($project as &$projectItem) {
        if (!empty($projectItem['projectImage'])) {
            $projectItem['projectImage'] = "/uploads/{$userID}/" . $projectItem['projectImage'];
        }
    }




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
