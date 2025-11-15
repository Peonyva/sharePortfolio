<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');

try {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($password === $user['password']) {

            $userID = $user['userID'];

            $checkProfile = $conn->prepare("SELECT isPublic, isEverPublic FROM profile WHERE userID = ?");
            $checkProfile->execute([$userID]);
            $profileData = $checkProfile->fetch(PDO::FETCH_ASSOC);

            // ถ้าไม่มี profile -> สร้างใหม่
            if ($checkProfile->rowCount() === 0) {
                $insertProfile = $conn->prepare("INSERT INTO profile (userID, isPublic, isEverPublic) VALUES (?, 0, 0)");
                $insertProfile->execute([$userID]);
                $profileData = ['isPublic' => 0, 'isEverPublic' => 0];
            }

            // รวมข้อมูล profile เข้ากับข้อมูล user
            $user = array_merge($user, $profileData);

            // ปลอดภัยขึ้น (ลบรหัสผ่านออกก่อนส่ง)
            unset($user['password']);

            echo json_encode([
                "status" => 1,
                "data" => $user, // มี userID, firstname, lastname, birthdate, email, isPublic, isEverPublic
                "message" => "Login successful"
            ]);
        } else {
            echo json_encode([
                "status" => 0,
                "message" => "Invalid email or password"
            ]);
        }
    } else {
        echo json_encode([
            "status" => 0,
            "message" => "Invalid email or password"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => 0,
        "message" => $e->getMessage()
    ]);
}
