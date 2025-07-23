<?php 
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize & collect form data
    $name = trim($_POST['name']);
    $employee_id = trim($_POST['employee_id']);
    $designation = trim($_POST['designation']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $hierarchy_level = trim($_POST['hierarchy_level']);
    $manager_name = trim($_POST['manager_name']);
    $mentor_name = isset($_POST['mentor_name']) ? trim($_POST['mentor_name']) : null;
    $location = trim($_POST['location']);
    $date_of_join = $_POST['date_of_join'];
    $date_of_birth = $_POST['date_of_birth'];

    // Validate L-2 must have a mentor
    if ($hierarchy_level === 'L-2' && empty($mentor_name)) {
        $_SESSION['error'] = "Mentor is mandatory for L-2 employees.";
        header("Location: add_employee.php");
        exit();
    }

    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;

        // Validate file extension
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Allowed: jpg, jpeg, png, gif.";
            header("Location: add_employee.php");
            exit();
        }

        if (move_uploaded_file($file_tmp, $target_file)) {
            $profile_picture = $target_file;
        } else {
            $_SESSION['error'] = "Failed to upload profile picture.";
            header("Location: add_employee.php");
            exit();
        }
    }

    try {
        // Check duplicate employee_id
        $check = $conn->prepare("SELECT COUNT(*) FROM employees WHERE employee_id = ?");
        $check->execute([$employee_id]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = "Employee ID already exists.";
            header("Location: add_employee.php");
            exit();
        }

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO employees 
            (name, employee_id, designation, email, phone, hierarchy_level, manager_name, mentor_name, location, date_of_join, date_of_birth, profile_picture)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $name,
            $employee_id,
            $designation,
            $email,
            $phone,
            $hierarchy_level,
            $manager_name,
            $mentor_name,
            $location,
            $date_of_join,
            $date_of_birth,
            $profile_picture
        ]);

        $_SESSION['message'] = "Employee added successfully.";
        header("Location: view_employees.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: add_employee.php");
        exit();
    }

} else {
    header("Location: add_employee.php");
    exit();
}
?>
