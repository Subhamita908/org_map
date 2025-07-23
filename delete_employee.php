<?php
session_start();

// Check if HR is logged in
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

require 'config.php';

// Validate the employee ID from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $employeeId = $_GET['id'];

    try {
        // Prepare and execute delete query
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $employeeId, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect back to view_employees page
        header("Location: view_employees.php?message=deleted");
        exit();

    } catch (PDOException $e) {
        echo "<h3 style='color: red;'>Error deleting employee: " . $e->getMessage() . "</h3>";
    }
} else {
    echo "<h3 style='color: red;'>Invalid employee ID.</h3>";
}
?>
