<?php
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require "config.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Employee | Ilogitron Technologies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #eef;
            padding-top: 80px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background-color: #fff;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #444;
        }

        input,
        select {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: red;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #b30000;
        }
    </style>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="container">
        <h2>Add New Employee</h2>
        <form action="save_employee.php" method="POST" enctype="multipart/form-data">

            <label for="employee_id">Employee ID</label>
            <input type="text" id="employee_id" name="employee_id" placeholder="Enter Employee ID" required>

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter Full Name" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter Email Address" required>

            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" placeholder="Enter Phone Number" required>

            <label for="date_of_birth">Date of Birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" required>

            <label for="date_of_join">Date of Joining</label>
            <input type="date" id="date_of_join" name="date_of_join" required>

            <label for="designation">Designation</label>
            <input type="text" id="designation" name="designation" placeholder="Enter Designation" required>

            <label for="hierarchy_level">Hierarchy Level</label>
            <select id="hierarchy_level" name="hierarchy_level" required>
                <option value="">-- Select Level --</option>
                <option value="L-2">L-2</option>
                <option value="L-3">L-3</option>
                <option value="L-4">L-4</option>
                <option value="L-5">L-5</option>
                <option value="L-6">L-6</option>
                <option value="L-7">L-7</option>
                <option value="L-8">L-8</option>
            </select>

            <label for="pid">Manager</label>
            <select id="pid" name="pid">
                <option value="">-- Select Manager --</option>
                <?php
                // $stmt = $pdo->query("SELECT id, name, hierarchy_level FROM employees ORDER BY hierarchy_level ASC");
                // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //     echo "<option value='{$row['id']}'>{$row['name']} ({$row['hierarchy_level']})</option>";
                // }
                $stmt = $pdo->query("SELECT name, designation, hierarchy_level 
                FROM employees 
                WHERE hierarchy_level IN ($placeholders) 
                ORDER BY FIELD(hierarchy_level, $placeholders)");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['id']}'>{$row['name']} ({$row['hierarchy_level']})</option>";
                }
                ?>
            </select>

            <label for="location">Location</label>
            <select id="location" name="location" required>
                <option value="">-- Select Location --</option>
                <option value="Agartala">Agartala</option>
                <option value="Bangalore">Bangalore</option>
                <!-- Add more locations if needed -->
            </select>

            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">


            <button type="submit">Save Employee</button>
        </form>
    </div>

</body>

</html>