<?php
// session_start(); // Uncomment if needed globally
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sidebar | iLogitron</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>

<!-- Hamburger Icon -->
<div class="hamburger" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <h2>iLogitron Technologies Pvt.Ltd</h2>
    <nav>
        <a href="dashboard.php"><i class="fas fa-chart-line"></i><span>Dashboard</span></a>
        <a href="add_employee.php"><i class="fas fa-user-plus"></i><span>Add Employee</span></a>
        <a href="view_employees.php"><i class="fas fa-users"></i><span>View Employees</span></a>
        <a href="index.php"><i class="fas fa-sitemap"></i><span>Organization Chart</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
    <div class="toggle-mode">
        <button onclick="toggleDarkMode()">Dark Mode</button>
    </div>
</div>

<!-- Main Content -->
<div class="main">
    <!-- Your page content goes here -->
</div>

<!-- Scripts -->
<script>
    // Dark mode toggle
    function toggleDarkMode() {
        document.body.classList.toggle("dark-mode");
    }

    // Active link highlight
    const links = document.querySelectorAll('.sidebar nav a');
    const currentPage = location.pathname.split("/").pop();
    links.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // Sidebar toggle
    function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}

</script>
</body>
</html>
