<?php 
session_start();
require 'config.php'; // Database connection ($pdo)

if (!isset($_SESSION['hr_logged_in']) || $_SESSION['hr_logged_in'] !== true) {
    header("Location: index.html");
    exit();
}

$hrUsername = $_SESSION['hr_username'] ?? 'HR User';
$totalEmployees = 0;
$growthData = [];

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Total employees
        $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
        $totalEmployees = $stmt->fetchColumn();

        // Employee growth per month (sample: based on join date)
        $stmt2 = $pdo->query("
            SELECT DATE_FORMAT(date_of_join, '%b %Y') AS month, COUNT(*) AS count 
            FROM employees 
            GROUP BY month 
            ORDER BY STR_TO_DATE(month, '%b %Y') ASC
        ");
        $growthData = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $totalEmployees = 0;
    $growthData = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Ilogitron Technologies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f3f4f6; display:flex; min-height:100vh; }
        .main-content { margin-left:250px; padding:2rem; width:calc(100% - 250px); }
        .card { background:#fff; border-radius:12px; padding:2rem; position:relative; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.05); transition:0.3s; }
        .card:hover { transform:translateY(-5px); box-shadow:0 8px 20px rgba(0,0,0,0.1); }
        .card::before { content:""; position:absolute; top:0; left:0; width:100%; height:6px; border-radius:12px 12px 0 0; background:linear-gradient(90deg,#dc2626,#b91c1c); }
        @media screen and (max-width:768px){ .main-content{margin-left:0;width:100%;padding:1rem;} }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-red-600">Dashboard</h2>
            <div class="text-gray-700 text-lg">
                Welcome, <?php echo htmlspecialchars($hrUsername); ?> 👋
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Employees -->
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Employees</h3>
                <p class="text-3xl font-bold text-red-600"><?php echo htmlspecialchars($totalEmployees); ?></p>
            </div>

            <!-- Upcoming Birthdays -->
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Upcoming Birthdays</h3>
                <p class="text-3xl font-bold text-red-600">3 This Week</p>
            </div>

            <!-- Work Anniversaries -->
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Work Anniversaries</h3>
                <p class="text-3xl font-bold text-red-600">2 This Month</p>
            </div>
        </div>

        <!-- Employee Growth Chart -->
        <div class="card mb-8">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Employee Growth</h3>
            <canvas id="growthChart" class="w-full h-64"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('growthChart').getContext('2d');
            const labels = <?php echo json_encode(array_column($growthData, 'month')); ?>;
            const data = <?php echo json_encode(array_column($growthData, 'count')); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Employees Joined',
                        data: data,
                        fill: true,
                        backgroundColor: 'rgba(220,38,38,0.1)',
                        borderColor: '#dc2626',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: '#b91c1c'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true, position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        </script>
    </div>
</body>
</html>
