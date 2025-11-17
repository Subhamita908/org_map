<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $stmt = $pdo->prepare("
        SELECT id, employee_id, name, designation, hierarchy_level 
        FROM employees 
        ORDER BY name ASC
    ");
    $stmt->execute();
    $employees = $stmt->fetchAll();
    
    echo json_encode($employees);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
