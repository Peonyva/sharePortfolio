<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');

try {
    $firstname = $_POST["firstname"] ?? '';
    $lastname = $_POST["lastname"] ?? '';
    $birthdate = $_POST["birthdate"] ?? '';
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($firstname) || empty($lastname)) {
        echo json_encode(["status" => 0, "message" => "Firstname or Lastname are required"]);
        exit;
    }

    if (empty($birthdate)) {
        echo json_encode(["status" => 0, "message" => "Date of birth is required"]);
        exit;
    }

    if (empty($email)) {
        echo json_encode(["status" => 0, "message" => "Email is required"]);
        exit;
    }

    if (empty($password)) {
        echo json_encode(["status" => 0, "message" => "Password is required"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO user (firstname, lastname, birthdate, email, password)
            VALUES (:firstname, :lastname, :birthdate, :email, :password)");

    $stmt->execute([
        ':firstname' => $firstname,
        ':lastname' => $lastname,
        ':birthdate' => $birthdate,
        ':email' => $email,
        ':password' => $password
    ]);

    echo json_encode([
        "status" => 1,
        "message" => "New User created successfully",
    ]);


} catch (Exception $e) {
    echo json_encode([
        "status" => 0,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>
