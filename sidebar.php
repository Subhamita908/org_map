<?php
// session_start(); // Uncomment if needed globally
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sidebar</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f9fafb;
    transition: margin-left 0.4s ease;
  }

  /* Hamburger */
  .hamburger {
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1001;
    background: #dc2626;
    color: white;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
  }

  .hamburger:hover {
    background: #b91c1c;
    transform: scale(1.1);
  }

  /* Sidebar */
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 260px;
    height: 100vh;
    background: #1e293b;
    color: white;
    transition: transform 0.35s ease-in-out;
    z-index: 1000;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  .sidebar.collapsed {
    transform: translateX(-260px);
  }

  /* Sidebar header with full image */
  .sidebar-header {
    width: 100%;
    height: 100px; /* adjust height as needed */
    overflow: hidden;
  }

  .sidebar-header img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
  }

  /* Nav links */
  nav a {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    font-size: 15px;
    transition: all 0.25s ease;
    border-left: 3px solid transparent;
    margin: 6px 10px;
    border-radius: 6px;
  }

  nav a i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
  }

  nav a:hover,
  nav a.active {
    color: #fff;
    background: rgba(220, 38, 38, 0.15);
    border-left-color: #dc2626;
    transform: translateX(6px);
    box-shadow: 0 2px 10px rgba(220, 38, 38, 0.15);
  }

  /* User Section */
  .user-section {
    margin-top: auto;
    background: #0f172a;
    padding: 18px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  .user-info {
    display: flex;
    align-items: center;
    margin-bottom: 14px;
  }

  .avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 16px;
  }

  .user-info h4 {
    font-size: 14px;
    margin-bottom: 2px;
  }
  .user-info p {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.6);
  }

  .logout-btn {
    width: 100%;
    padding: 10px;
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 3px 8px rgba(220,38,38,0.3);
  }
  .logout-btn:hover {
    background: #b91c1c;
    transform: scale(1.03);
  }

  /* Overlay for mobile */
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
  }

  .overlay.active {
    opacity: 1;
    visibility: visible;
  }

  /* Responsive */
  @media screen and (max-width: 1024px) {
    .sidebar {
      width: 80%;
      max-width: 280px;
      transform: translateX(-100%);
    }
    .sidebar.open {
      transform: translateX(0);
    }
  }
</style>
</head>
<body>

<div class="overlay" onclick="closeSidebar()"></div>

<div class="hamburger" onclick="toggleSidebar()">
  <i class="fas fa-bars"></i>
</div>

<div class="sidebar" id="sidebar">
  <!-- Full image header -->
  <div class="sidebar-header">
    <img src="ilogitron.png" alt="Company Logo">
  </div>

  <!-- Navigation -->
  <nav>
    <a href="view_profile.php"><i class="fas fa-user"></i> View Profile</a>
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="add_employee.php"><i class="fas fa-user-plus"></i> Add Employee</a>
    <a href="view_employees.php" class="active"><i class="fas fa-users"></i> View Employees</a>
    <a href="map.php"><i class="fas fa-sitemap"></i> Organization Chart</a>
  
  </nav>

  <!-- User Section -->
  <div class="user-section">
    <div class="user-info">
      <div class="avatar"><i class="fas fa-user"></i></div>
      <div>
        <h4>HR Admin</h4>
        <p>admin@ilogitron.com</p>
      </div>
    </div>
    <button class="logout-btn" onclick="confirmLogout()">
      <i class="fas fa-sign-out-alt" style="margin-right:6px;"></i> Quick Logout
    </button>
  </div>
</div>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.querySelector('.overlay');

  if(window.innerWidth <= 1024) {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
  } else {
    sidebar.classList.toggle('collapsed');
  }
}

function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open','collapsed');
  document.querySelector('.overlay').classList.remove('active');
}

function confirmLogout() {
  if(confirm('Are you sure you want to logout?')) {
    window.location.href = 'logout.php';
  }
}
</script>

</body>
</html>
