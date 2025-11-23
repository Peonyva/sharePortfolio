<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. Method Check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. Validation (ใช้ filter_input เพื่อความชัวร์ว่าเป็นตัวเลขจริงๆ)
$userID = filter_input(INPUT_GET, 'userID', FILTER_VALIDATE_INT);

if (!$userID || $userID <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Invalid or missing User ID.']);
    exit;
}

try {
    // 3. Query
    $stmt = $conn->prepare("SELECT * FROM workexperience WHERE userID = :userID ORDER BY sortOrder ASC");
    $stmt->execute(['userID' => $userID]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    

    // 4. Response
    echo json_encode([
        'status' => 1,
        'data' => $rows
    ]);

} catch (PDOException $e) {
    // 5. Security: เก็บ Error จริงไว้ที่ Server เท่านั้น
    error_log("Get Work Experience Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error occurred.'
    ]);
}

$conn = null;
?>