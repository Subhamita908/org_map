<?php
session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}
require 'config.php';

$stmt = $pdo->prepare("SELECT * FROM employees");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Organizational Chart - Topic/Subtopic View</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        #chart_div {
            overflow-x: auto;
            overflow-y: auto;
            width: 100%;
            height: 700px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        #searchBox {
            margin-bottom: 20px;
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        img {
            border-radius: 50%;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
        }
        .close {
            float: right;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
        }
        .modal img {
            width: 80px;
            height: 80px;
        }
    </style>
</head>
<body>
    <h2>Organizational Chart (Topic → Subtopic View)</h2>
    <input type="text" id="searchBox" placeholder="Search employee name...">
    <div id="chart_div"></div>

    <!-- Modal -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalDetails"></div>
        </div>
    </div>

    <script type="text/javascript">
        google.charts.load('current', {packages:["orgchart"]}); // orgchart used (supports collapse)
        google.charts.setOnLoadCallback(drawChart);

        let chart, data;
        const employeeDetails = <?php echo json_encode($employees); ?>;

        function drawChart() {
            data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            data.addRows([
                <?php
                foreach ($employees as $emp):
                    $empName = $emp['name'];
                    $parentId = $emp['pid'];
                    $designation = $emp['designation'];
                    $location = $emp['location'];
                    $level = $emp['hierarchy_level'];
                    $profile = $emp['profile_picture'] ? $emp['profile_picture'] : 'default.png';

                    $tooltip = "$designation ($location)";

                    // Assign styles for hierarchy (topic → subtopic)
                    $style = "";
                    if ($level == 1) $style = "font-size:16px; font-weight:bold; color:#1a237e;";
                    elseif ($level == 2) $style = "font-size:14px; font-weight:600; color:#1565c0; margin-left:15px;";
                    else $style = "font-size:13px; color:#444; margin-left:30px;";

                    $nodeContent = "
                        <div style='display:flex; align-items:center; $style padding:5px;'>
                            <img src='{$profile}' alt='{$empName}' style='width:40px;height:40px;margin-right:8px;'/>
                            <div><strong>{$empName}</strong><br><small>{$designation}</small></div>
                        </div>
                    ";

                    // Resolve manager name
                    $parentName = '';
                    foreach ($employees as $mgr) {
                        if ($mgr['id'] == $parentId) {
                            $parentName = $mgr['name'];
                            break;
                        }
                    }

                    echo "[{v:'$empName', f:`$nodeContent`}, " . ($parentName ? "'$parentName'" : "null") . ", '$tooltip'],\n";
                endforeach;
                ?>
            ]);

            chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
            chart.draw(data, {
                allowHtml: true,
                allowCollapse: true,
                size: 'large'
            });

            // Handle click event → show modal
            google.visualization.events.addListener(chart, 'select', function () {
                const selection = chart.getSelection();
                if (selection.length > 0) {
                    const empName = data.getValue(selection[0].row, 0);
                    const emp = employeeDetails.find(e => e.name === empName);
                    if (emp) showModal(emp);
                }
            });
        }

        // Modal
        function showModal(emp) {
            const modal = document.getElementById("employeeModal");
            const modalContent = document.getElementById("modalDetails");
            modalContent.innerHTML = `
                <div style="text-align:center;">
                    <img src="${emp.profile_picture || 'default.png'}" alt="${emp.name}" />
                    <h3>${emp.name}</h3>
                    <p><strong>Employee ID:</strong> ${emp.employee_id}</p>
                    <p><strong>Designation:</strong> ${emp.designation}</p>
                    <p><strong>Email:</strong> ${emp.email}</p>
                    <p><strong>Phone:</strong> ${emp.phone}</p>
                    <p><strong>Location:</strong> ${emp.location}</p>
                    <p><strong>Manager:</strong> ${emp.manager_name}</p>
                    <p><strong>Hierarchy Level:</strong> ${emp.hierarchy_level}</p>
                </div>
            `;
            modal.style.display = "block";
        }

        document.querySelector(".close").onclick = () => {
            document.getElementById("employeeModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("employeeModal");
            if (event.target === modal) modal.style.display = "none";
        }

        // Search
        document.getElementById("searchBox").addEventListener("keyup", function() {
            const searchTerm = this.value.toLowerCase();
            const nodes = document.querySelectorAll('.google-visualization-orgchart-node');
            nodes.forEach(function(node) {
                let text = node.innerText.toLowerCase();
                if (text.includes(searchTerm) && searchTerm.length > 0) {
                    node.style.border = "2px solid #007BFF";
                    node.scrollIntoView({behavior: "smooth", block: "center"});
                } else {
                    node.style.border = "none";
                }
            });
        });
    </script>
</body>
</html>
