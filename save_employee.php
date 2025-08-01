<?php
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

require "config.php";

// Handle file upload (profile picture)
$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create upload directory if not exists
    }

    $fileName = basename($_FILES['profile_picture']['name']);
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid("IMG_", true) . '.' . $fileExt;
    $targetFilePath = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
        $profile_picture = $targetFilePath;
    }
}

// Collect form data safely
$employee_id     = $_POST['employee_id'];
$name            = $_POST['name'];
$email           = $_POST['email'];
$phone           = $_POST['phone'];
$date_of_birth   = $_POST['date_of_birth'];
$date_of_join    = $_POST['date_of_join'];
$designation     = $_POST['designation'];
$hierarchy_level = $_POST['hierarchy_level'];
$pid             = !empty($_POST['pid']) ? $_POST['pid'] : null; // Allow NULL if no manager
$location        = $_POST['location'];
$active_status   = $_POST['active_status'];

// Get manager name from ID if set
$manager_name = null;
if ($pid !== null) {
    $stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt->execute([$pid]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($manager) {
        $manager_name = $manager['name'];
    }
}

// Insert into DB
$sql = "INSERT INTO employees (
            pid, name, employee_id, designation, email, phone, hierarchy_level, 
            manager_name, location, date_of_birth, date_of_join, profile_picture
        ) VALUES (
            :pid, :name, :employee_id, :designation, :email, :phone, :hierarchy_level, 
            :manager_name, :location, :date_of_birth, :date_of_join, :profile_picture, 
        )";

$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    ':pid'            => $pid,
    ':name'           => $name,
    ':employee_id'    => $employee_id,
    ':designation'    => $designation,
    ':email'          => $email,
    ':phone'          => $phone,
    ':hierarchy_level'=> $hierarchy_level,
    ':manager_name'   => $manager_name,
    ':location'       => $location,
    ':date_of_birth'  => $date_of_birth,
    ':date_of_join'   => $date_of_join,
    ':profile_picture'=> $profile_picture,
    
]);

if ($success) {
    echo "<script>alert('Employee added successfully'); window.location.href='view_employees.php';</script>";
} else {
    echo "<script>alert('Error saving employee'); window.history.back();</script>";
}
?>
