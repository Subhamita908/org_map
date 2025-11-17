<?php 
session_start();
if (!isset($_SESSION['hr_logged_in']) || $_SESSION['hr_logged_in'] !== true) {
    header("Location: index.html");
    exit();
}

require 'config.php';

$hr_username = $_SESSION['hr_username'] ?? null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $message = "<p class='error'>New passwords do not match.</p>";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM hr_login WHERE username = ?");
        $stmt->execute([$hr_username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            $message = "<p class='error'>Current password is incorrect.</p>";
        } else {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE hr_login SET password = ? WHERE username = ?");
            if ($update_stmt->execute([$hashed_new_password, $hr_username])) {
                $message = "<p class='success'>✅ Password changed successfully.</p>";
            } else {
                $message = "<p class='error'>❌ Failed to change password.</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - HR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f2f2f2;
            padding: 40px;
        }
        .container {
            max-width: 450px;
            background-color: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            color: red;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[type="submit"] {
            background-color: red;
            color: white;
            border: none;
            margin-top: 25px;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .error, .success {
            padding: 10px;
            margin-top: 15px;
            border-radius: 6px;
            font-weight: bold;
        }
        .error {
            background-color: #ffe5e5;
            border: 1px solid red;
            color: red;
        }
        .success {
            background-color: #e6ffea;
            border: 1px solid #28a745;
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Change Password</h2>

    <?= $message ?>

    <form method="POST" action="">
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <input type="submit" value="Update Password">
    </form>
</div>

</body>
</html>
