<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// upload & check image

function uploadImage($file, $dir, $filename)
{
    $maxSize = 10 * 1024 * 1024;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if ($file['size'] > $maxSize) {
        throw new Exception("File size exceeds 10MB limit");
    }
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception("Invalid file type");
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $finalName = $filename . '.' . $ext;

    // safe path
    $finalPath = rtrim($dir, "/") . "/" . $finalName;

    if (!move_uploaded_file($file['tmp_name'], $finalPath)) {
        throw new Exception("Failed to upload file");
    }

    return $finalName;
}

// get userID

$userID = intval($_POST['userID'] ?? 0);

if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {

    // prepare folder
    $dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/portfolio/$userID/";
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $uploaded = [];

    // upload images
    if (!empty($_FILES['logoImage']['size'])) {
        $uploaded['logoImage'] = uploadImage($_FILES['logoImage'], $dir, 'logo');
    }
    if (!empty($_FILES['profileImage']['size'])) {
        $uploaded['profileImage'] = uploadImage($_FILES['profileImage'], $dir, 'profile');
    }
    if (!empty($_FILES['coverImage']['size'])) {
        $uploaded['coverImage'] = uploadImage($_FILES['coverImage'], $dir, 'cover');
    }


    // update user table

    $userFields = ['firstname', 'lastname', 'birthdate'];
    $updateUser = [];
    $paramsUser = [':userID' => $userID];

    foreach ($userFields as $key) {
        if (isset($_POST[$key]) && $_POST[$key] !== "") {
            $updateUser[] = "$key = :$key";
            $paramsUser[":$key"] = $_POST[$key];
        }
    }

    if ($updateUser) {
        $sql = "UPDATE user SET " . implode(", ", $updateUser) . " WHERE userID = :userID";
        $stmt = $conn->prepare($sql);
        $stmt->execute($paramsUser);
    }


    // update profile table

    $profileMap = [
        'phone' => 'phone',
        'ProfessionalTitle' => 'professionalTitle',
        'facebook' => 'facebook',
        'facebookUrl' => 'facebookUrl',
        'introContent' => 'introContent',
        'skillsContent' => 'skillsContent'
    ];

    $updateProfile = [];
    $paramsProfile = [':userID' => $userID];

    foreach ($profileMap as $post => $db) {
        if (isset($_POST[$post]) && $_POST[$post] !== "") {
            $updateProfile[] = "$db = :$db";
            $paramsProfile[":$db"] = $_POST[$post];
        }
    }

    foreach ($uploaded as $field => $filename) {
        $updateProfile[] = "$field = :$field";
        $paramsProfile[":$field"] = $filename;
    }

    if ($updateProfile) {
        $sql = "UPDATE profile SET " . implode(", ", $updateProfile) . " WHERE userID = :userID";
        $stmt = $conn->prepare($sql);
        $stmt->execute($paramsProfile);
    }


    // update skills

    if (isset($_POST['mySkills'])) {

        $skills = array_filter(array_map('intval', explode(",", $_POST['mySkills'])));

        $conn->prepare("DELETE FROM profileskill WHERE userID = :userID")
            ->execute([':userID' => $userID]);

        if (!empty($skills)) {
            $sql = "INSERT INTO profileskill (userID, skillsID) VALUES (:userID, :skillsID)";
            $stmt = $conn->prepare($sql);

            foreach ($skills as $id) {
                $stmt->execute([
                    ':userID' => $userID,
                    ':skillsID' => $id
                ]);
            }
        }
    }

    echo json_encode([
        'status' => true,
        'message' => 'Personal data saved successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
}
?>
