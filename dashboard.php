<?php   
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require 'config.php';

// Fetch dashboard data
$totalEmployees = $conn->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$birthdaysToday = $conn->query("SELECT COUNT(*) FROM employees WHERE DATE_FORMAT(date_of_birth, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')")->fetchColumn();
$anniversariesToday = $conn->query("SELECT COUNT(*) FROM employees WHERE DATE_FORMAT(date_of_join, '%m-%d') = DATE_FORMAT(CURDATE(), '%m-%d')")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Ilogitron Technologies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-direction:column;
            margin-bottom: 30px;
        }

        .dashboard-header h2 {
            color: red;
        }
/*
        .notification-bell {
            position: relative;
            font-size: 22px;
            color: #2c3e50;
        }

        .notification-bell i {
            cursor: pointer;
        }*/

        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.06);
            text-align: center;
        }

        .card h3 {
            font-size: 20px;
            color: #34495e;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 26px;
            font-weight: bold;
            color: #1abc9c;
        }

        @media screen and (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header">
        <h2>Dashboard</h2>
        <!---
        <div class="notification-bell">
            <i class="fas fa-bell"></i>
        </div>--->
    </div>

    <div class="dashboard-widgets">
        <div class="card">
            <h3>Total Employees</h3>
            <p><?= $totalEmployees ?></p>
        </div>
        <div class="card">
            <h3>Today's Birthdays</h3>
            <p><?= $birthdaysToday ?></p>
        </div>
        <div class="card">
            <h3>Today's Anniversaries</h3>
            <p><?= $anniversariesToday ?></p>
        </div>
    </div>
</div>

</body>
</html>
