<?php
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

require 'config.php';

// Export logic
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=employees_" . date("Ymd_His") . ".xls");

    echo "<table border='1'>";
    echo "<tr>
            <th>Employee ID</th><th>Name</th><th>Designation</th>
            <th>Email</th><th>Phone</th><th>Hierarchy</th><th>Manager</th>
            <th>Location</th><th>Date of Joining</th>
        </tr>";

    $stmt = $pdo->query("SELECT * FROM employees ORDER BY hierarchy_level ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>" . htmlspecialchars($row['employee_id'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['name'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['designation'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['email'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['phone'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['hierarchy_level'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['manager_name'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['location'] ?? '') . "</td>
            <td>" . htmlspecialchars($row['date_of_join'] ?? '') . "</td>
        </tr>";
    }
    echo "</table>";
    exit();
}

// Fetch for UI display
$stmt = $pdo->query("
    SELECT 
        e.*, 
        m.name AS manager_name 
    FROM employees e 
    LEFT JOIN employees m ON e.pid = m.id 
    ORDER BY e.hierarchy_level ASC
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate stats
$total_employees = count($employees);
$active_employees = $total_employees;
$open_positions = 2;
$departments = 3;

function getInitials($name) {
    $words = preg_split('/\s+/', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return $initials;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Directory | Ilogitron Technologies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f0f2f5;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            margin: 20px auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #d32f2f;
            color: white;
            padding: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 1.75rem;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 0.875rem;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .stat-box {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            min-width: 120px;
        }

        .stat-box span {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }

        .stat-box label {
            font-size: 0.875rem;
        }

        .top-bar {
            padding: 20px;
            background-color: white;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .top-bar input[type="text"] {
            padding: 10px;
            font-size: 0.875rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            flex: 1;
            min-width: 200px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 0.875rem;
        }

        .add-btn {
            background-color: #d32f2f;
        }

        .export-btn {
            background-color: green;
        }

        .print-btn {
            background-color: #ff9800;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        th,
        td {
            padding: 15px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #d32f2f;
            color: white;
            text-transform: uppercase;
        }

        .employee-cell {
            display: flex;
            align-items: center;
        }

        .initials {
            background-color: #e0e0e0;
            color: #333;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .employee-info span {
            display: block;
            font-size: 0.875rem;
            color: #333;
        }

        .employee-info small {
            font-size: 0.75rem;
            color: #666;
        }

        .designation-pill {
            background-color: #ffcdd2;
            color: #b71c1c;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-block;
        }

        .contact-cell span {
            display: block;
            font-size: 0.875rem;
        }

        .contact-cell small {
            font-size: 0.75rem;
            color: #666;
        }

        .level-pill {
            background-color: #c8e6c9;
            color: #388e3c;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            display: inline-block;
        }

        .action-btn {
            padding: 8px 15px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 0.75rem;
            margin-right: 5px;
        }

        .edit-btn {
            background-color: #222;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .scrollable-table {
            overflow-x: auto;
            padding: 0 20px 20px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }

            .stats {
                justify-content: center;
            }

            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .top-bar input[type="text"] {
                width: 100%;
            }

            .buttons {
                justify-content: center;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            th,
            td {
                min-width: 120px;
            }

            .employee-cell {
                flex-direction: column;
                text-align: center;
            }

            .initials,
            .profile-image {
                margin: 0 0 10px 0;
            }
        }
    </style>

    <script>
        function searchTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("tbody tr");

            rows.forEach(row => {
                let match = Array.from(row.cells).some(td => td.textContent.toLowerCase().includes(input));
                row.style.display = match ? "" : "none";
            });
        }
    </script>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Employee Directory</h1>
            <p>Manage and view all employees in your organization</p>
            <div class="stats">
                <div class="stat-box">
                    <span><?php echo $total_employees; ?></span>
                    <label>Total Employees</label>
                </div>
                <div class="stat-box">
                    <span><?php echo $active_employees; ?></span>
                    <label>Active Employees</label>
                </div>
                <div class="stat-box">
                    <span><?php echo $open_positions; ?></span>
                    <label>Open Positions</label>
                </div>
                <div class="stat-box">
                    <span><?php echo $departments; ?></span>
                    <label>Departments</label>
                </div>
            </div>
        </div>

        <div class="top-bar">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search employees...">
            <div class="buttons">
                <a href="add_employee.php" class="btn add-btn">+ Add Employee</a>
                <a href="view_employees.php?export=excel" class="btn export-btn">Export Excel</a>
                <button onclick="window.print()" class="btn print-btn">Print</button>
            </div>
        </div>

        <div class="scrollable-table">
            <table>
                <thead>
                    <tr>
                        <th>EMPLOYEE</th>
                        <th>DESIGNATION</th>
                        <th>PHONE</th>
                        <th>LEVEL</th>
                        <th>MANAGER</th>
                        <th>LOCATION</th>
                        <th>JOINING DATE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $row): ?>
                        <tr>
                            <td>
                                <div class="employee-cell">
                                    <?php if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="<?php echo htmlspecialchars($row['name'] ?? ''); ?>'s Profile Picture" class="profile-image">
                                    <?php else: ?>
                                        <div class="initials"><?php echo getInitials($row['name'] ?? ''); ?></div>
                                    <?php endif; ?>
                                    <div class="employee-info">
                                        <span><?php echo htmlspecialchars($row['name'] ?? ''); ?></span>
                                        <small><?php echo htmlspecialchars($row['employee_id'] ?? ''); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="designation-pill"><?php echo htmlspecialchars($row['designation'] ?? ''); ?></span>
                            </td>
                            <td class="contact-cell">
                                <span><?php echo htmlspecialchars($row['email'] ?? ''); ?></span>
                                <small><?php echo htmlspecialchars($row['phone'] ?? ''); ?></small>
                            </td>
                            <td>
                                <span class="level-pill"><?php echo htmlspecialchars($row['hierarchy_level'] ?? ''); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($row['manager_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['date_of_join'] ?? ''); ?></td>
                            <td>
                        <span class="status-indicator" style="color: <?= $row['status'] === 'Active' ? 'green' : 'red' ?>;">●</span>
                        <?= htmlspecialchars($row['status']) ?>
                    </td>
                            <td>
                                <a href="edit_employee.php?id=<?php echo $row['id'] ?? ''; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="delete_employee.php?id=<?php echo $row['id'] ?? ''; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>