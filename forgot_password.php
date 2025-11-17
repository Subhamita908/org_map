<?php
session_start();
require 'config.php'; // This should define $pdo
require 'email_helper.php'; // Contains sendEmail() function

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM hr_login WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate new random password
        $newPassword = bin2hex(random_bytes(4)); // 8-character random string
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in DB
        $update = $pdo->prepare("UPDATE hr_login SET password = ? WHERE email = ?");
        if ($update->execute([$hashedPassword, $email])) {
            $subject = " Your Password Has Been Reset - Ilogitron HR";
            $body = "
                <p>Hi <strong>{$user['username']}</strong>,</p>
                <p>Your password has been reset successfully. Here is your new login password:</p>
                <p><strong style='font-size:18px;'>{$newPassword}</strong></p>
                <p>Please login and change your password immediately.</p>
                <p>Regards,<br>Ilogitron HR Team</p>
            ";

            if (sendEmail($email, $subject, $body)) {
                $message = "<p class='success'>Password has been reset. Please check your email.</p>";
            } else {
                $message = "<p class='error'>Failed to send email. Try again.</p>";
            }
        } else {
            $message = "<p class='error'> Failed to update password in the database.</p>";
        }
    } else {
        $message = "<p class='error'> No user found with this email.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f2f2;
            padding: 40px;
        }
        .container {
            max-width: 400px;
            background-color: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: red;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: red;
            color: white;
            border: none;
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .success {
            color: green;
            background: #e1ffe1;
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
        }
        .error {
            color: red;
            background: #ffe5e5;
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?= $message ?>

    <form method="POST">
        <label for="email">Enter Registered Email:</label>
        <input type="email" name="email" id="email" required>
        <input type="submit" value="Reset Password">
    </form>
</div>
</body>
</html>
