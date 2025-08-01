<?php    
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require 'config.php';

// Fetch dashboard data
$totalEmployees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
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
            flex-direction: column;
            margin-bottom: 30px;
        }

        .dashboard-header h2 {
            color: red;
        }

        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

        iframe {
            width: 100%;
            height: 400px;
            border: none;
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
    </div>


        <div class="card" style="grid-column: span 2;">
            <h3>Organization Chart</h3>
            <iframe src="index.php"></iframe>
        </div>
    </div>
</div>

</body>
</html>
