<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);  // ไม่แสดงบนหน้าเว็บ แต่บันทึกใน log

$userID = $_GET['userID'] ?? '';

if (empty($userID)) {
    echo json_encode([
        'status' => 0,
        'message' => 'User ID is required'
    ]);
    exit;
}

try {
    // ทดสอบการเชื่อมต่อ database
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Query 1: ข้อมูลผู้ใช้ (เริ่มจาก user ก่อน)
    $stmt = $conn->prepare("SELECT userID, firstname, lastname, birthdate, email
        FROM user WHERE userID = :userID");

    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        echo json_encode([
            'status' => 0,
            'message' => 'User not found',
            'userID' => $userID
        ]);
        exit;
    }

    // Query 2: ข้อมูล Profile (ถ้ามี)
    $stmt = $conn->prepare("SELECT professionalTitle, phone, facebook, facebookUrl,
            logoImage, profileImage, coverImage, introContent, skillsContent
        FROM profile WHERE userID = :userID");

    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

    // รวมข้อมูล user และ profile
    $data = array_merge($userData, $profileData ?: []);

    // เพิ่ม Full Path ให้รูปภาพ
    if (!empty($data['logoImage'])) {
        $data['logoImage'] = "/uploads/{$userID}/" . $data['logoImage'];
    }
    if (!empty($data['profileImage'])) {
        $data['profileImage'] = "/uploads/{$userID}/" . $data['profileImage'];
    }
    if (!empty($data['coverImage'])) {
        $data['coverImage'] = "/uploads/{$userID}/" . $data['coverImage'];
    }

    // Query 3: ข้อมูลทักษะของผู้ใช้
    $stmt = $conn->prepare("SELECT s.skillsID, s.skillsName 
        FROM profileskill ps
        INNER JOIN skills s ON s.skillsID = ps.skillsID
        WHERE ps.userID = :userID
        ORDER BY s.skillsName ASC");

    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $selectedSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Response structure
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
