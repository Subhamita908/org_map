<?php
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $employee_id     = $_POST['employee_id'];
    $name            = $_POST['name'];
    $email           = $_POST['email'];
    $phone           = $_POST['phone'];
    $designation     = $_POST['designation'];
    $location        = $_POST['location'];
    $hierarchy_level = $_POST['hierarchy_level'];
    $manager_id      = !empty($_POST['pid']) ? $_POST['pid'] : NULL;
    $date_of_birth   = $_POST['date_of_birth'];
    $date_of_join    = $_POST['date_of_join'];
    $status          = $_POST['status']; // new field

    // Handle image upload
    $profile_picture = NULL;
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $filename;
        }
    }

    // Insert into DB (with status)
    $stmt = $pdo->prepare("INSERT INTO employees 
        (employee_id, name, email, phone, designation, location, hierarchy_level, pid, date_of_birth, date_of_join, profile_picture, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $employee_id,
        $name,
        $email,
        $phone,
        $designation,
        $location,
        $hierarchy_level,
        $manager_id,
        $date_of_birth,
        $date_of_join,
        $profile_picture,
        $status
    ]);

    header("Location: view_employees.php");
    exit();
}
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
            min-height: 100vh;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
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
            background-color: #fff;
        }

        select {
            cursor: pointer;
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

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            body {
                padding-top: 60px;
            }
            
            .container {
                margin: 10px;
                padding: 20px 15px;
                max-width: none;
            }
            
            h2 {
                font-size: 1.5em;
                margin-bottom: 20px;
            }
            
            input,
            select {
                padding: 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }

        @media screen and (max-width: 480px) {
            .container {
                margin: 5px;
                padding: 15px 10px;
            }
            
            h2 {
                font-size: 1.3em;
            }
            
            label {
                font-size: 14px;
            }
            
            button[type="submit"] {
                padding: 14px;
                font-size: 16px;
            }
        }

        /* Loading state for manager dropdown */
        #pid.loading {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3" fill="none" stroke="%23666" stroke-width="1" opacity="0.4"/><path d="M8 2 L8 5" stroke="%23666" stroke-width="2" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" values="0 8 8;360 8 8" dur="1s" repeatCount="indefinite"/></path></svg>');
            background-repeat: no-repeat;
            background-position: calc(100% - 10px) center;
            background-size: 16px 16px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            </select>

            <label for="location">Location</label>
            <select id="location" name="location" required>
                <option value="">-- Select Location --</option>
                <option value="Agartala">Agartala</option>
                <option value="Bangalore">Bangalore</option>
                <!-- Add more locations if needed -->
            </select>
            
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Active" selected>Active</option>
                <option value="Inactive">Inactive</option>
            </select>

            <label for="profile_picture">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" required>

            <button type="submit">Save Employee</button>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            $('#hierarchy_level').on('change', function () {
                var selectedLevel = $(this).val();
                var $managerSelect = $('#pid');

                if (selectedLevel !== "") {
                    // Add loading state
                    $managerSelect.addClass('loading');
                    $managerSelect.prop('disabled', true);
                    $managerSelect.html('<option value="">Loading managers...</option>');

                    $.ajax({
                        url: 'get_managers.php',
                        method: 'POST',
                        data: { level: selectedLevel },
                        timeout: 10000, // 10 second timeout
                        success: function (response) {
                            $managerSelect.removeClass('loading');
                            $managerSelect.prop('disabled', false);
                            
                            if (response && response.trim() !== '') {
                                $managerSelect.html(response);
                            } else {
                                $managerSelect.html('<option value="">-- No managers found --</option>');
                            }
                        },
                        error: function(xhr, status, error) {
                            $managerSelect.removeClass('loading');
                            $managerSelect.prop('disabled', false);
                            $managerSelect.html('<option value="">-- Error loading managers --</option>');
                            
                            console.error('AJAX Error:', {
                                status: status,
                                error: error,
                                responseText: xhr.responseText
                            });
                        }
                    });
                } else {
                    $managerSelect.removeClass('loading');
                    $managerSelect.prop('disabled', false);
                    $managerSelect.html('<option value="">-- Select Manager --</option>');
                }
            });

            // Debug: Log when hierarchy level changes
            $('#hierarchy_level').on('change', function() {
                console.log('Hierarchy level changed to:', $(this).val());
            });
        });
    </script>

</body>
</html>