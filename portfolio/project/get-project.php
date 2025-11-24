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
    // 3. Query Projects
    $sql = "SELECT projectID, projectTitle, projectImage, keyPoint, sortOrder
            FROM project
            WHERE userID = :userID
            ORDER BY sortOrder ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['userID' => $userID]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Query Skills สำหรับแต่ละ Project
    $skillSql = "SELECT ps.projectID, s.skillsID, s.skillsName
                 FROM projectSkill ps
                 INNER JOIN skills s ON ps.skillsID = s.skillsID
                 WHERE ps.projectID = :projectID";
    
    $skillStmt = $conn->prepare($skillSql);

    // 5. Loop เพื่อเติม Skills ใน Project แต่ละตัว
    foreach ($projects as &$project) {
        // จัดการ Path รูปภาพ
        if (!empty($project['projectImage'])) {
            $project['projectImage'] = $project['projectImage'];
        } else {
            $project['projectImage'] = null;
        }

        // ดึง Skills
        $skillStmt->execute(['projectID' => $project['projectID']]);
        $skills = $skillStmt->fetchAll(PDO::FETCH_ASSOC);

        // สร้าง Array ของ Skills ในรูปแบบที่ต้องการ
        $project['skills'] = array_map(function($skill) {
            return [
                'skillsID' => (int)$skill['skillsID'],
                'skillsName' => $skill['skillsName']
            ];
        }, $skills);
    }

    echo json_encode([
        'status' => 1,
        'data' => $projects
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Get Project Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 0,
        'message' => 'Database Error occurred.'
    ]);
}

$conn = null;