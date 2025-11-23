<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');

try {
    $userID = $_POST["userID"] ?? null;
    $companyName = trim($_POST["companyName"] ?? '');
    $position = trim($_POST["position"] ?? '');
    $jobDescription = trim($_POST["jobDescription"] ?? '');
    $employeeType = $_POST["employeeType"] ?? '';
    $startDate = $_POST["startDate"] ?? '';
    $endDate = $_POST["endDate"] ?? null;
    $isCurrent = isset($_POST["isCurrent"]) ? 1 : 0;
    $remark = $_POST["remark"] ?? null; 

    // Validation
    if (!$userID) {
        echo json_encode(["status" => 0, "message" => "User ID is required"]);
        exit;
    }

    if (empty($companyName) || empty($position) || empty($jobDescription)) {
        echo json_encode(["status" => 0, "message" => "Company name, position, and job description are required"]);
        exit;
    }

    if (empty($employeeType)) {
        echo json_encode(["status" => 0, "message" => "Employment type is required"]);
        exit;
    }

    if (empty($startDate)) {
        echo json_encode(["status" => 0, "message" => "Start date is required"]);
        exit;
    }

    if ($isCurrent == 1) {
        $endDate = null;
    } else {
        if (empty($endDate)) {
            echo json_encode(["status" => 0, "message" => "End date is required if not currently working here"]);
            exit;
        }

        if (strtotime($startDate) >= strtotime($endDate)) {
            echo json_encode(["status" => 0, "message" => "End date must be after start date"]);
            exit;
        }
    }

    $today = date('Y-m-d');
    if ($startDate > $today) {
        echo json_encode(["status" => 0, "message" => "Start date cannot be in the future"]);
        exit;
    }

    if (!$isCurrent && $endDate && $endDate > $today) {
        echo json_encode(["status" => 0, "message" => "End date cannot be in the future"]);
        exit;
    }

    // หา sort_order ถัดไป
    $stmt = $conn->prepare("SELECT COALESCE(MAX(sortOrder), 0) + 1 AS sortOrder FROM workexperience WHERE userID = :userID");
    $stmt->execute([':userID' => $userID]);
    $sortOrder = $stmt->fetch(PDO::FETCH_ASSOC)['sortOrder'];

    // Insert ข้อมูล
    $stmt = $conn->prepare("
        INSERT INTO workexperience
        (userID, companyName, position, jobDescription, employeeType, startDate, endDate, isCurrent, sortOrder, remark)
        VALUES (:userID, :companyName, :position, :jobDescription, :employeeType, :startDate, :endDate, :isCurrent, :sortOrder, :remark)
    ");

    $stmt->execute([
        ':userID' => $userID,
        ':companyName' => $companyName,
        ':position' => $position,
        ':jobDescription' => $jobDescription,
        ':employeeType' => $employeeType,
        ':startDate' => $startDate,
        ':endDate' => $endDate,
        ':isCurrent' => $isCurrent,
        ':sortOrder' => $sortOrder,
        ':remark' => $remark
    ]);

    echo json_encode([
        "status" => 1,
        "message" => "New work experience created successfully",
        "userID" => $userID,
        "insertId" => $conn->lastInsertId(),
        "sortOrder" => $sortOrder
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => 0,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>
