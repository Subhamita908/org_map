<?php 
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

require "config.php";

// Handle image upload
$profile_picture = null;
if (!empty($_FILES['profile_picture']['name'])) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $filename = time() . '_' . basename($_FILES['profile_picture']['name']);
    $target_file = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        $profile_picture = $target_file;
    }
}

// Collect form data
$employee_id     = $_POST['employee_id'];
$name            = $_POST['name'];
$email           = $_POST['email'];
$phone           = $_POST['phone'];
$date_of_birth   = $_POST['date_of_birth'];
$date_of_join    = $_POST['date_of_join'];
$designation     = $_POST['designation'];
$hierarchy_level = $_POST['hierarchy_level'];
$location        = $_POST['location'];

// Handle manager - convert manager_id to manager_name if needed
$manager_name = null;
if (!empty($_POST['manager_id'])) {
    // If you're receiving manager_id, fetch the manager's name
    $manager_stmt = $pdo->prepare("SELECT name FROM employees WHERE id = :manager_id");
    $manager_stmt->execute([':manager_id' => $_POST['manager_id']]);
    $manager = $manager_stmt->fetch(PDO::FETCH_ASSOC);
    $manager_name = $manager ? $manager['name'] : null;
} elseif (!empty($_POST['manager_name'])) {
    // If you're receiving manager_name directly
    $manager_name = $_POST['manager_name'];
}

// Handle pid (parent id) - this should match your table structure
$pid = !empty($_POST['pid']) ? $_POST['pid'] : null;

// Insert into database (matching your actual table structure)
$sql = "INSERT INTO employees (
            name, email, designation, employee_id, location, hierarchy_level, 
            manager_name, date_of_birth, date_of_join, profile_picture, pid, phone, status
        ) VALUES (
            :name, :email, :designation, :employee_id, :location, :hierarchy_level, 
            :manager_name, :date_of_birth, :date_of_join, :profile_picture, :pid, :phone, :status
        )";

$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    ':name'            => $name,
    ':email'           => $email,
    ':designation'     => $designation,
    ':employee_id'     => $employee_id,
    ':location'        => $location,
    ':hierarchy_level' => $hierarchy_level,
    ':manager_name'    => $manager_name,
    ':date_of_birth'   => $date_of_birth,
    ':date_of_join'    => $date_of_join,
    ':profile_picture' => $profile_picture,
    ':pid'             => $pid,
    ':phone'           => $phone,
    ':status'          => 'Active' // Default status
]);

if ($success) {
    echo "<script>alert('Employee added successfully'); window.location.href='view_employees.php';</script>";
} else {
    echo "<script>alert('Error saving employee'); window.history.back();</script>";
}
?>