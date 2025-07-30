<?php
require "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $employee_id = $_POST['employee_id'];
    $designation = $_POST['designation'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $hierarchy_level = $_POST['hierarchy_level'];
    $location = $_POST['location'];
    $dob = $_POST['date_of_birth'];
    $doj = $_POST['date_of_join'];
    $pid = $_POST['pid'] ?? null;

    // Get manager name
    $manager_name = null;
    if (!empty($pid)) {
        $stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ?");
        $stmt->execute([$pid]);
        $manager_name = $stmt->fetchColumn();
    }

    // Upload profile picture
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);
        $profile_picture = $target_dir . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO employees 
        (pid, name, employee_id, designation, email, phone, hierarchy_level, manager_name, location, date_of_birth, date_of_join, profile_picture, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$pid, $name, $employee_id, $designation, $email, $phone, $hierarchy_level, $manager_name, $location, $dob, $doj, $profile_picture]);

    echo "<script>alert('Employee added successfully!'); window.location.href='view_employees.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
        }

        .form-card {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-title {
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: bold;
        }

        .btn-submit {
            width: 100%;
        }
    </style>

    <script>
        function fetchManagers() {
            const level = document.getElementById('hierarchy_level').value;
            const location = document.getElementById('location').value;

            const managerDropdown = document.getElementById('managerDropdown');
            managerDropdown.innerHTML = '<option>Loading...</option>';
if (!level) {
    managerDropdown.innerHTML = '<option value="">Select manager</option>';
    return;
}


            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_managers.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (this.status === 200) {
                    managerDropdown.innerHTML = this.responseText;
                }
            };
            xhr.send('level=' + encodeURIComponent(level) + '&location=' + encodeURIComponent(location));
        }

    </script>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="form-card">
            <div class="form-title">Add New Employee</div>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Name:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Employee ID:</label>
                    <input type="text" name="employee_id" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Designation:</label>
                    <input type="text" name="designation" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone:</label>
                    <input type="text" name="phone" class="form-control" maxlength="10" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hierarchy Level:</label>
                    <select name="hierarchy_level" id="hierarchy_level" class="form-select" onchange="fetchManagers()"
                        required>
                        <option value="">Select Level</option>
                        <option value="L-2">L-2</option>
                        <option value="L-3">L-3</option>
                        <option value="L-4">L-4</option>
                        <option value="L-5">L-5</option>
                        <option value="L-6">L-6</option>
                        <option value="L-7">L-7</option>
                        <option value="L-8">L-8</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Location:</label>
                    <select type="text" name="location" id="location" class="form-select" required>
                        <option value="Agartala">Agartala</option>
                        <option value="Bangalore">Bangalore</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Manager Name:</label>
                    <!-- Manager Dropdown: REMOVE the onchange -->
                    <select name="pid" id="managerDropdown" class="form-select" required>
                        <option value="">Select manager</option>
                        
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date of Birth:</label>
                    <input type="date" name="date_of_birth" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date of Joining:</label>
                    <input type="date" name="date_of_join" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Profile Picture:</label>
                    <input type="file" name="profile_picture" class="form-control">
                </div>

                <button type="submit" class="btn btn-danger btn-submit">Add Employee</button>
            </form>
        </div>
    </div>
</body>

</html>