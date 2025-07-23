<?php
require 'config.php';

if (!isset($_GET['level']) || !isset($_GET['location'])) {
    echo json_encode([]);
    exit;
}

$currentLevel = $_GET['level'];
$location = $_GET['location'];

// Fetch all employees from higher hierarchy levels and same location
$stmt = $conn->prepare("SELECT name, hierarchy_level FROM employees WHERE location = ? AND hierarchy_level != ?");
$stmt->execute([$location, $currentLevel]);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return as JSON
echo json_encode($all);
?>
