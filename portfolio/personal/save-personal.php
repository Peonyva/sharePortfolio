<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

session_start();
$userID = intval($_POST['userID'] ?? 0);

if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $password = $_POST['password'] ?? null;

    // ✅ เตรียมอัพโหลดไฟล์
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/portfolio/' . $userID . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $logoImage = null;
    $profileImage = null;
    $coverImage = null;

    if (isset($_FILES['logoImage']) && $_FILES['logoImage']['size'] > 0) {
        $logoImage = uploadImage($_FILES['logoImage'], $uploadDir, 'logo');
    }
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['size'] > 0) {
        $profileImage = uploadImage($_FILES['profileImage'], $uploadDir, 'profile');
    }
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['size'] > 0) {
        $coverImage = uploadImage($_FILES['coverImage'], $uploadDir, 'cover');
    }

    // ===========================
    // ✅ UPDATE USER (รวม password, firstname, lastname, datebirth, email)
    // ===========================
    $userFields = ['firstname', 'lastname', 'birthdate', 'email'];
    $updateUserFields = [];
    $paramsUser = [':userID' => $userID];

    foreach ($userFields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $updateUserFields[] = "$field = :$field";
            $paramsUser[":$field"] = $_POST[$field];
        }
    }

    if (!empty($password)) {
        $updateUserFields[] = "password = :password";
        $paramsUser[':password'] = $password; 
    }

    if (!empty($updateUserFields)) {
        $sqlUser = "UPDATE user SET " . implode(', ', $updateUserFields) . " WHERE userID = :userID";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->execute($paramsUser);
    }

    // ===========================
    // ✅ UPDATE PROFILE (เฉพาะ field จริงใน profile)
    // ===========================
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

    foreach ($profileFields as $postKey => $dbField) {
        if (isset($_POST[$postKey])) {
            $updateProfileFields[] = "$dbField = :$dbField";
            $paramsProfile[":$dbField"] = $_POST[$postKey];
        }
    }

    // ✅ รูปภาพ
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
        $sqlProfile = "UPDATE profile SET " . implode(', ', $updateProfileFields) . " WHERE userID = :userID";
        $stmtProfile = $conn->prepare($sqlProfile);
        $stmtProfile->execute($paramsProfile);
    }

    // ===========================
    // ✅ อัพเดต skills
    // ===========================
    if (isset($_POST['mySkills'])) {
        $selectedSkills = json_decode($_POST['mySkills'], true) ?? [];
        $conn->prepare("DELETE FROM profileskill WHERE userID = :userID")
             ->execute([':userID' => $userID]);

        if (!empty($selectedSkills)) {
            $sqlInsert = "INSERT INTO profileskill (userID, skillsID) VALUES (:userID, :skillsID)";
            $stmtInsert = $conn->prepare($sqlInsert);
            foreach ($selectedSkills as $skillsID) {
                $stmtInsert->execute([':userID' => $userID, ':skillsID' => intval($skillsID)]);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Personal data saved successfully'
    ]);

} catch (Exception $e) {
    error_log("Error saving personal data: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// ===========================
// ✅ ฟังก์ชันอัพโหลดไฟล์
// ===========================
function uploadImage($file, $dir, $prefix) {
    $maxSize = 10 * 1024 * 1024;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if ($file['size'] > $maxSize) {
        throw new Exception("File size exceeds 10MB limit");
    }
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception("Invalid file type");
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '.' . $ext;
    $filepath = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("Failed to upload file");
    }

    return '/uploads/portfolio/' . basename(dirname($filepath)) . '/' . $filename;
}
?>
