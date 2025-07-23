<?php
require 'config.php';

function fetchEmployees($parentId = null) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM employees WHERE pid " . 
        ($parentId === null ? "IS NULL" : "= ?") . " ORDER BY hierarchy_level DESC");

    $parentId === null ? $stmt->execute() : $stmt->execute([$parentId]);

    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tree = [];
    foreach ($employees as $emp) {
        $emp['children'] = fetchEmployees($emp['id']);
        $tree[] = $emp;
    }
    return $tree;
}

$orgData = fetchEmployees(); // Start from top-level (no parent)
echo json_encode($orgData);
?>