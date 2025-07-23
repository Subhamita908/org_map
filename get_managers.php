<?php
require 'config.php';

if (!isset($_GET['level']) || !isset($_GET['location'])) {
    echo 'hello';
    exit;
}

$level = $_GET['level'];
$location = $_GET['location'];

// Define hierarchy order
$levels = ['L-8', 'L-7', 'L-6', 'L-5', 'L-4', 'L-3', 'L-2'];
echo 'iuweriweiurweiu';

// Get current index
$currentIndex = array_search($level, $levels);

// Get higher levels only
$higherLevels = array_slice($levels, 0, $currentIndex);

if (count($higherLevels) === 0) {
    echo '<option value="">No managers available</option>';
    exit;
}

// Convert to placeholders
$placeholders = implode(',', array_fill(0, count($higherLevels), '?'));
$sql = "SELECT name FROM employees WHERE hierarchy_level IN ($placeholders) AND location = ? ORDER BY hierarchy_level DESC";
$stmt = $conn->prepare($sql);
$params = array_merge($higherLevels, [$location]);
$stmt->execute($params);

$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo $managers;

echo '<option value="">-- Select Manager --</option>';
foreach ($managers as $manager) {
    echo "<option value=\"" . htmlspecialchars($manager['name']) . "\">" . htmlspecialchars($manager['name']) . "</option>";
}
?>
