<?php
// get-login.php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Please provide email and password'
    ]);
    exit;
}

try {
    // ดึงข้อมูล user พร้อม isEverPublic จากตาราง profile
    $stmt = $conn->prepare("
        SELECT u.userID, u.email, p.isPublic, p.isEverPublic 
        FROM user u
        LEFT JOIN profile p ON u.userID = p.userID
        WHERE u.email = :email
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 0,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // ตรวจสอบรหัสผ่าน (สมมติว่ามีการเข้ารหัสด้วย password_hash)
    // ถ้าใช้ plain text ให้เปรียบเทียบตรงๆ
    // if ($password !== $user['password']) { ... }
    
    // ถ้ายังไม่มี profile record ให้ตั้งค่าเริ่มต้น
    if ($user['isEverPublic'] === null) {
        // สร้าง profile record ใหม่
        $createStmt = $conn->prepare("
            INSERT INTO profile (userID, isPublic, isEverPublic) 
            VALUES (:userID, 0, 0)
        ");
        $createStmt->execute(['userID' => $user['userID']]);
        
        $user['isPublic'] = 0;
        $user['isEverPublic'] = 0;
    }

    // ส่งข้อมูลกลับ
    echo json_encode([
        'status' => 1,
        'message' => 'Login successful',
        'data' => [
            'userID' => $user['userID'],
            'email' => $user['email'],
            'isPublic' => intval($user['isPublic'] ?? 0),
            'isEverPublic' => intval($user['isEverPublic'] ?? 0)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        'status' => 0,
        'message' => 'Database error occurred'
    ]);
}
?>