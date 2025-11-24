<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. Method Check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 0, 'message' => 'Method Not Allowed']);
    exit;
}

// 2. Validation
$userID = filter_input(INPUT_GET, 'userID', FILTER_VALIDATE_INT);
if (!$userID || $userID <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 0, 'message' => 'Invalid User ID.']);
    exit;
}

try {
    // 3. Query
    $sql = "SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, p.sortOrder, 
                   JSON_ARRAYAGG(s.skillsName) AS skills
            FROM project AS p
            LEFT JOIN projectSkill ps ON p.projectID = ps.projectID
            LEFT JOIN skills s ON ps.skillsID = s.skillsID
            WHERE p.userID = :userID
            GROUP BY p.projectID
            ORDER BY p.sortOrder ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['userID' => $userID]);

    $project = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Loop เพื่อจัดการ Data Type และ Path
    foreach ($project as &$item) {

        // 4.1 จัดการ Path รูปภาพ
        if (!empty($item['projectImage'])) {
            $item['projectImage'] = $item['projectImage'];
        } else {
            $item['projectImage'] = null;
        }

        // 4.2 แก้ปัญหา JSON Double Encoding และ [null]
        if (!empty($item['skills'])) {
            $decodedSkills = json_decode($item['skills']);

            // ถ้า decode แล้วได้ [null] (เกิดจาก Left Join แล้วไม่เจอคู่) ให้เปลี่ยนเป็น []
            if (is_array($decodedSkills) && count($decodedSkills) === 1 && $decodedSkills[0] === null) {
                $item['skills'] = [];
            } else {
                $item['skills'] = $decodedSkills; // คืนค่าเป็น Array จริงๆ
            }
        } else {
            $item['skills'] = [];
        }
    }

    echo json_encode([
        'status' => 1,
        'data' => $project
    ]);
} catch (PDOException $e) {
    error_log("Get Project Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error occurred.'
    ]);
}

$conn = null;
