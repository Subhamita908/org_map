<?php
require 'config.php';

$level_hierarchy = [
    'L2' => 'L3',
    'L3' => 'L4',
    'L4' => 'L5',
    'L5' => 'L6',
    'L6' => 'L7',
    'L7' => 'L8',
    'L8' => null
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['level'])) {
    $current_level = $_POST['level'];
    $manager_level = $level_hierarchy[$current_level] ?? null;

    if ($manager_level) {
        $stmt = $pdo->prepare("SELECT name FROM employees WHERE hierarchy_level = ?");
        $stmt->execute([$manager_level]);

        echo '<option value="">-- Select Manager --</option>';
        while ($row = $stmt->fetch()) {
            echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
        }
    } else {
        echo '<option value="">No higher-level manager available</option>';
    }
}
?>
