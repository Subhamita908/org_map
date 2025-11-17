<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['hr_logged_in'])) {
    header("Location: index.html");
    exit();
}

try {
    require 'config.php';
    // Select only needed columns to optimize query
    $stmt = $pdo->prepare("SELECT id, employee_id, name, email, phone, designation, hierarchy_level, pid, location, profile_picture FROM employees");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching employee data: " . htmlspecialchars($e->getMessage());
    exit();
}

if (empty($employees)) {
    echo "<p>No employees found.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizational Chart | Corporate System</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e1e8ed;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 i {
            color: #3498db;
            font-size: 2.2rem;
        }

        .header-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 25px;
        }

        .search-container {
            position: relative;
            max-width: 400px;
        }

        #searchBox {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            background: #fbfcfd;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        #searchBox:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            background: white;
        }

        .search-icon {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid #e1e8ed;
            min-height: 600px;
        }

        #chart_div {
            overflow-x: auto;
            width: 100%;
            min-height: 500px;
        }

        /* Custom scrollbar */
        #chart_div::-webkit-scrollbar {
            height: 8px;
        }

        #chart_div::-webkit-scrollbar-track {
            background: #f1f3f4;
            border-radius: 4px;
        }

        #chart_div::-webkit-scrollbar-thumb {
            background: #c1c8cd;
            border-radius: 4px;
        }

        #chart_div::-webkit-scrollbar-thumb:hover {
            background: #a8b2b8;
        }

        .google-visualization-orgchart-node {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 12px !important;
        }

        .google-visualization-orgchart-node:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: slideIn 0.3s ease;
            border: 1px solid #e1e8ed;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, white 0%, red 100%);
            padding: 25px 30px;
            color: white;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: 300;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .employee-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            margin: -50px auto 20px;
            display: block;
        }

        .employee-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 5px;
        }

        .employee-title {
            color: #7f8c8d;
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 25px;
        }

        .info-grid {
            display: grid;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
        }

        .info-icon.id { background: #e8f5e8; color: #27ae60; }
        .info-icon.email { background: #e8f4fd; color: #3498db; }
        .info-icon.phone { background: #fff3e0; color: #f39c12; }
        .info-icon.location { background: #fce4ec; color: #e91e63; }
        .info-icon.manager { background: #f3e5f5; color: #9c27b0; }
        .info-icon.level { background: #e0f2f1; color: #00796b; }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .info-value {
            color: #5a6c7d;
            font-size: 1rem;
        }

        .legend {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e8ed;
        }

        .legend h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .chart-container {
                padding: 20px;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }

            .legend-items {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-sitemap"></i>Organizational Structure</h1>
            <p class="header-subtitle">Interactive corporate hierarchy visualization</p>
            
            <div class="search-container">
                <input type="text" id="searchBox" placeholder="Search for employees..." aria-label="Search employee name">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div class="legend">
            <h3><i class="fas fa-palette"></i>Hierarchy Levels</h3>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff6b6b;"></div>
                    <span>Executive Level (L-8)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #4ecdc4;"></div>
                    <span>Senior Management (L-4)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffe66d;"></div>
                    <span>Management (L-2)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f8f9fa;"></div>
                    <span>Staff Level</span>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <div id="chart_div" role="region" aria-label="Organizational Chart"></div>
        </div>
    </div>

    <!-- Modal -->
    <div id="employeeModal" class="modal" role="dialog" aria-labelledby="modalTitle">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" role="button" aria-label="Close modal">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalDetails"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        google.charts.load('current', { packages: ["orgchart"] });
        google.charts.setOnLoadCallback(drawChart);

        const employeeDetails = <?php echo json_encode($employees); ?>;

        // Enhanced color mapping for hierarchy levels
        const hierarchyColors = {
            'L-8': { bg: 'linear-gradient(135deg, #ff6b6b, #ee5a52)', border: '#ff6b6b', shadow: '0 8px 25px rgba(255, 107, 107, 0.3)' },
            'L-7': { bg: 'linear-gradient(135deg, #ff6b6b, #ee5a52)', border: '#ff6b6b', shadow: '0 8px 25px rgba(255, 107, 107, 0.3)' },
            
            'L-4': { bg: 'linear-gradient(135deg, #4ecdc4, #44a08d)', border: '#4ecdc4', shadow: '0 8px 25px rgba(78, 205, 196, 0.3)' },
            'L-3': { bg: 'linear-gradient(135deg, #4ecdc4, #44a08d)', border: '#4ecdc4', shadow: '0 8px 25px rgba(78, 205, 196, 0.3)' },
            
            'L-2': { bg: 'linear-gradient(135deg, #ffe66d, #ffd93d)', border: '#ffe66d', shadow: '0 8px 25px rgba(255, 230, 109, 0.3)' },
            'default': { bg: 'linear-gradient(135deg, #f8f9fa, #e9ecef)', border: '#dee2e6', shadow: '0 8px 25px rgba(0, 0, 0, 0.1)' }
        };

        function drawChart() {
            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            const rows = employeeDetails.map(emp => {
                const profile = emp.profile_picture || 'Uploads/default.png';
                const tooltip = `${emp.designation} • ${emp.location}`;

                const colors = hierarchyColors[emp.hierarchy_level] || hierarchyColors['default'];
                const nodeContent = `
                    <div style="
                        display: flex;
                        align-items: center;
                        background: ${colors.bg};
                        border: 2px solid ${colors.border};
                        padding: 15px;
                        border-radius: 12px;
                        box-shadow: ${colors.shadow};
                        min-width: 250px;
                        transition: all 0.3s ease;
                        backdrop-filter: blur(10px);
                    ">
                        <img src="${profile}" alt="${emp.name}" style="
                            width: 60px;
                            height: 60px;
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                            margin-right: 15px;
                        " />
                        <div style="color: #2c3e50;">
                            <div style="
                                font-weight: 700;
                                font-size: 16px;
                                margin-bottom: 4px;
                                color: #2c3e50;
                            ">${emp.name}</div>
                            <div style="
                                font-size: 13px;
                                color: #5a6c7d;
                                font-weight: 500;
                                margin-bottom: 2px;
                            ">${emp.designation}</div>
                            <div style="
                                font-size: 11px;
                                color: #7f8c8d;
                                display: flex;
                                align-items: center;
                                gap: 4px;
                            ">
                                <i class="fas fa-map-marker-alt" style="font-size: 10px;"></i>
                                ${emp.location}
                            </div>
                        </div>
                    </div>`;

                let parentName = null;
                if (emp.pid) {
                    const mgr = employeeDetails.find(e => e.id == emp.pid);
                    parentName = mgr ? mgr.name : null;
                }

                return [{ v: emp.name, f: nodeContent }, parentName, tooltip];
            });

            data.addRows(rows);

            const options = {
                allowHtml: true,
                nodeClass: 'org-chart-node',
                selectedNodeClass: 'org-chart-selected'
            };

            const chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
            chart.draw(data, options);

            google.visualization.events.addListener(chart, 'select', function () {
                const selection = chart.getSelection();
                if (selection.length > 0) {
                    const empName = data.getValue(selection[0].row, 0);
                    const emp = employeeDetails.find(e => e.name === empName);
                    if (emp) showModal(emp);
                }
            });
        }

        function showModal(emp) {
            const modal = document.getElementById("employeeModal");
            const modalContent = document.getElementById("modalDetails");
            const profile = emp.profile_picture || 'Uploads/default.png';
            const manager = emp.pid ? (employeeDetails.find(e => e.id == emp.pid)?.name || 'Not specified') : 'Top Level';
            
            modalContent.innerHTML = `
                <img src="${profile}" alt="${emp.name}" class="employee-avatar" />
                <div class="employee-name" id="modalTitle">${emp.name}</div>
                <div class="employee-title">${emp.designation || 'Position not specified'}</div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon id"><i class="fas fa-id-badge"></i></div>
                        <div class="info-content">
                            <div class="info-label">Employee ID</div>
                            <div class="info-value">${emp.employee_id || 'Not assigned'}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon email"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <div class="info-label">Email Address</div>
                            <div class="info-value">${emp.email || 'Not provided'}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon phone"><i class="fas fa-phone"></i></div>
                        <div class="info-content">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value">${emp.phone || 'Not provided'}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon location"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="info-content">
                            <div class="info-label">Location</div>
                            <div class="info-value">${emp.location || 'Not specified'}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon manager"><i class="fas fa-user-tie"></i></div>
                        <div class="info-content">
                            <div class="info-label">Reports To</div>
                            <div class="info-value">${manager}</div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon level"><i class="fas fa-layer-group"></i></div>
                        <div class="info-content">
                            <div class="info-label">Hierarchy Level</div>
                            <div class="info-value">${emp.hierarchy_level || 'Not defined'}</div>
                        </div>
                    </div>
                </div>
            `;
            
            modal.style.display = "block";
            setTimeout(() => modalContent.querySelector('.employee-name').focus(), 100);
        }

        // Event listeners
        document.querySelector(".close").onclick = () => {
            document.getElementById("employeeModal").style.display = "none";
        };

        window.onclick = function(event) {
            const modal = document.getElementById("employeeModal");
            if (event.target === modal) modal.style.display = "none";
        };

        // Enhanced search functionality
        let searchTimeout;
        document.getElementById("searchBox").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.toLowerCase().trim();
            
            searchTimeout = setTimeout(() => {
                const nodes = document.querySelectorAll('.google-visualization-orgchart-node');
                let foundMatch = false;
                
                nodes.forEach(node => {
                    node.style.border = "";
                    node.style.boxShadow = "";
                    node.style.transform = "";
                });
                
                if (searchTerm) {
                    nodes.forEach(node => {
                        if (node.innerText.toLowerCase().includes(searchTerm)) {
                            node.style.border = "3px solid #3498db";
                            node.style.boxShadow = "0 0 20px rgba(52, 152, 219, 0.5)";
                            node.style.transform = "scale(1.05)";
                            
                            if (!foundMatch) {
                                node.scrollIntoView({ 
                                    behavior: "smooth", 
                                    block: "center",
                                    inline: "center"
                                });
                                foundMatch = true;
                            }
                        }
                    });
                }
            }, 300);
        });

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById("employeeModal");
                if (modal.style.display === "block") {
                    modal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>