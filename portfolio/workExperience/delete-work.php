<?php 
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. ตรวจสอบ Input
if (empty($_POST['id']) || empty($_POST['userID'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 0,
        'message' => 'Missing Work Experience ID or User ID.'
    ]);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    // 3. หา sortOrder ของรายการที่ต้องการลบ (ตรวจสอบสิทธิ์เจ้าของไปในตัว)
    $stmt = $conn->prepare("SELECT sortOrder FROM workexperience WHERE id = :id AND userID = :userID");
    $stmt->execute([':id' => $id, ':userID' => $userID]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 0, 'message' => 'Work Experience not found or access denied.']);
        exit;
    }

    $deletedOrder = $item['sortOrder'];

    // 4. ลบรายการที่เลือก
    $stmtDelete = $conn->prepare("DELETE FROM workexperience WHERE id = :id AND userID = :userID");
    
    if ($stmtDelete->execute([':id' => $id, ':userID' => $userID])) {
        
        // 5. ลด sortOrder ของรายการที่อยู่ถัดไป (Reorder)
        // ใช้ try-catch ย่อย หรือปล่อยผ่านก็ได้ เพราะถ้า reorder ไม่ได้ ข้อมูลก็แค่ฟันหลอ ไม่ถึงกับพัง
        $sqlReorder = "UPDATE workexperience 
                       SET sortOrder = sortOrder - 1 
                       WHERE userID = :userID AND sortOrder > :deletedOrder";
        $stmtReorder = $conn->prepare($sqlReorder);
        $stmtReorder->execute([':userID' => $userID, ':deletedOrder' => $deletedOrder]);

        // ส่งผลลัพธ์
        echo json_encode([
            'status' => 1,
            'message' => 'Work Experience deleted successfully.'
        ]);

    } else {
        echo json_encode([
            'status' => 0,
            'message' => 'Failed to delete record.'
        ]);
    }

} catch (PDOException $e) {
    // Security: บันทึก Error จริงลง Log Server เท่านั้น
    error_log("Delete Work Error: " . $e->getMessage());

    // แจ้ง User ด้วยข้อความกลางๆ
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error occurred.'
    ]);
}

$conn = null;
?>