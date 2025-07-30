<?php
require_once "config.php";

// Fetch employee details
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo "Employee not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}

// Update employee on form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $employee_id = $_POST['employee_id'];
    $designation = $_POST['designation'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $hierarchy_level = $_POST['hierarchy_level'];
    $pid = $_POST['pid'] ?? NULL;
    $location = $_POST['location'];
    $dob = $_POST['date_of_birth'];
    $doj = $_POST['date_of_join'];

    // Handle profile picture
    $profile_picture = $employee['profile_picture'];
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;
    }

    // Get manager name from pid
    $manager_name = NULL;
    if (!empty($pid)) {
        $stmt = $pdo->prepare("SELECT name FROM employees WHERE id = ?");
        $stmt->execute([$pid]);
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
        $manager_name = $manager['name'] ?? NULL;
    }

    $update_stmt = $pdo->prepare("UPDATE employees SET name=?, employee_id=?, designation=?, email=?, phone=?, hierarchy_level=?, pid=?, manager_name=?, location=?, date_of_birth=?, date_of_join=?, profile_picture=? WHERE id=?");
    $update_stmt->execute([$name, $employee_id, $designation, $email, $phone, $hierarchy_level, $pid, $manager_name, $location, $dob, $doj, $profile_picture, $id]);

    echo "<script>alert('Employee updated successfully!'); window.location.href='view_employees.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 20px; }
        .container { background: white; padding: 20px; max-width: 700px; margin: auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin: 8px 0; }
        label { font-weight: bold; }
        button { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        img { max-width: 100px; margin-top: 10px; }
    </style>
    <script>
        function fetchManagers() {
            const level = document.getElementById("hierarchy_level").value;
            const location = document.getElementById("location").value;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "get_managers.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                document.getElementById("pid").innerHTML = this.responseText;
            };
            xhr.send("hierarchy_level=" + encodeURIComponent(level) + "&location=" + encodeURIComponent(location));
        }

        window.onload = function () {
            fetchManagers(); // Preload manager list
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Employee</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($employee['name']) ?>" required>

            <label>Employee ID:</label>
            <input type="text" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>" required>

            <label>Designation:</label>
            <input type="text" name="designation" value="<?= htmlspecialchars($employee['designation']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($employee['phone']) ?>" required>

            <label>Hierarchy Level:</label>
            <select name="hierarchy_level" id="hierarchy_level" onchange="fetchManagers()" required>
                <?php
                for ($i = 2; $i <= 8; $i++) {
                    $level = "L-" . $i;
                    echo "<option value='$level'" . ($employee['hierarchy_level'] == $level ? " selected" : "") . ">$level</option>";
                }
                ?>
            </select>

            <label>Location:</label>
            <select name="location" id="location" onchange="fetchManagers()" required>
                <?php
                $locations = ['Kolkata', 'Bangalore', 'Hyderabad'];
                foreach ($locations as $loc) {
                    echo "<option value='$loc'" . ($employee['location'] == $loc ? " selected" : "") . ">$loc</option>";
                }
                ?>
            </select>

            <label>Manager:</label>
            <select name="pid" id="pid" required></select>

            <label>Date of Birth:</label>
            <input type="date" name="date_of_birth" value="<?= $employee['date_of_birth'] ?>" required>

            <label>Date of Joining:</label>
            <input type="date" name="date_of_join" value="<?= $employee['date_of_join'] ?>" required>

            <label>Profile Picture:</label>
            <input type="file" name="profile_picture" accept="image/*">
            <?php if ($employee['profile_picture']): ?>
                <img src="<?= htmlspecialchars($employee['profile_picture']) ?>" alt="Current Picture">
            <?php endif; ?>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
