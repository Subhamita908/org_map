<?php
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $hr_email = $_SESSION['hr_email']; // Email stored during login

    // Password validation
    if ($new_password !== $confirm_password) {
        $error = "❌ New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ Password must be at least 6 characters.";
    } else {
        // Fetch current hashed password from DB
        $stmt = $conn->prepare("SELECT password FROM hr WHERE email = ?");
        $stmt->execute([$hr_email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($current_password, $row['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE hr SET password = ? WHERE email = ?");
            if ($update->execute([$hashed_password, $hr_email])) {
                $success = "✅ Password changed successfully.";
            } else {
                $error = "❌ Failed to update password. Please try again.";
            }
        } else {
            $error = "❌ Current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password | HR Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #eef1f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            border: none;
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            color: green;
            margin-top: 15px;
        }
        .error {
            text-align: center;
            color: red;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Change Password</h2>
        <?php if ($success): ?><p class="message"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <label for="current_password">Current Password</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <input type="submit" value="Change Password">
    </form>
</body>
</html>
