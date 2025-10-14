<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ ID
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
        // ✅ ดึงข้อมูลเก่าจากฐานข้อมูลก่อน
        $sqlSelect = "SELECT * FROM workexperience WHERE id = :id AND userID = :userID";
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bindParam(':id', $id);
        $stmtSelect->bindParam(':userID', $userID);
        $stmtSelect->execute();
        
        $oldData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if (!$oldData) {
            echo json_encode([
                'status' => 0,
                'message' => 'Work Experience not found or does not belong to this user.'
            ]);
            exit;
        }

        // ✅ ใช้ข้อมูลเก่าถ้าไม่ได้ส่งค่ามา หรือค่าว่าง
        $companyName = !empty($_POST['companyName']) ? trim($_POST['companyName']) : $oldData['companyName'];
        $employeeType = !empty($_POST['employeeType']) ? trim($_POST['employeeType']) : $oldData['employeeType'];
        $position = !empty($_POST['position']) ? trim($_POST['position']) : $oldData['position'];
        $startDate = !empty($_POST['startDate']) ? trim($_POST['startDate']) : $oldData['startDate'];
        $jobDescription = !empty($_POST['jobDescription']) ? trim($_POST['jobDescription']) : $oldData['jobDescription'];
        
        // ✅ isCurrent - ถ้าไม่ได้ส่งมา ใช้ค่าเก่า
        $isCurrent = isset($_POST['isCurrent']) ? intval($_POST['isCurrent']) : intval($oldData['isCurrent']);
        
        // ✅ endDate - จัดการตามเงื่อนไข isCurrent
        if ($isCurrent == 1) {
            $endDate = null; // ถ้ากำลังทำงานอยู่ ให้เป็น NULL
        } else {
            // ถ้าไม่ได้กำลังทำงาน ใช้ค่าที่ส่งมา หรือค่าเก่า
            $endDate = isset($_POST['endDate']) && !empty($_POST['endDate']) 
                       ? trim($_POST['endDate']) 
                       : $oldData['endDate'];
        }
        
        // ✅ remarks - อนุญาตให้เป็นค่าว่างได้
        $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : $oldData['remarks'];

        // =============================
        // Validation
        // =============================
        
        // เช็คฟิลด์ที่จำเป็น
        if (empty($companyName) || empty($employeeType) || empty($position) || empty($startDate) || empty($jobDescription)) {
            echo json_encode([
                'status' => 0,
                'message' => 'Please fill in all required fields.'
            ]);
            exit;
        }

        // ✅ ตรวจสอบว่า endDate มากกว่า startDate หรือไม่ (เฉพาะตอนที่ไม่ได้กำลังทำงาน)
        if ($isCurrent == 0 && !empty($endDate)) {
            if (strtotime($endDate) <= strtotime($startDate)) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'End Date must be after Start Date.'
                ]);
                exit;
            }

            // ✅ ตรวจสอบว่า endDate ไม่เกินวันปัจจุบัน
            if (strtotime($endDate) > time()) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'End Date cannot be in the future.'
                ]);
                exit;
            }
        }

        // ✅ ตรวจสอบว่า startDate ไม่เกินวันปัจจุบัน
        if (strtotime($startDate) > time()) {
            echo json_encode([
                'status' => 0,
                'message' => 'Start Date cannot be in the future.'
            ]);
            exit;
        }

        // =============================
        // SQL Update
        // =============================
        
        $sql = "UPDATE workexperience SET 
                companyName = :companyName,
                employeeType = :employeeType,
                position = :position,
                startDate = :startDate,
                endDate = :endDate,
                isCurrent = :isCurrent,
                jobDescription = :jobDescription,
                remarks = :remarks
                WHERE id = :id AND userID = :userID"; // ✅ เอา comma หลัง remarks ออก และเพิ่ม userID

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':companyName', $companyName);
        $stmt->bindParam(':employeeType', $employeeType);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':startDate', $startDate);
        $stmt->bindParam(':endDate', $endDate);
        $stmt->bindParam(':isCurrent', $isCurrent, PDO::PARAM_INT);
        $stmt->bindParam(':jobDescription', $jobDescription);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // ✅ ตรวจสอบว่ามีการอัพเดทจริงหรือไม่
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'status' => 1,
                    'message' => 'Work Experience updated successfully.'
                ]);
            } else {
                // ไม่มีการเปลี่ยนแปลงข้อมูล (ข้อมูลเหมือนเดิม)
                echo json_encode([
                    'status' => 1,
                    'message' => 'No changes detected. Data remains the same.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to update Work Experience.'
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            'status' => 0,
            'message' => 'Database Error: ' . $e->getMessage()
        ]);
    }

} else {
    echo json_encode([
        'status' => 0,
        'message' => 'Invalid request method. Only POST is allowed.'
    ]);
}

$conn = null;
?>