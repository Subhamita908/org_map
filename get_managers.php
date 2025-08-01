<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['level'])) {
    $selectedLevel = $_POST['level'];

    // Define hierarchy map
    $hierarchyMap = [
        'L-2' => ['L-2', 'L-3', 'L-4', 'L-5', 'L-6', 'L-7', 'L-8'],
        'L-3' => ['L-4', 'L-5', 'L-6', 'L-7', 'L-8'],
        'L-4' => ['L-5', 'L-6', 'L-7', 'L-8'],
        'L-5' => ['L-6', 'L-7', 'L-8'],
        'L-6' => ['L-7', 'L-8'],
        'L-7' => ['L-8'],
        'L-8' => [] // No managers; connected to organization
    ];

    if ($selectedLevel === 'L-8') {
        echo "<option value=''>Connected to Organization</option>";
        exit;
    }

    if (!isset($hierarchyMap[$selectedLevel])) {
        echo "<option value=''>-- Invalid Level --</option>";
        exit;
    }

    $allowedLevels = $hierarchyMap[$selectedLevel];

    // Convert to comma-separated quoted values for SQL
    $placeholders = implode(',', array_fill(0, count($allowedLevels), '?'));

    $sql = "SELECT id, name, hierarchy_level FROM employees WHERE hierarchy_level IN ($placeholders) ORDER BY hierarchy_level ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($allowedLevels);

    echo "<option value=''>-- Select Manager --</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<option value='{$row['id']}'>{$row['name']} ({$row['hierarchy_level']})</option>";
    }
}
?>
