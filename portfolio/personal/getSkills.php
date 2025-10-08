<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
header('Content-Type: application/json');


try {
    // ตรวจสอบการเชื่อมต่อ database
    if (!isset($conn)) {
        throw new Exception('Database connection not established');
    }

    $stmt = $conn->query("SELECT skillsID as id, skillsName as name FROM skills ORDER BY name ASC");
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่ง array (ถ้าไม่มีข้อมูลจะเป็น array เปล่า)
    echo json_encode($skills ?: [], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>