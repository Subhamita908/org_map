<?php
session_start();
require 'config.php';

if (!isset($_SESSION['hr_logged_in']) || !isset($_SESSION['hr_email'])) {
    header("Location: index.html");
    exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $hr_email = $_SESSION['hr_email'];

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        // Fetch current hashed password
        $stmt = $conn->prepare("SELECT password FROM hr WHERE email = ?");
        $stmt->execute([$hr_email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($current_password, $row['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE hr SET password = ? WHERE email = ?");
            if ($update->execute([$hashed_password, $hr_email])) {
                $success = "Password changed successfully.";
            } else {
                $error = "Something went wrong while updating the password.";
            }
        } else {
            $error = "Current password is incorrect.";
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
            background-color: #f4f4f4;
            margin: 0;
            padding: 2rem;
        }
        form {
            background: #fff;
            padding: 2rem;
            max-width: 420px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }
        label {
            display: block;
            margin: 0.5rem 0 0.2rem;
            color: #333;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            background-color: red;
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }
        input[type="submit"]:hover {
            background-color: red;
        }
        .message {
            text-align: center;
            color: green;
            margin-bottom: 1rem;
        }
        .error {
            text-align: center;
            color: red;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <form method="POST" action="">
        <h2>Change Password</h2>

        <?php if ($success): ?>
            <p class="message"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <input type="submit" value="Change Password">
    </form>

</body>
</html>
