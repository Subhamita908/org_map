<?php
session_start();
require 'config.php';

// ✅ Check if `id` is passed
if (!isset($_GET['id'])) {
    header("Location: view_employees.php");
    exit();
}

$id = (int) $_GET['id'];

// ✅ Fetch existing employee
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "Employee not found.";
    exit();
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id     = trim($_POST['employee_id']);
    $name            = trim($_POST['name']);
    $designation     = trim($_POST['designation']);
    $email           = trim($_POST['email']);
    $phone           = trim($_POST['phone']);
    $hierarchy_level = trim($_POST['hierarchy_level']);
    $manager_id      = !empty($_POST['pid']) ? (int)$_POST['pid'] : null; // 🔹 maps to `pid`
    $location        = trim($_POST['location']);
    $date_of_birth   = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $date_of_join    = !empty($_POST['date_of_join']) ? $_POST['date_of_join'] : null;
    $manager_name    = trim($_POST['manager_name']);
    $status          = $_POST['status']; // 🔹 new field

    // ✅ Handle profile picture upload
    $profile_picture = $employee['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . time() . "_" . basename($_FILES["profile_picture"]["name"]);
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_picture = $target_file;
        }
    }

    // ✅ Update query with status
    $stmt = $pdo->prepare("UPDATE employees 
        SET employee_id=?, name=?, designation=?, email=?, phone=?, hierarchy_level=?, pid=?, location=?, date_of_birth=?, date_of_join=?, profile_picture=?, manager_name=?, status=? 
        WHERE id=?");

    $stmt->execute([
        $employee_id, $name, $designation, $email, $phone, $hierarchy_level,
        $manager_id, $location, $date_of_birth, $date_of_join, $profile_picture,
        $manager_name, $status, $id
    ]);

    header("Location: view_employees.php?success=1");
    exit();
}

// ✅ Fetch managers for dropdown
$managers = $pdo->query("SELECT id, name, designation FROM employees ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: red;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: darkred;
        }
        .profile-preview {
            margin-bottom: 15px;
        }
        .profile-preview img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php';?>
<div class="container">
    <h2>Edit Employee</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Employee ID</label>
        <input type="text" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>" required>

        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required>

        <label>Designation</label>
        <input type="text" name="designation" value="<?= htmlspecialchars($employee['designation']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($employee['phone']) ?>">

        <label>Hierarchy Level</label>
        <input type="text" name="hierarchy_level" value="<?= htmlspecialchars($employee['hierarchy_level']) ?>">

        <label>Manager Name</label>
        <select name="pid">
            <option value="">-- None --</option>
            <?php foreach ($managers as $manager): ?>
                <option value="<?= $manager['id'] ?>" <?= ($employee['pid'] == $manager['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($manager['name'] . " (" . $manager['designation'] . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Location</label>
        <select name="location">
            <option value="Agartala" <?= ($employee['location'] == 'Agartala') ? 'selected' : '' ?>>Agartala</option>
            <option value="Bangalore" <?= ($employee['location'] == 'Bangalore') ? 'selected' : '' ?>>Bangalore</option>
        </select>

        <label>Status</label>
        <select name="status" required>
            <option value="Active" <?= ($employee['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= ($employee['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
        </select>

        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($employee['date_of_birth']) ?>">

        <label>Date of Joining</label>
        <input type="date" name="date_of_join" value="<?= htmlspecialchars($employee['date_of_join']) ?>">

        <label>Profile Picture</label>
        <div class="profile-preview">
            <?php if (!empty($employee['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($employee['profile_picture']) ?>" alt="Profile Picture">
            <?php endif; ?>
        </div>
        <input type="file" name="profile_picture">

        <button type="submit">Update Employee</button>
    </form>
</div>
</body>
</html>
