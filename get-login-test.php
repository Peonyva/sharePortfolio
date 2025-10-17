<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');

try {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($email)) {
        echo json_encode(["status" => 0, "message" => "Email is required"]);
        exit;
    }

    if (empty($password)) {
        echo json_encode(["status" => 0, "message" => "Password is required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($password === $user['password']) {

            $userID = $user['userID'];

            // ตรวจสอบและสร้าง Profile
            $checkProfile = $conn->prepare("SELECT isPublic, isEverPublic FROM profile WHERE userID = ?");
            $checkProfile->execute([$userID]);

            $profileData = $checkProfile->fetch(PDO::FETCH_ASSOC); 

            // ถ้ายังไม่มี -> สร้างใหม่เลย
            if ($checkProfile->rowCount() === 0) {
                $insertProfile = $conn->prepare(" INSERT INTO profile (userID, isPublic, isEverPublic) VALUES (?, 0, 0) ");
                $insertProfile->execute([$userID]);

                // ตั้งค่า profileData สำหรับส่งกลับในกรณีที่สร้างใหม่
                $profileData = ['isPublic' => 0, 'isEverPublic' => 0];
            }


            // **รวมข้อมูล Profile เข้าไปใน data ที่จะส่งกลับ**
            $user = array_merge($user, $profileData);

            echo json_encode([
                "status" => 1,
                "data" => $user, // ตอนนี้ $user มี isPublic และ isEverPublic แล้ว
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
