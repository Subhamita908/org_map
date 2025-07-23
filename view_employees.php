<?php 
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

require 'config.php';

// Handle search
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchTerm !== '') {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE name LIKE :search1 OR employee_id LIKE :search2 ORDER BY hierarchy_level ASC");
    $stmt->execute([
        ':search1' => "%$searchTerm%",
        ':search2' => "%$searchTerm%"
    ]);
} else {
    $stmt = $conn->prepare("SELECT * FROM employees ORDER BY hierarchy_level ASC");
    $stmt->execute();
}
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Excel Export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=export_employees_list_" . date('Y-m-d') . ".xls");

    echo "Name\tEmployee ID\tDesignation\tEmail\tPhone\tManager\tLocation\n";
    foreach ($employees as $emp) {
        echo "{$emp['name']}\t{$emp['employee_id']}\t{$emp['designation']}\t{$emp['email']}\t{$emp['phone']}\t{$emp['manager_name']}\t{$emp['location']}\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        .table-container {
            margin: 40px auto;
            width: 95%;
        }
        .table th {
            background-color: red;
            color: #fff;
        }
        .profile-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .btn-export {
            float: right;
            margin-bottom: 10px;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-container form {
            display: flex;
            align-items: center;
        }
        .search-container input[type="text"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
            outline: none;
        }
        .search-container button {
            padding: 8px 16px;
            border: none;
            background-color: crimson;
            color: white;
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="container table-container">

    <div class="search-container">
        <h2>All Employees</h2>
        <form method="GET" action="view_employees.php">
            <input type="text" name="search" placeholder="Search by Name or ID" value="<?= htmlspecialchars($searchTerm) ?>" required>
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <a href="view_employees.php?export=excel<?= $searchTerm !== '' ? '&search=' . urlencode($searchTerm) : '' ?>" class="btn btn-success btn-export">
        <i class="fas fa-file-excel"></i> Export to Excel
    </a>

    <table class="table table-bordered table-hover table-striped align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Employee ID</th>
                <th>Designation</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Manager</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <?php if (!empty($emp['profile_picture']) && file_exists('' . $emp['profile_picture'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($emp['profile_picture']); ?>" class="profile-img" alt="Profile">
                            <?php else: ?>
                                <img src=" " class="profile-img" alt="Default">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($emp['name']) ?></td>
                        <td><?= htmlspecialchars($emp['employee_id']) ?></td>
                        <td><?= htmlspecialchars($emp['designation']) ?></td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td><?= htmlspecialchars($emp['phone']) ?></td>
                        <td><?= htmlspecialchars($emp['manager_name']) ?></td>
                        <td><?= htmlspecialchars($emp['location']) ?></td>
                        <td>
                            <a href="edit_employee.php?id=<?= $emp['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_employee.php?id=<?= $emp['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this employee?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No employees found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
