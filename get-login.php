<?php
// get-login.php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// รับค่าจาก FormData (PHP รับผ่าน $_POST ได้ปกติ)
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 1. เช็คค่าว่าง
if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Please enter your email and password.'
    ]);
    exit;
}

try {
    // 2. แก้ไข SQL Query (สำคัญ!)
    // ต้องเลือก u.userID ออกมาด้วย เพื่อเอาไว้ใช้ระบุตัวตน
    // และเปลี่ยนชื่อ p.userID เป็น profile_check เพื่อเช็คว่ามี profile หรือยัง
    $sql = "SELECT u.userID, u.firstname, u.lastname, u.birthdate, u.email, p.userID AS has_profile 
            FROM user u
            LEFT JOIN profile p ON u.userID = p.userID
            WHERE u.email = :email AND u.password = :password";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. ถ้าไม่เจอ User หรือ Password ผิด
    if (!$row) {
        echo json_encode([
            'status' => 0,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // 4. แก้ไข Logic การสร้าง Profile (ถ้ายังไม่มี)
    // เช็คจาก has_profile (p.userID) ว่าเป็น null ไหม
    if ($row['has_profile'] === null) {
        try {
            // ใช้ u.userID (จากตาราง user) ในการ insert
            $insertStmt = $conn->prepare("INSERT INTO profile (userID) VALUES (:userID)");
            $insertStmt->execute(['userID' => $row['userID']]);
        } catch (Exception $ex) {
            // กรณี Insert ไม่ผ่าน (อาจจะข้ามไปก่อน หรือ log error ไว้)
            error_log("Auto-create profile failed: " . $ex->getMessage());
        }
    }

    // 5. ส่งค่ากลับให้ JS
    echo json_encode([
        'status' => 1,
        'message' => 'Login successful',
        'data' => [
            'userID' => $row['userID'], // สำคัญ: JS ต้องใช้ค่านี้ไปทำ Link Redirect
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'birthdate' => $row['birthdate'],
            'email' => $row['email']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Login Database Error: " . $e->getMessage());
    echo json_encode([
        'status' => 0,
        'message' => 'Database error occurred.'
    ]);
}
?>