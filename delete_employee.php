<?php
session_start();
require 'config.php';

if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$id = $_GET['id'];

// Optional: Delete profile picture file
$stmt = $pdo->prepare("SELECT profile_picture FROM employees WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && file_exists($row['profile_picture'])) {
    unlink($row['profile_picture']);
}

// Delete the employee
$stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
$success = $stmt->execute([$id]);

if ($success) {
    echo "<script>alert('Employee deleted successfully'); window.location.href='view_employees.php';</script>";
} else {
    echo "<script>alert('Failed to delete employee'); window.history.back();</script>";
}
?>
