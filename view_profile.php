<?php  
session_start();
if (!isset($_SESSION['hr_logged_in']) || $_SESSION['hr_logged_in'] !== true) {
    header("Location: index.html");
    exit();
}

require 'config.php'; // This should return a PDO instance as $pdo

$hr_username = $_SESSION['hr_username'] ?? null;

if (!$hr_username) {
    echo "Error: HR username not set in session.";
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, username, email, profile_image FROM hr_login WHERE username = ?");
    $stmt->execute([$hr_username]);
    $hr_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Profile - Ilogitron Technologies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .profile-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 2px solid #ccc;
        }
        .profile-info label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
            color: #555;
        }
        .profile-info p {
            margin: 5px 0 15px;
            color: #333;
        }
        .profile-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .profile-actions a {
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            background-color: red;
            color: white;
            font-weight: bold;
        }
        .profile-actions a.delete {
            background-color: red;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php';?>

<!-- Profile Section -->
<div class="profile-container">
    <h2>Your Profile</h2>
    <?php if ($hr_data): ?>
        <?php
        $profileImage = $hr_data['profile_image'] ?? '';
        $image_path = (!empty($profileImage) && file_exists("uploads/hr_profiles/" . $profileImage))
            ? "uploads/hr_profiles/" . $profileImage
            : "assets/default_avatar.png";
        ?>
        <div style="margin: 20px auto;">
            <img src="<?= htmlspecialchars($image_path) ?>" alt="Profile Picture" style="width: 250px; height: 250px; border-radius: 70%; object-fit: cover; border: 2px solid #ccc; padding: 5px; background-color: #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
        </div>

        <div class="profile-info">
            <label>Username:</label>
            <p><?= htmlspecialchars($hr_data['username']) ?></p>

            <label>Email:</label>
            <p><?= htmlspecialchars($hr_data['email']) ?></p>
        </div>

        <div class="profile-actions">
            <a href="edit_profile.php?id=<?= $hr_data['id'] ?>">Edit</a>
            <a href="delete_profile.php?id=<?= $hr_data['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete your profile? This action is irreversible.');">Delete</a>
            <a href="change_password.php">Change Password</a>
        </div>
    <?php else: ?>
        <p style="color:red;">Profile data not found.</p>
    <?php endif; ?>
</div>

</body>
</html>
