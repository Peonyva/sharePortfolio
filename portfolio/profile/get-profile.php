<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

$userID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
if ($userID <= 0) {
    echo json_encode([
        'status' => 0,
        'message' => 'User ID is invalid'
    ]);
    exit;
}

try {
    // Query user
    $stmt = $conn->prepare("SELECT userID, firstname, lastname, birthdate, email
        FROM user WHERE userID = :userID");
    $stmt->execute(['userID' => $userID]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        echo json_encode([
            'status' => 0,
            'message' => 'User not found',
            'userID' => $userID
        ]);
        exit;
    }

    // Query profile
    $stmt = $conn->prepare("SELECT professionalTitle, phone, facebook, facebookUrl,
            logoImage, profileImage, coverImage, introContent, skillsContent
        FROM profile WHERE userID = :userID");
    $stmt->execute(['userID' => $userID]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Merge user + profile
    $data = array_merge($userData, $profileData ?: []);

    // Full path images
    foreach (['logoImage', 'profileImage', 'coverImage'] as $img) {
        if (!empty($data[$img])) {
            $data[$img] = "/upload/{$userID}/" . $data[$img];
        }
    }

    // Query skills
    $stmt = $conn->prepare("SELECT s.skillsID, s.skillsName 
        FROM profileskill ps
        INNER JOIN skills s ON s.skillsID = ps.skillsID
        WHERE ps.userID = :userID
        ORDER BY s.skillsName ASC");
    $stmt->execute(['userID' => $userID]);
    $selectedSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 1,
        'message' => 'Profile loaded successfully',
        'data' => $data,
        'selectedSkills' => $selectedSkills
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 0,
        'message' => 'Database error occurred',
        'error' => $e->getMessage(),
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 0,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}

$conn = null;
?>
