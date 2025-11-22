<?php
// get-login.php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Please enter your email and password.'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT u.firstname, u.lastname, u.birthdate, u.email, p.userID  FROM user u
        LEFT JOIN profile p ON u.userID = p.userID
        WHERE u.email = :email AND u.password = :password");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$row) {
        echo json_encode([
            'status' => 0,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    if ($row['userID'] === null) {
        $stmt = $conn->prepare("INSERT INTO profile (userID) VALUES (:userID)");
        $stmt->execute(['userID' => $row['userID']]);
    }

    echo json_encode([
        'status' => 1,
        'message' => 'Login successful',
        'data' => [
            'userID' => $row['userID'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'lastname' => $row['lastname'],
            'birthdate' => $row['birthdate'],
            'email' => $row['email']
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
