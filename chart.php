<?php
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require 'config.php';

$stmt = $conn->prepare("SELECT * FROM employees");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Organizational Chart</title>
    <script src="https://balkangraph.com/js/OrgChart.js"></script>
    <style>
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        #orgChartContainer {
            width: 100%;
            height: 100vh;
            overflow: auto; /* Enable both horizontal and vertical scrolling */
            background-color: #f4f6f8;
            padding: 20px;
            box-sizing: border-box;
        }

        #orgChart {
            width: max-content; /* Ensure wide org charts display properly */
            height: max-content;
            margin: auto;
        }
    </style>
</head>
<body>
    <div id="orgChartContainer">
        <div id="orgChart"></div>
    </div>

    <script>
        const chart = new OrgChart(document.getElementById("orgChart"), {
            template: "ana",
            enableSearch: true,
            scaleInitial: OrgChart.match.boundary,
            nodeBinding: {
                field_0: "name",
                field_1: "title",
                img_0: "img"
            },
            nodes: [
                <?php foreach ($employees as $emp): ?>
                {
                    id: <?= $emp['id'] ?>,
                    <?php if ($emp['pid']): ?>pid: <?= $emp['pid'] ?>,<?php endif; ?>
                    name: "<?= htmlspecialchars($emp['name']) ?>",
                    title: "<?= htmlspecialchars($emp['designation'] ?? 'N/A') ?> (<?= htmlspecialchars($emp['hierarchy_level'] ?? 'L-?') ?>)",
                    img: "uploads/<?= htmlspecialchars($emp['profile_picture'] ?? 'DP.png') ?>"
                },
                <?php endforeach; ?>
            ]
        });
    </script>
</body>
</html>
