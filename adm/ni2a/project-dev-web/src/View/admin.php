<?php
require_once __DIR__ . '/../Auth/AuthCheck.php';
AuthCheck::checkUserAuth('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-cube fa-2x"></i>
                <h2>Admin Panel</h2>
            </div>
            <ul class="nav-links">
                <li class="active">
                    <a href="admin.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="../Controller/userController.php">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="../Controller/companyController.php">
                        <i class="fas fa-building"></i>
                        <span>Companies</span>
                    </a>
                </li>
                <li>
                    <a href="../Controller/internshipController.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Internships</span>
                    </a>
                </li>
                <li>
                    <a href="../Controller/applicationController.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Applications</span>
                    </a>
                </li>
                <li>
                    <a href="../Controller/logoutController.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>
            
            <div class="dashboard-summary">
                <h2>Welcome to the Admin Dashboard</h2>
                <p>From here you can manage all aspects of the application.</p>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Users</h3>
                        <p>Manage all users</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-building"></i>
                        <h3>Companies</h3>
                        <p>Manage companies</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-briefcase"></i>
                        <h3>Internships</h3>
                        <p>Manage internships</p>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-file-alt"></i>
                        <h3>Applications</h3>
                        <p>Manage applications</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>