<?php
require 'config.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['level'])) {
    $selectedLevel = $_POST['level'];

    // Define hierarchy map - employees at these levels can be managers for the selected level
    $hierarchyMap = [
        'L-2' => ['L-3', 'L-4', 'L-5', 'L-6', 'L-7', 'L-8'],
        'L-3' => ['L-4', 'L-5', 'L-6', 'L-7', 'L-8'],
        'L-4' => ['L-5', 'L-6', 'L-7', 'L-8'],
        'L-5' => ['L-6', 'L-7', 'L-8'],
        'L-6' => ['L-7', 'L-8'],
        'L-7' => ['L-8'],
        'L-8' => [] // No managers; connected to board/organization
    ];

    try {
        if ($selectedLevel === 'L-8') {
            // For L-8, show board connection (disabled to prevent selection)
            echo "<option value='' disabled selected>Connected to Board</option>";
            exit;
        }

        if (!isset($hierarchyMap[$selectedLevel])) {
            echo "<option value=''>-- Invalid Level --</option>";
            exit;
        }

        $allowedLevels = $hierarchyMap[$selectedLevel];

        if (empty($allowedLevels)) {
            echo "<option value=''>-- No managers available --</option>";
            exit;
        }

        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($allowedLevels), '?'));

        // Query to get potential managers (only active employees)
        $sql = "SELECT id, name, hierarchy_level, employee_id 
                FROM employees 
                WHERE hierarchy_level IN ($placeholders) 
                AND status = 'Active'
                ORDER BY hierarchy_level ASC, name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($allowedLevels);

        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<option value=''>-- Select Manager --</option>";
        
        if (empty($managers)) {
            echo "<option value=''>-- No managers found --</option>";
        } else {
            foreach ($managers as $row) {
                echo "<option value='{$row['id']}'>{$row['name']} ({$row['hierarchy_level']}) - ID: {$row['employee_id']}</option>";
            }
        }

    } catch (PDOException $e) {
        // Log the error (in production, log to file instead)
        error_log("Database error in get_managers.php: " . $e->getMessage());
        echo "<option value=''>-- Database error --</option>";
    } catch (Exception $e) {
        // Log general errors
        error_log("General error in get_managers.php: " . $e->getMessage());
        echo "<option value=''>-- Error loading managers --</option>";
    }

} else {
    echo "<option value=''>-- Invalid request --</option>";
}
?>