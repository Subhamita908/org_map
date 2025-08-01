<?php
session_start();
require 'config.php'; // make sure it defines $pdo

if (isset($_SESSION['hr_logged_in'])) {
    unset($_SESSION['hr_logged_in']);
    unset($_SESSION['hr_username']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));

    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in all fields'); window.location.href='index.html';</script>";
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM hr_login WHERE email = :username");
        $stmt->execute([':username' => $username]);
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
        echo "Login error: " . $e->getMessage();
    }
}

?>