<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. Method Check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. Validation Inputs หลัก
if (empty($_POST['id']) || empty($_POST['userID'])) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Missing Work Experience ID or User ID.']);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    // 3. ดึงข้อมูลเก่า (Old Data)
    $sqlSelect = "SELECT * FROM workexperience WHERE id = :id AND userID = :userID";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->execute([':id' => $id, ':userID' => $userID]);
    $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        http_response_code(404);
        echo json_encode(['status' => 0, 'message' => 'Work Experience not found or access denied.']);
        exit;
    }

    // 4. เตรียมข้อมูล (Data Preparation) - ใช้ isset เพื่อเช็คว่ามีการส่งค่ามาแก้ไขไหม
    // ถ้าส่งมาให้ใช้ค่าใหม่ (trim) ถ้าไม่ส่งให้ใช้ค่าเก่า
    $companyName    = isset($_POST['companyName']) ? trim($_POST['companyName']) : $oldData['companyName'];
    $employeeType   = isset($_POST['employeeType']) ? trim($_POST['employeeType']) : $oldData['employeeType'];
    $position       = isset($_POST['position']) ? trim($_POST['position']) : $oldData['position'];
    $startDate      = isset($_POST['startDate']) ? trim($_POST['startDate']) : $oldData['startDate'];
    $jobDescription = isset($_POST['jobDescription']) ? trim($_POST['jobDescription']) : $oldData['jobDescription'];
    $remarks        = isset($_POST['remarks']) ? trim($_POST['remarks']) : $oldData['remarks'];
    
    // จัดการ isCurrent
    $isCurrent = isset($_POST['isCurrent']) ? intval($_POST['isCurrent']) : intval($oldData['isCurrent']);

    // จัดการ endDate
    if ($isCurrent == 1) {
        $endDate = null;
    } else {
        // ถ้าส่ง endDate มาให้ใช้ค่าใหม่ ถ้าไม่ส่งใช้ค่าเก่า
        $endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : $oldData['endDate'];
        // ถ้าค่าเป็น string ว่าง ให้เป็น null (เผื่อกรณีส่งมาล้างค่า)
        if ($endDate === '') $endDate = null;
    }

    // 5. Logic Validation
    // เช็ค Required Fields
    if (empty($companyName) || empty($employeeType) || empty($position) || empty($startDate) || empty($jobDescription)) {
        echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    $today = date('Y-m-d'); // ใช้วันที่ปัจจุบัน (ไม่เอาเวลา)

    // Start Date ต้องไม่เป็นอนาคต
    if (strtotime($startDate) > strtotime($today)) {
        echo json_encode(['status' => 0, 'message' => 'Start Date cannot be in the future.']);
        exit;
    }

    if ($isCurrent == 0) {
        if (empty($endDate)) {
            echo json_encode(['status' => 0, 'message' => 'End Date is required for past jobs.']);
            exit;
        }
        
        // End Date ต้องไม่เป็นอนาคต
        if (strtotime($endDate) > strtotime($today)) {
            echo json_encode(['status' => 0, 'message' => 'End Date cannot be in the future.']);
            exit;
        }

        // End Date ต้องหลัง Start Date
        if (strtotime($endDate) <= strtotime($startDate)) {
            echo json_encode(['status' => 0, 'message' => 'End Date must be after Start Date.']);
            exit;
        }
    }

    // 6. Update Database
    $sql = "UPDATE workexperience SET 
            companyName = :companyName,
            employeeType = :employeeType,
            position = :position,
            startDate = :startDate,
            endDate = :endDate,
            isCurrent = :isCurrent,
            jobDescription = :jobDescription,
            remarks = :remarks
            WHERE id = :id AND userID = :userID";

    $stmt = $conn->prepare($sql);
    // Bind แบบ Array เพื่อความกระชับ
    $result = $stmt->execute([
        ':companyName' => $companyName,
        ':employeeType' => $employeeType,
        ':position' => $position,
        ':startDate' => $startDate,
        ':endDate' => $endDate,
        ':isCurrent' => $isCurrent,
        ':jobDescription' => $jobDescription,
        ':remarks' => $remarks,
        ':id' => $id,
        ':userID' => $userID
    ]);

    if ($result) {
        // rowCount() > 0 คือมีการเปลี่ยนค่า, = 0 คือข้อมูลเหมือนเดิม (แต่นับว่า Success)
        $msg = ($stmt->rowCount() > 0) ? 'Work Experience updated successfully.' : 'No changes made.';
        echo json_encode(['status' => 1, 'message' => $msg]);
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to update Work Experience.']);
    }

} catch (PDOException $e) {
    // Security: บันทึก Log และซ่อน Error จริง
    error_log("Update Work Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Database Error occurred.']);
}

$conn = null;
?>