<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['id']) || empty($_POST['userID'])) {
        echo json_encode([
            'status' => 0,
            'message' => 'Missing Work Experience ID or User ID.'
        ]);
        exit;
    }

    $id = intval($_POST['id']);
    $userID = intval($_POST['userID']);

    try {
        // 🔹 1. หา sortOrder ของรายการที่ต้องการลบ
        $stmt = $conn->prepare("SELECT sortOrder FROM workexperience WHERE id = :id AND userID = :userID");
        $stmt->execute([':id' => $id, ':userID' => $userID]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception("Work Experience not found for this user.");
        }

        $deletedOrder = $item['sortOrder'];

        // 🔹 2. ลบรายการที่เลือก
        $stmt = $conn->prepare("DELETE FROM workexperience WHERE id = :id AND userID = :userID");
        $deleted = $stmt->execute([':id' => $id, ':userID' => $userID]);

        if (!$deleted) {
            throw new Exception("Failed to delete record.");
        }

        // 🔹 3. ลด sortOrder ของรายการที่อยู่ถัดไปของ user เดียวกัน
        $stmt = $conn->prepare("
            UPDATE workexperience 
            SET sortOrder = sortOrder - 1 
            WHERE userID = :userID AND sortOrder > :deletedOrder
        ");
        $updated = $stmt->execute([':userID' => $userID, ':deletedOrder' => $deletedOrder]);

        // 🔹 4. ตอบกลับ
        if ($updated) {
            echo json_encode([
                'status' => 1,
                'message' => 'Work Experience deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to update sort order.'
            ]);
        }

    } catch (Exception $e) {
        echo json_encode([
            'status' => 0,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid request method.'
    ]);
}
