<?php  
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require "config.php";

$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $employee_id = $_POST['employee_id'];
    $designation = $_POST['designation'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $hierarchy_level = $_POST['hierarchy_level'];
    $manager_name = $_POST['manager_name'];
    $mentor_name = $_POST['mentor_name'];
    $location = $_POST['location'];
    $date_of_birth = $_POST['date_of_birth'];
    $date_of_join = $_POST['date_of_join'];

    // Handle profile picture upload
    $profile_picture = null;
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $profile_picture = $upload_dir . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    // Fetch manager's id to set as pid
    $pid = null;
    if (!empty($manager_name)) {
        $stmt = $conn->prepare("SELECT id FROM employees WHERE name = ? AND location = ?");
        $stmt->execute([$manager_name, $location]);
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($manager) {
            $pid = $manager['id'];
        }
    }

    $stmt = $conn->prepare("INSERT INTO employees 
        (pid, name, employee_id, designation, email, phone, hierarchy_level, manager_name, mentor_name, location, date_of_birth, date_of_join, profile_picture)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $pid, $name, $employee_id, $designation, $email, $phone,
        $hierarchy_level, $manager_name, $mentor_name, $location,
        $date_of_birth, $date_of_join, $profile_picture
    ]);

    $success = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 720px; margin-top: 30px; }
        .form-card {
            background-color: #fff0f0;
            border: 2px solid gray;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(220, 20, 60, 0.1);
        }
        .btn-danger {
            width: 100%;
            font-weight: bold;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="container">
    <h2 class="mb-4 text-center">Add New Employee</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Employee added successfully!</div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Employee ID</label>
                <input type="text" name="employee_id" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Designation</label>
                <input type="text" name="designation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" pattern="\d{10}" required>
            </div>

            <div class="mb-3">
                <label>Hierarchy Level</label>
                <select name="hierarchy_level" id="hierarchy_level" class="form-select" required onchange="fetchManagers()">
                    <option value="">-- Select --</option>
                    <option value="L-8">L-8</option>
                    <option value="L-7">L-7</option>
                    <option value="L-6">L-6</option>
                    <option value="L-5">L-5</option>
                    <option value="L-4">L-4</option>
                    <option value="L-3">L-3</option>
                    <option value="L-2">L-2</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Location</label>
                <select name="location" id="location" class="form-select" required onchange="fetchManagers()">
                    <option value="">-- Select Location --</option>
                    <option value="Agartala">Agartala</option>
                    <option value="Bangalore">Bangalore</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Date of Joining</label>
                <input type="date" name="date_of_join" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control" accept="image/*">
            </div>

            <div class="mb-3">
                <label>Manager</label>
                <select name="manager_name" id="manager_name" class="form-select" required>

                    <option value="">-- Select Manager --</option>

                </select>
            </div>

            <div class="mb-3">
                <label>Mentor <span id="mentor_required" class="text-danger" style="display:none;">*</span></label>
                <select name="mentor_name" id="mentor_name" class="form-select">
                    <option value="">-- Select Mentor --</option>
                </select>
            </div>

            <button type="submit" class="btn btn-danger">Add Employee</button>
        </form>
    </div>
</div>

<script>
function fetchManagers() {
    const level = document.getElementById("hierarchy_level").value;
    const location = document.getElementById("location").value;

    if (!level || !location) return;

    fetch(`get_managers.php?level=${level}&location=${location}`)
        .then(res => res.text())
        .then(data => {
            document.getElementById("manager_name").innerHTML = data;
            document.getElementById("mentor_name").innerHTML = data;
        });

    // Mentor is required only for L-2
    const mentorReq = document.getElementById("mentor_required");
    const mentorSelect = document.getElementById("mentor_name");
    if (level === "L-2") {
        mentorReq.style.display = "inline";
        mentorSelect.setAttribute("required", "required");
    } else {
        mentorReq.style.display = "none";
        mentorSelect.removeAttribute("required");
    }
}
</script>
</body>
</html>
