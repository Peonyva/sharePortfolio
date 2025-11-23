<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. รับค่าและตรวจสอบ Input
$userID = filter_input(INPUT_POST, 'userID', FILTER_VALIDATE_INT);
$projectTitle = isset($_POST['projectTitle']) ? trim($_POST['projectTitle']) : '';
$keyPoint = isset($_POST['keyPoint']) ? trim($_POST['keyPoint']) : '';
$myProjectSkills = isset($_POST['myProjectSkills']) ? $_POST['myProjectSkills'] : '';

if (!$userID || empty($projectTitle) || empty($keyPoint) || empty($myProjectSkills)) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields.']);
    exit;
}

// 3. แปลง Skill เป็น Array (รองรับทั้ง JSON string, Array, หรือ Comma separated)
if (!is_array($myProjectSkills)) {
    $decoded = json_decode($myProjectSkills, true);
    // ถ้า decode ไม่ได้ ให้ลอง explode comma, ถ้าไม่ได้อีกให้จับใส่ array เลย
    $myProjectSkills = $decoded ?: (strpos($myProjectSkills, ',') !== false ? explode(',', $myProjectSkills) : [$myProjectSkills]);
}

// 4. ตรวจสอบและอัปโหลดไฟล์
if (!isset($_FILES['projectImage']) || $_FILES['projectImage']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 0, 'message' => 'Project image is required.']);
    exit;
}

$file = $_FILES['projectImage'];
$maxSize = 10 * 1024 * 1024; // 10MB

if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 0, 'message' => 'Image size must not exceed 10MB.']);
    exit;
}

// ตรวจ MIME Type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $allowedMimeTypes)) {
    echo json_encode(['status' => 0, 'message' => 'Only JPG, PNG, GIF, and WebP images are allowed.']);
    exit;
}

// ตั้งชื่อไฟล์ (ใช้ uniqid กันซ้ำ)
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'proj_' . $userID . '_' . uniqid() . '.' . $extension;
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/';

// สร้าง Folder ถ้าไม่มี
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadPath = $uploadDir . $newFileName;
$dbImagePath = '/uploads/projects/' . $newFileName;

// เริ่มอัปโหลดไฟล์
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode(['status' => 0, 'message' => 'Failed to save image file.']);
    exit;
}

try {
    // 5. หา sortOrder ล่าสุด
    $sqlMaxSort = "SELECT COALESCE(MAX(sortOrder), 0) + 1 AS newSort FROM project WHERE userID = :userID";
    $stmtMaxSort = $conn->prepare($sqlMaxSort);
    $stmtMaxSort->execute([':userID' => $userID]);
    $newSort = $stmtMaxSort->fetch(PDO::FETCH_ASSOC)['newSort'];

    // 6. Insert Project
    $sqlProject = "INSERT INTO project (userID, projectTitle, projectImage, keyPoint, sortOrder)
                   VALUES (:userID, :projectTitle, :projectImage, :keyPoint, :sortOrder)";
    $stmtProject = $conn->prepare($sqlProject);
    $stmtProject->execute([
        ':userID' => $userID,
        ':projectTitle' => $projectTitle,
        ':projectImage' => $dbImagePath,
        ':keyPoint' => $keyPoint,
        ':sortOrder' => $newSort
    ]);

    $projectID = $conn->lastInsertId();

    // 7. Insert Project Skills (ถ้ามี Project ID แล้ว)
    if ($projectID && !empty($myProjectSkills)) {
        $sqlSkill = "INSERT INTO projectSkill (projectID, skillsID) VALUES (:projectID, :skillsID)";
        $stmtSkill = $conn->prepare($sqlSkill);

        foreach ($myProjectSkills as $skillID) {
            $skillID = intval($skillID);
            if ($skillID > 0) {
                $stmtSkill->execute([
                    ':projectID' => $projectID,
                    ':skillsID' => $skillID
                ]);
            }
        }
    }

    echo json_encode([
        'status' => 1,
        'message' => 'Project saved successfully!',
        'projectID' => $projectID
    ]);

} catch (PDOException $e) {
    // Error Handling: ถ้า Database พัง ให้ลบรูปที่เพิ่งอัปโหลดทิ้ง เพื่อไม่ให้รก Server
    if (file_exists($uploadPath)) {
        @unlink($uploadPath);
    }

    error_log("Insert Project Error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error occurred.'
    ]);
}

$conn = null;
?>