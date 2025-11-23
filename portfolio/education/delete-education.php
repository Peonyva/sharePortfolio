<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. ตรวจสอบว่าเป็น POST Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. ตรวจสอบค่าที่ส่งมา
if (empty($_POST['id']) || empty($_POST['userID'])) {
    echo json_encode(['status' => 0, 'message' => 'Missing Education ID or User ID.']);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    // ขั้นตอนที่ 1: ดึง sortOrder เดิมออกมาก่อน
    $sql = "SELECT sortOrder FROM education WHERE id = :id AND userID = :userID";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id, ':userID' => $userID]);
    
    $education = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$education) {
        echo json_encode(['status' => 0, 'message' => 'Education not found or access denied.']);
        exit;
    }
    
    $deletedSortOrder = $education['sortOrder'];
    
    // ขั้นตอนที่ 2: ลบข้อมูล
    $sqlDelete = "DELETE FROM education WHERE id = :id AND userID = :userID";
    $stmtDelete = $conn->prepare($sqlDelete);
    
    if ($stmtDelete->execute([':id' => $id, ':userID' => $userID])) {
        
        // ขั้นตอนที่ 3: เรียงลำดับใหม่ (อัปเดตแถวที่อยู่ข้างล่างให้ขยับขึ้น)
        // ทำงานต่อทันทีถ้าลบสำเร็จ
        $sqlUpdate = "UPDATE education 
                      SET sortOrder = sortOrder - 1 
                      WHERE userID = :userID AND sortOrder > :deletedSort";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([':userID' => $userID, ':deletedSort' => $deletedSortOrder]);
        
        echo json_encode(['status' => 1, 'message' => 'Education deleted successfully!']);
        
    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to delete education.']);
    }

} catch (PDOException $e) {
    // เก็บ Log Error ไว้ดูเอง
    error_log("Delete Error: " . $e->getMessage());
    
    // แจ้ง User แค่สั้นๆ
    echo json_encode(['status' => 0, 'message' => 'Database Error occurred.']);
}

$conn = null;
?>