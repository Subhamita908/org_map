<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill in all fields'); window.location.href='register.html';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format'); window.location.href='register.html';</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href='register.html';</script>";
        exit();
    }

    // Check if username or email already exists
    try {
        $stmt = $conn->prepare("SELECT * FROM hr_login WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Username or email already exists'); window.location.href='register.html';</script>";
            exit();
        }

        // Hash password using md5
        $hashed_password = md5($password);

        // Insert user into database
        $insert = $conn->prepare("INSERT INTO hr_login (username, email, password) VALUES (:username, :email, :password)");
        $insert->bindParam(':username', $username);
        $insert->bindParam(':email', $email);
        $insert->bindParam(':password', $hashed_password);
        $insert->execute();

        echo "<script>alert('Registration successful'); window.location.href='index.html';</script>";
        exit();
    } catch (PDOException $e) {
        error_log("Signup error: " . $e->getMessage());
        echo "<script>alert('Database error occurred'); window.location.href='register.html';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>HR Registration</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f4f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .register-box {
      background: #fff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      width: 400px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      width: 100%;
      padding: 12px;
      background: red;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background: #ccc;
    }
    .login-link {
      text-align: center;
      margin-top: 15px;
    }
    .login-link a {
      color: #0066cc;
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="register-box">
  <h2>iLogitron Technologies Pvt.Ltd <br> HR Registration</h2>
  <form action="signup.php" method="POST">
    <input type="text" name="username" placeholder="Username" required />
    <input type="email" name="email" placeholder="Email ID" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    <button type="submit">Register</button>
  </form>
  <div class="login-link">
    Already registered? <a href="index.html">Login here</a>
  </div>
</div>

</body>
</html>