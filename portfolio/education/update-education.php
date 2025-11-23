<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// ตรวจสอบ Input หลัก
if (empty($_POST['id']) || empty($_POST['userID'])) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Missing Education ID or User ID.']);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    // 1. ดึงข้อมูลเก่า (Old Data)
    $sqlSelect = "SELECT * FROM education WHERE id = :id AND userID = :userID";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->execute([':id' => $id, ':userID' => $userID]);
    $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$oldData) {
        echo json_encode(['status' => 0, 'message' => 'Education not found or access denied.']);
        exit;
    }

    // 2. เตรียมข้อมูล (Data Preparation)
    // ใช้ Ternary Operator: ถ้ามีการส่งค่ามาให้ใช้ค่าใหม่(แม้จะเป็นค่าว่าง) ถ้าไม่ส่งมาเลยให้ใช้ค่าเก่า
    
    // กลุ่ม Required Fields (ห้ามว่าง)
    $educationName = isset($_POST['educationName']) ? trim($_POST['educationName']) : $oldData['educationName'];
    $degree        = isset($_POST['degree']) ? trim($_POST['degree']) : $oldData['degree'];
    $facultyName   = isset($_POST['facultyName']) ? trim($_POST['facultyName']) : $oldData['facultyName'];
    $majorName     = isset($_POST['majorName']) ? trim($_POST['majorName']) : $oldData['majorName'];
    $startDate     = isset($_POST['startDate']) ? trim($_POST['startDate']) : $oldData['startDate'];
    $isCurrent     = isset($_POST['isCurrent']) ? intval($_POST['isCurrent']) : intval($oldData['isCurrent']);

    // กลุ่ม Optional Fields (ว่างได้)
    $remark = isset($_POST['remark']) ? trim($_POST['remark']) : $oldData['remark'];

    // จัดการ End Date
    if ($isCurrent == 1) {
        $endDate = null;
    } else {
        // ถ้าส่งมาให้ใช้ค่าใหม่ ถ้าไม่ส่งให้ใช้ค่าเก่า
        $endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : $oldData['endDate'];
        // ถ้าค่าที่ได้เป็น string ว่าง ให้เป็น null
        if ($endDate === '') $endDate = null;
    }

    // 3. Validation (ตรวจสอบความถูกต้อง)
    if (empty($educationName) || empty($degree) || empty($facultyName) || empty($majorName) || empty($startDate)) {
        echo json_encode(['status' => 0, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // Date Logic Check
    $today = date('Y-m-d'); // ใช้วันที่ปัจจุบันแบบไม่มีเวลา
    
    if (strtotime($startDate) > strtotime($today)) {
        echo json_encode(['status' => 0, 'message' => 'Start Date cannot be in the future.']);
        exit;
    }

    if ($isCurrent == 0 && !empty($endDate)) {
        if (strtotime($endDate) < strtotime($startDate)) {
            echo json_encode(['status' => 0, 'message' => 'End Date must be after Start Date.']);
            exit;
        }
        if (strtotime($endDate) > strtotime($today)) {
            echo json_encode(['status' => 0, 'message' => 'End Date cannot be in the future.']);
            exit;
        }
    }

    // 4. Update Query
    $sql = "UPDATE education SET 
            educationName = :educationName,
            degree = :degree,
            facultyName = :facultyName,
            majorName = :majorName,
            startDate = :startDate,
            endDate = :endDate,
            isCurrent = :isCurrent,
            remark = :remark
            WHERE id = :id AND userID = :userID";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':educationName' => $educationName,
        ':degree' => $degree,
        ':facultyName' => $facultyName,
        ':majorName' => $majorName,
        ':startDate' => $startDate,
        ':endDate' => $endDate,
        ':isCurrent' => $isCurrent,
        ':remark' => $remark,
        ':id' => $id,
        ':userID' => $userID
    ]);

    if ($result) {
        // rowCount() จะคืนค่า 0 ถ้าข้อมูลใหม่เหมือนข้อมูลเก่าเป๊ะๆ (ซึ่งถือว่า update สำเร็จ)
        echo json_encode([
            'status' => 1, 
            'message' => ($stmt->rowCount() > 0) ? 'Education updated successfully!' : 'No changes made.'
        ]);
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to update education.']);
    }

} catch (PDOException $e) {
    error_log("Update Error: " . $e->getMessage());
    echo json_encode(['status' => 0, 'message' => 'Database Error occurred.']);
}

$conn = null;
?>