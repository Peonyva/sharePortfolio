<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (empty($_POST['id']) || empty($_POST['userID'])) {
        echo json_encode(['status' => 0, 'message' => 'Missing Education ID or User ID.']);
        exit;
    }
    
    $id = intval($_POST['id']);
    $userID = intval($_POST['userID']);
    
    try {
        // ดึง sortOrder ก่อนลบ
        $sqlSelect = "SELECT sortOrder FROM education WHERE id = :id AND userID = :userID";
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtSelect->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmtSelect->execute();
        
        $education = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        
        if (!$education) {
            echo json_encode(['status' => 0, 'message' => 'Education not found.']);
            exit;
        }
        
        $deletedSortOrder = $education['sortOrder'];
        
        // ลบข้อมูล
        $sqlDelete = "DELETE FROM education WHERE id = :id AND userID = :userID";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDelete->bindParam(':userID', $userID, PDO::PARAM_INT);
        
        if ($stmtDelete->execute()) {
            // จัดเรียง sortOrder ใหม่
            $sqlReorder = "UPDATE education 
                          SET sortOrder = sortOrder - 1 
                          WHERE userID = :userID AND sortOrder > :deletedSort";
            $stmtReorder = $conn->prepare($sqlReorder);
            $stmtReorder->bindParam(':userID', $userID, PDO::PARAM_INT);
            $stmtReorder->bindParam(':deletedSort', $deletedSortOrder, PDO::PARAM_INT);
            $stmtReorder->execute();
            
            echo json_encode(['status' => 1, 'message' => 'Education deleted successfully!']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to delete education.']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 0, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
}

$conn = null;
?>