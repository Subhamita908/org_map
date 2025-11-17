<?php
session_start();
if (!isset($_SESSION['hr_logged_in']) || $_SESSION['hr_logged_in'] !== true) {
    header("Location: index.html");
    exit();
}

require 'config.php';

$hr_id = $_GET['id'] ?? null;

if (!$hr_id) {
    echo "Invalid request.";
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Handle profile image upload
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);
        $uploadDir = "uploads/hr_profiles/";
        $targetPath = $uploadDir . time() . "_" . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $profile_image = basename($targetPath);
        } else {
            $message = "Error uploading image.";
        }
    }

    // Update query
    try {
        if ($profile_image) {
            $stmt = $pdo->prepare("UPDATE hr_login SET username = ?, email = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$username, $email, $profile_image, $hr_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE hr_login SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hr_id]);
        }
        $message = "Profile updated successfully.";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch existing HR data
$stmt = $pdo->prepare("SELECT * FROM hr_login WHERE id = ?");
$stmt->execute([$hr_id]);
$hr = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hr) {
    echo "HR record not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit HR Profile</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 0 10px #ccc;
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: red;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border: 1px solid #aaa;
            border-radius: 4px;
        }

        button {
            margin-top: 20px;
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .message {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            color: green;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #333;
            text-decoration: none;
        }

        img {
            max-width: 150px;
            height: auto;
            margin-top: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($hr['username']) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($hr['email']) ?>" required>

        <label>Current Profile Image:</label><br>
        <?php
        $image = $hr['profile_image'] ? "uploads/hr_profiles/" . $hr['profile_image'] : "assets/default_avatar.png";
        ?>
        <img src="<?= htmlspecialchars($image) ?>" alt="Profile Image">

        <label>Change Profile Image:</label>
        <input type="file" name="profile_image" accept="image/*">

        <button type="submit">Update Profile</button>
    </form>

    <div class="back-link">
        <a href="view_profile.php">← Back to Profile</a>
    </div>
</div>

</body>
</html>
