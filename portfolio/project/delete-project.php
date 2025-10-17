<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

if (empty($_POST['id']) || empty($_POST['userID'])) {
    echo json_encode([
        'status' => 0,
        'message' => 'Missing Project ID or User ID.'
    ]);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Select Project Data
    $sqlSelect = "SELECT projectImage, sortOrder FROM project 
                  WHERE projectID = :id AND userID = :userID";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtSelect->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmtSelect->execute();

    $project = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo json_encode([
            'status' => 0,
            'message' => 'Project not found or does not belong to this user.'
        ]);
        exit;
    }

    $deletedSortOrder = intval($project['sortOrder']);

    // 2. Delete projectSkill
    $sqlDeleteSkills = "DELETE FROM projectSkill WHERE projectID = :id";
    $stmtDeleteSkills = $conn->prepare($sqlDeleteSkills);
    $stmtDeleteSkills->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtDeleteSkills->execute();

    // 3. Delete Project
    $sqlDelete = "DELETE FROM project WHERE projectID = :id AND userID = :userID";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtDelete->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmtDelete->execute();

    // 4. Delete Image File
    if (!empty($project['projectImage'])) {
        $imagePath = realpath($_SERVER['DOCUMENT_ROOT'] . $project['projectImage']);
        $uploadBase = realpath($_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/');

        if ($imagePath && $uploadBase && 
            strpos($imagePath, $uploadBase) === 0 && 
            strpos($imagePath, '..') === false && 
            file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    // 5. Reorder sortOrder
    $sqlReorder = "UPDATE project SET sortOrder = sortOrder - 1
                   WHERE userID = :userID AND sortOrder > :deletedSort";
    $stmtReorder = $conn->prepare($sqlReorder);
    $stmtReorder->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmtReorder->bindParam(':deletedSort', $deletedSortOrder, PDO::PARAM_INT);
    $stmtReorder->execute();

    echo json_encode([
        'status' => 1,
        'message' => 'Project deleted successfully!'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error: ' . $e->getMessage()
    ]);
}

$conn = null;
?>