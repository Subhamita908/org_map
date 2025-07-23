<?php
session_start();
require 'config.php';

// Clear any existing session
if (isset($_SESSION['hr_logged_in'])) {
    unset($_SESSION['hr_logged_in']);
    unset($_SESSION['hr_username']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password'])); // md5 hashing

    // Validate input
    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in all fields'); window.location.href='index.html';</script>";
        exit();
    }

    try {
        // Use a single query to check both username or email
        $stmt = $conn->prepare("SELECT * FROM hr_login WHERE username = :input OR email = :input");
        $stmt->bindParam(':input', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) {
            $_SESSION['hr_logged_in'] = true;
            $_SESSION['hr_username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid username/email or password'); window.location.href='index.html';</script>";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo "<script>alert('Database error occurred'); window.location.href='index.html';</script>";
    }
}
?>