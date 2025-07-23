<?php
session_start();
require 'config.php';

if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$id = $_GET['id'];

// Fetch employee details
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    echo "Employee not found.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $employee_id = trim($_POST['employee_id']);
    $designation = trim($_POST['designation']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $manager_name = trim($_POST['manager_name']);
    $location = trim($_POST['location']);

    $newImage = $employee['profile_picture'];

    // Handle image upload
    // if (!empty($_FILES['profile_picture']['name'])) {
    //     $imgName = basename($_FILES['profile_picture']['name']);
    //     $targetDir = " ";
    //     $targetFile = $targetDir . uniqid() . "_" . $imgName;

    //     // Validate image file type
    //     $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    //     $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    //     if (!in_array($fileType, $allowedTypes)) {
    //         echo "<script>alert('Only JPG, PNG, and GIF files are allowed.');</script>";
    //     } else {
    //         if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
    //             // Delete old image if it exists
    //             if (!empty($employee['profile_picture']) && file_exists("uploads/" . $employee['profile_picture'])) {
    //                 unlink("uploads/" . $employee['profile_picture']);
    //             }
    //             $newImage = basename($targetFile);
    //         } else {
    //             echo "<script>alert('Image upload failed.');</script>";
    //         }
    //     }
    // }

    // Handle profile picture upload
    $profile_picture = "";
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $profile_picture = $_FILES['profile_picture']['name'];
        // move_uploaded_file($_FILES['profile_picture']['name'], $profile_picture);
    }
    

    //  echo  $profile_picture;
    // Update employee record
    $update = $conn->prepare("UPDATE employees SET name = ?, employee_id = ?, designation = ?, email = ?, phone = ?, manager_name = ?, location = ?, profile_picture = ? WHERE id = ?");
    $success = $update->execute([
        $name, $employee_id, $designation, $email, $phone, $manager_name, $location ,$profile_picture, $id
    ]);

    if ($success) {
        echo "<script>alert('Employee updated successfully!'); window.location.href='view_employees.php';</script>";
    } else {
        echo "<script>alert('Error updating employee.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 40px;
            max-width: 600px;
        }
        .form-label {
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 5px;
        }
        img.preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-top: 10px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container">
    <h2>Edit Employee</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($employee['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Employee ID:</label>
            <input type="text" name="employee_id" class="form-control" value="<?= htmlspecialchars($employee['employee_id']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Designation:</label>
            <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($employee['designation']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone:</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($employee['phone']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Manager Name:</label>
            <input type="text" name="manager_name" class="form-control" value="<?= htmlspecialchars($employee['manager_name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Location:</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($employee['location']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Picture:</label>
            <input type="file" name="profile_picture" class="form-control">
            <?php if (!empty($employee['profile_picture']) && file_exists("" . $employee['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($employee['profile_picture']) ?>" class="preview" alt="Current">
            <?php else: ?>
                <img src="default.png" class="preview" alt="Default">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="view_employees.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
