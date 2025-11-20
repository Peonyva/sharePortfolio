<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$userID = intval($_POST['userID'] ?? 0);

if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {

    // Start Transaction


    // ===============================
    // 🔹 Upload Directory
    // ===============================
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/portfolio/' . $userID . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $logoImage = null;
    $profileImage = null;
    $coverImage = null;

    if (!empty($_FILES['logoImage']['size'])) {
        $logoImage = uploadImage($_FILES['logoImage'], $uploadDir, 'logo');
    }
    if (!empty($_FILES['profileImage']['size'])) {
        $profileImage = uploadImage($_FILES['profileImage'], $uploadDir, 'profile');
    }
    if (!empty($_FILES['coverImage']['size'])) {
        $coverImage = uploadImage($_FILES['coverImage'], $uploadDir, 'cover');
    }

    // ===============================
    // 🔹 UPDATE USER TABLE
    // ===============================
    $userFields = ['firstname', 'lastname', 'birthdate', 'email'];
    $updateUserFields = [];
    $paramsUser = [':userID' => $userID];

    foreach ($userFields as $field) {
        if (isset($_POST[$field])) {
            $updateUserFields[] = "$field = :$field";
            $paramsUser[":$field"] = $_POST[$field];
        }
    }

    if (!empty($password)) {
        $updateUserFields[] = "password = :password";
        $paramsUser[':password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if (!empty($updateUserFields)) {
        $sql = "UPDATE user SET " . implode(', ', $updateUserFields) . " WHERE userID = :userID";
        $conn->prepare($sql)->execute($paramsUser);
    }

    // ===============================
    // 🔹 UPDATE PROFILE TABLE
    // ===============================
    $profileFields = [
        'phone' => 'phone',
        'ProfessionalTitle' => 'professionalTitle',
        'facebook' => 'facebook',
        'facebookUrl' => 'facebookUrl',
        'introContent' => 'introContent',
        'skillsContent' => 'skillsContent'
    ];

    $updateProfileFields = [];
    $paramsProfile = [':userID' => $userID];

    foreach ($profileFields as $post => $db) {
        if (isset($_POST[$post])) {
            $updateProfileFields[] = "$db = :$db";
            $paramsProfile[":$db"] = $_POST[$post];
        }
    }

    if ($logoImage) {
        $updateProfileFields[] = "logoImage = :logoImage";
        $paramsProfile[':logoImage'] = $logoImage;
    }
    if ($profileImage) {
        $updateProfileFields[] = "profileImage = :profileImage";
        $paramsProfile[':profileImage'] = $profileImage;
    }
    if ($coverImage) {
        $updateProfileFields[] = "coverImage = :coverImage";
        $paramsProfile[':coverImage'] = $coverImage;
    }

    if (!empty($updateProfileFields)) {
        $sql = "UPDATE profile SET " . implode(', ', $updateProfileFields) . " WHERE userID = :userID";
        $conn->prepare($sql)->execute($paramsProfile);
    }

    // ===============================
    // 🔹 UPDATE SKILLS
    // ===============================
    if (isset($_POST['mySkills'])) {

        // ถ้ามาจาก hidden input → เป็น JSON
        $selectedSkills = json_decode($_POST['mySkills'], true);

        if (!is_array($selectedSkills)) {
            $selectedSkills = [];
        }

        // ลบทิ้งก่อน
        $conn->prepare("DELETE FROM profileskill WHERE userID = :userID")
            ->execute([':userID' => $userID]);

        // insert ใหม่
        if (!empty($selectedSkills)) {
            $sql = "INSERT INTO profileskill (userID, skillsID) VALUES (:userID, :skillsID)";
            $stmt = $conn->prepare($sql);

            foreach ($selectedSkills as $id) {
                $stmt->execute([
                    ':userID' => $userID,
                    ':skillsID' => intval($id)
                ]);
            }
        }
    }


    echo json_encode([
        'success' => true,
        'message' => 'Personal saved successfully'
    ]);

} catch (Exception $e) {

    $conn->rollBack();

    error_log("Save Personal Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}


/**
 * ===============================
 *  🔹 Image Upload Function
 * ===============================
 */
function uploadImage($file, $dir, $prefix)
{
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 10 * 1024 * 1024; // 10MB

    if (!in_array($file['type'], $allowed)) {
        throw new Exception("Invalid file type");
    }
    if ($file['size'] > $maxSize) {
        throw new Exception("File too large (max 10MB)");
    }

    // random filename เพื่อแก้ cache
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $filepath = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("File upload failed");
    }

    return '/uploads/portfolio/' . basename(dirname($filepath)) . '/' . $filename;
}

?>
