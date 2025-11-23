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
    echo json_encode(['status' => 0, 'message' => 'Missing Project ID or User ID.']);
    exit;
}

$id = intval($_POST['id']);
$userID = intval($_POST['userID']);

try {
    // 3. ดึงข้อมูล Project และ SortOrder ออกมาก่อน
    $sqlSelect = "SELECT projectImage, sortOrder FROM project 
                  WHERE projectID = :id AND userID = :userID";
    $stmtSelect = $conn->prepare($sqlSelect);
    $stmtSelect->execute([':id' => $id, ':userID' => $userID]);

    $project = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        echo json_encode(['status' => 0, 'message' => 'Project not found or access denied.']);
        exit;
    }

    $deletedSortOrder = intval($project['sortOrder']);

    // 4. ลบ Skills ที่เกี่ยวข้อง (Manual Delete)
    // หมายเหตุ: ถ้า Database ตั้ง Foreign Key แบบ ON DELETE CASCADE ไว้อยู่แล้ว บรรทัดนี้ไม่ต้องมีก็ได้
    $sqlDeleteSkills = "DELETE FROM projectSkill WHERE projectID = :id";
    $stmtDeleteSkills = $conn->prepare($sqlDeleteSkills);
    $stmtDeleteSkills->execute([':id' => $id]);

    // 5. ลบ Project
    $sqlDelete = "DELETE FROM project WHERE projectID = :id AND userID = :userID";
    $stmtDelete = $conn->prepare($sqlDelete);
    
    if ($stmtDelete->execute([':id' => $id, ':userID' => $userID])) {

        // 6. ลบไฟล์รูปภาพ (Secure File Deletion)
        if (!empty($project['projectImage'])) {
            // แปลง path ให้เป็น Absolute Path ของ Server
            $imagePath = realpath($_SERVER['DOCUMENT_ROOT'] . $project['projectImage']);
            $uploadBase = realpath($_SERVER['DOCUMENT_ROOT'] . '/uploads/projects/');

            // Security Check: ป้องกันการลบไฟล์ผิดที่ หรือการยิง ../../
            if ($imagePath && $uploadBase && 
                strpos($imagePath, $uploadBase) === 0 && 
                file_exists($imagePath)) {
                @unlink($imagePath); // ใช้ @ เพื่อ ignore error เล็กน้อย (เช่นไฟล์ถูกล็อกอยู่)
            }
        }

        // 7. จัดเรียงลำดับใหม่ (Reorder)
        $sqlReorder = "UPDATE project SET sortOrder = sortOrder - 1
                       WHERE userID = :userID AND sortOrder > :deletedSort";
        $stmtReorder = $conn->prepare($sqlReorder);
        $stmtReorder->execute([':userID' => $userID, ':deletedSort' => $deletedSortOrder]);

        echo json_encode(['status' => 1, 'message' => 'Project deleted successfully!']);

    } else {
        echo json_encode(['status' => 0, 'message' => 'Failed to delete project.']);
    }

} catch (PDOException $e) {
    // Security: บันทึก Log แทนการแสดง Error จริง
    error_log("Delete Project Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 0, 'message' => 'Database Error occurred.']);
}

$conn = null;
?>