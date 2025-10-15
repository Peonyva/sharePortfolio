<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';


// เปิด error reporting (สำหรับ debug)
error_reporting(E_ALL);
ini_set('display_errors', 0); // ไม่แสดง error ออกหน้าจอ แต่เก็บไว้ใน log

// Log ข้อมูลที่ได้รับ
error_log("POST data received: " . print_r($_POST, true));

// รับค่าจาก POST
$id = isset($_POST['id']) ? trim($_POST['id']) : null;
$userID = isset($_POST['userID']) ? trim($_POST['userID']) : null;

// Debug: แสดงค่าที่ได้รับ
error_log("Parsed - id: $id, userID: $userID");

// ตรวจสอบว่ามีค่าครบหรือไม่
if (empty($id) || empty($userID)) {
    echo json_encode([
        'status' => 0,
        'message' => 'Missing Project ID or User ID.',
        'debug' => [
            'id' => $id,
            'userID' => $userID,
            'POST' => $_POST
        ]
    ]);
    exit;
}

// เชื่อมต่อฐานข้อมูล
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';

try {
    // ดึงข้อมูล project เดิมเพื่อลบรูปภาพ
    $stmt = $conn->prepare("SELECT projectImage FROM project WHERE id = ? AND userID = ?");
    $stmt->bind_param("ii", $id, $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 0,
            'message' => 'Project not found or you do not have permission to delete it.'
        ]);
        exit;
    }
    
    $project = $result->fetch_assoc();
    $oldImage = $project['projectImage'];
    
    // ลบ project จากฐานข้อมูล
    $deleteStmt = $conn->prepare("DELETE FROM project WHERE id = ? AND userID = ?");
    $deleteStmt->bind_param("ii", $id, $userID);
    
    if ($deleteStmt->execute()) {
        // ลบไฟล์รูปภาพ (ถ้ามี)
        if (!empty($oldImage) && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImage)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $oldImage);
        }
        
        // อัพเดท sortOrder ของ project อื่นๆ
        $updateStmt = $conn->prepare("
            UPDATE project 
            SET sortOrder = sortOrder - 1 
            WHERE userID = ? AND sortOrder > (
                SELECT sortOrder FROM (SELECT sortOrder FROM project WHERE id = ?) AS temp
            )
        ");
        $updateStmt->bind_param("ii", $userID, $id);
        $updateStmt->execute();
        
        echo json_encode([
            'status' => 1,
            'message' => 'Project deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 0,
            'message' => 'Failed to delete project: ' . $deleteStmt->error
        ]);
    }
    
    $stmt->close();
    $deleteStmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Delete project error: " . $e->getMessage());
    echo json_encode([
        'status' => 0,
        'message' => 'An error occurred while deleting the project.',
        'error' => $e->getMessage();
    ]);
}



// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     echo json_encode([
//         'status' => 0,
//         'message' => 'Invalid request method.'
//     ]);
//     exit;
// }

// if (empty($_POST['id']) || empty($_POST['userID'])) {
//     echo json_encode([
//         'status' => 0,
//         'message' => 'Missing Project ID or User ID.'
//     ]);
//     exit;
// }

// $id = intval($_POST['id']);
// $userID = intval($_POST['userID']);

// try {
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     $sqlSelect = "
//         SELECT projectImage, sortOrder 
//         FROM project 
//         WHERE projectID = :id AND userID = :userID
//     ";
//     $stmtSelect = $conn->prepare($sqlSelect);
//     $stmtSelect->bindParam(':id', $id, PDO::PARAM_INT);
//     $stmtSelect->bindParam(':userID', $userID, PDO::PARAM_INT);
//     $stmtSelect->execute();

//     $project = $stmtSelect->fetch(PDO::FETCH_ASSOC);

//     if (!$project) {
//         echo json_encode([
//             'status' => 0,
//             'message' => 'Project not found or does not belong to this user.'
//         ]);
//         exit;
//     }

//     $deletedSortOrder = intval($project['sortOrder']);

//     $sqlDelete = "
//         DELETE FROM project 
//         WHERE projectID = :id AND userID = :userID
//     ";
//     $stmtDelete = $conn->prepare($sqlDelete);
//     $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
//     $stmtDelete->bindParam(':userID', $userID, PDO::PARAM_INT);

//     if ($stmtDelete->execute()) {

//         if (!empty($project['projectImage'])) {
//             $imagePath = realpath($_SERVER['DOCUMENT_ROOT'] . $project['projectImage']);
//             $uploadBase = realpath($_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/');

//             if ($imagePath && strpos($imagePath, $uploadBase) === 0 && file_exists($imagePath)) {
//                 unlink($imagePath);
//             }
//         }
//         $sqlReorder = "
//             UPDATE project
//             SET sortOrder = sortOrder - 1
//             WHERE userID = :userID AND sortOrder > :deletedSort
//         ";
//         $stmtReorder = $conn->prepare($sqlReorder);
//         $stmtReorder->bindParam(':userID', $userID, PDO::PARAM_INT);
//         $stmtReorder->bindParam(':deletedSort', $deletedSortOrder, PDO::PARAM_INT);
//         $stmtReorder->execute();

//         echo json_encode([
//             'status' => 1,
//             'message' => 'Project deleted successfully!'
//         ]);
//     } else {
//         echo json_encode([
//             'status' => 0,
//             'message' => 'Failed to delete project.'
//         ]);
//     }

// } catch (PDOException $e) {
//     echo json_encode([
//         'status' => 0,
//         'message' => 'Database Error: ' . $e->getMessage()
//     ]);
// }

// $conn = null;
?>