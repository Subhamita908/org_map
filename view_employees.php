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
            <th>Location</th><th>Date of Birth</th><th>Date of Joining</th>
        </tr>";

    $stmt = $pdo->query("SELECT * FROM employees ORDER BY hierarchy_level ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['employee_id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['designation']}</td>
            <td>{$row['email']}</td>
            <td>{$row['phone']}</td>
            <td>{$row['hierarchy_level']}</td>
            <td>{$row['manager_name']}</td>
            <td>{$row['location']}</td>
            <td>{$row['date_of_birth']}</td>
            <td>{$row['date_of_join']}</td>
        </tr>";
    }
    echo "</table>";
    exit();
}

// Fetch for UI display
$stmt = $pdo->query("SELECT * FROM employees ORDER BY hierarchy_level ASC");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        }

        body {
            margin: 0;
            background-color: #f0f2f5;
        }

        .container {
            padding: 40px 20px;
            max-width: 100%;
            width: 1200px;
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        .top-bar input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            flex: 1;
        }

        .top-bar a.export-btn {
            background-color: green;
            color: white;
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .top-bar a.export-btn:hover {
            background-color: darkgreen;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: red;
            color: white;
        }

        img.profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        a {
            text-decoration: none;
            padding: 6px 12px;
            margin: 0 4px;
            border-radius: 4px;
            font-weight: 500;
        }

        a.edit {
            background-color: blue;
            color: white;
        }

        a.delete {
            background-color: red;
            color: white;
        }

        a.edit:hover, a.delete:hover {
            opacity: 0.7;
        }

        .scrollable-table {
            overflow-x: auto;
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
    <h2>Employee Directory</h2>

    <div class="top-bar">
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search by name, email, ID, phone, etc.">
        <a href="view_employees.php?export=excel" class="export-btn">Export to Excel</a>
    </div>

    <div class="scrollable-table">
        <table>
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Hierarchy</th>
                    <th>Manager</th>
                    <th>Location</th>
                    <th>DOB</th>
                    <th>DOJ</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $row): ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['profile_picture'])): ?>
                                <img src="<?= htmlspecialchars($row['profile_picture']) ?>" alt="Profile" class="profile-pic">
                            <?php else: ?>
                                <img src="default.png" alt="Default" class="profile-pic">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['employee_id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['designation']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['hierarchy_level']) ?></td>
                        <td><?= htmlspecialchars($row['manager_name']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['date_of_birth']) ?></td>
                        <td><?= htmlspecialchars($row['date_of_join']) ?></td>
                        <td>
                            <a href="edit_employee.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
                            <a href="delete_employee.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
