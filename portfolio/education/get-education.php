<?php
header('Content-Type: application/json; charset=utf-8');

// (Optional) หากต้องการเปิดให้เรียกข้ามโดเมนได้ (CORS) ให้เปิดบรรทัดล่างนี้
// header('Access-Control-Allow-Origin: *'); 

// 1. ตรวจสอบว่าเป็น GET Request เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. ตรวจสอบว่าไฟล์ config มีอยู่จริงก่อนเรียกใช้ เพื่อป้องกัน Error หลุด
$configFile = $_SERVER['DOCUMENT_ROOT'] . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configFile;

// 3. ตรวจสอบ Input 
$userID = filter_input(INPUT_GET, 'userID', FILTER_VALIDATE_INT);

if ($userID === false || $userID === null || $userID <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 0, 'message' => 'Invalid or missing User ID.']);
    exit;
}

try {
    // เตรียม Query (ใช้ Prepared Statement)
    $stmt = $conn->prepare("SELECT * FROM education WHERE userID = :userID ORDER BY sortOrder ASC");
    $stmt->execute(['userID' => $userID]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'status' => 1,
        'data' => $rows
    ]);

} catch (PDOException $e) {
    
    // 4. Security
    error_log("Database Error: " . $e->getMessage());

    // ส่งข้อความกลางๆ ให้ Client
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 0, 
        'message' => 'An internal error occurred. Please try again later.'
    ]);
}

$conn = null;
?>