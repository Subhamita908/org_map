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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        #searchBox {
            display: block;
            margin: 0 auto 15px auto;
            padding: 10px;
            width: 300px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .action-buttons {
            text-align: center;
            margin-bottom: 15px;
        }

        .action-buttons button {
            padding: 8px 12px;
            margin: 0 5px;
            font-size: 14px;
            cursor: pointer;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .action-buttons button:hover {
            background-color: #0056b3;
        }

        #chart_div {
            overflow-x: auto;
            padding: 20px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 10px;
        }
        
        .google-visualization-orgchart-node{
            background-color: red;
        }

        .google-visualization-orgchart-node button {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 6px;
            padding: 2px 6px;
            cursor: pointer;
        }

        .google-visualization-orgchart-node button:hover {
            background-color: #ddd;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 8% auto;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.25);
        }

        .close {
            float: right;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: red;
        }

        .modal img {
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
            border-radius: 50%;
        }

        .modal-content h3 {
            margin: 10px 0;
        }

        .modal-content p {
            font-size: 15px;
            margin: 5px 0;
        }
    </style>
</head>
<body>

    <h2>Organizational Chart</h2>
    
    <input type="text" id="searchBox" placeholder="Search employee name...">

    <div class="action-buttons">
        <button onclick="expandAll()">Expand All</button>
        <button onclick="collapseAll()">Collapse All</button>
    </div>

    <div id="chart_div"></div>

    <!-- Modal -->
    <div id="employeeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalDetails"></div>
        </div>
    </div>

<script type="text/javascript">
google.charts.load('current', {packages:["orgchart"]});
google.charts.setOnLoadCallback(initializeChart);

const employees = <?php echo json_encode($employees); ?>;

const treeMap = new Map();
employees.forEach(emp => {
    const parent = emp.pid || null;
    if (!treeMap.has(parent)) treeMap.set(parent, []);
    treeMap.get(parent).push(emp);
});

const expandedNodes = new Set();
const employeeMap = new Map();
employees.forEach(emp => employeeMap.set(emp.id, emp));

function initializeChart() {
    // Expand root (pid null)
    treeMap.get(null)?.forEach(emp => expandedNodes.add(emp.id));
    drawChart();
}

function drawChart() {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'Name');
    data.addColumn('string', 'Manager');
    data.addColumn('string', 'ToolTip');

    const rows = [];
    const idToNameMap = {};
    employees.forEach(emp => idToNameMap[emp.id] = emp.name);

    function buildRows(currentId) {
        const children = treeMap.get(currentId);
        if (!children) return;

        for (const emp of children) {
            const isExpanded = expandedNodes.has(emp.id);
            const hasChildren = !!treeMap.get(emp.id);

            const profile = emp.profile_picture ? emp.profile_picture : 'default.png';
            const toggleBtn = hasChildren ? `<button onclick="toggleNode(${emp.id}, event)">${isExpanded ? '−' : '+'}</button>` : '';

            const nodeContent = `
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src='uploads/${profile}' width='50' height='50'/>
                    <div>
                        <strong>${emp.name}</strong><br>
                        <small>${emp.designation}</small>
                    </div>
                    ${toggleBtn}
                </div>
            `;

            const managerName = currentId ? idToNameMap[currentId] : null;
            const tooltip = `${emp.designation} (${emp.location})`;

            rows.push([{v: emp.name, f: nodeContent}, managerName, tooltip]);

            if (isExpanded) {
                buildRows(emp.id);
            }
        }
    }

    buildRows(null);
    data.addRows(rows);

    const chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
    chart.draw(data, {allowHtml: true});

    google.visualization.events.addListener(chart, 'select', function () {
        const selection = chart.getSelection();
        if (selection.length > 0) {
            const empName = data.getValue(selection[0].row, 0);
            const emp = employees.find(e => e.name === empName);
            if (emp) showModal(emp);
        }
    });
}

function toggleNode(empId, event) {
    event.stopPropagation();
    if (expandedNodes.has(empId)) expandedNodes.delete(empId);
    else expandedNodes.add(empId);
    drawChart();
}

function expandAll() {
    employees.forEach(emp => {
        if (treeMap.has(emp.id)) expandedNodes.add(emp.id);
    });
    drawChart();
}

function collapseAll() {
    expandedNodes.clear();
    treeMap.get(null)?.forEach(emp => expandedNodes.add(emp.id)); // Keep root visible
    drawChart();
}

// Search and highlight
document.getElementById("searchBox").addEventListener("keyup", function() {
    const term = this.value.toLowerCase();
    const nodes = document.querySelectorAll('.google-visualization-orgchart-node');

    nodes.forEach(node => {
        const text = node.innerText.toLowerCase();
        node.style.border = text.includes(term) ? "2px solid #007BFF" : "none";
        if (text.includes(term)) node.scrollIntoView({behavior: "smooth", block: "center"});
    });
});

// Modal Handling
function showModal(emp) {
    const modal = document.getElementById("employeeModal");
    const content = document.getElementById("modalDetails");

    content.innerHTML = `
        <div style="text-align:center;">
            <img src="uploads/${emp.profile_picture || 'default.png'}" />
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

document.querySelector(".close").onclick = function() {
    document.getElementById("employeeModal").style.display = "none";
};

window.onclick = function(event) {
    const modal = document.getElementById("employeeModal");
    if (event.target === modal) modal.style.display = "none";
};
</script>
</body>
</html>
