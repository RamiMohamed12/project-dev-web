<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Préchargement des classes pour éviter les erreurs
require_once __DIR__ . '/../Model/user.php';
require_once __DIR__ . '/../Model/company.php';

// Initialisation des objets
$user = new User($conn);
$company = new Company($conn);

// Récupération des données
try {
    $students = $user->getAllStudents();
    $pilotes = $user->getAllPilotes();
    $admins = $user->getAllAdmins();
    $totalUsers = count($students) + count($pilotes) + count($admins);
    
    $companies = $company->readAll();
    $totalCompanies = count($companies);
} catch (Exception $e) {
    // Gestion des erreurs
    $error = $e->getMessage();
    $totalUsers = 0;
    $totalCompanies = 0;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
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
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="userController.php">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="companyController.php">
                        <i class="fas fa-building"></i>
                        <span>Companies</span>
                    </a>
                </li>
                <li>
                    <a href="internshipController.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Internships</span>
                    </a>
                </li>
                <li>
                    <a href="applicationController.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Applications</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?>!</h1>
                <div class="user-info">
                    <button id="theme-toggle" class="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
            <div class="error-message" style="color: var(--danger-color); background: rgba(231, 76, 60, 0.1); padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                <p><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="cards-container">
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-info">
                        <h3>Users</h3>
                        <p><?php echo $totalUsers; ?> total users</p>
                    </div>
                    <a href="userController.php" class="card-link">Manage Users</a>
                </div>
                
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="card-info">
                        <h3>Companies</h3>
                        <p><?php echo $totalCompanies; ?> companies</p>
                    </div>
                    <a href="companyController.php" class="card-link">Manage Companies</a>
                </div>
                
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="card-info">
                        <h3>Internships</h3>
                        <p>Manage internship offers</p>
                    </div>
                    <a href="internshipController.php" class="card-link">Manage Internships</a>
                </div>
                
                <div class="card">
                    <div class="card-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card-info">
                        <h3>Applications</h3>
                        <p>Review student applications</p>
                    </div>
                    <a href="applicationController.php" class="card-link">View Applications</a>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-details">
                            <p>New user registered</p>
                            <span class="time">2 hours ago</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="activity-details">
                            <p>New company added</p>
                            <span class="time">Yesterday</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="activity-details">
                            <p>New internship offer posted</p>
                            <span class="time">3 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;
        const themeIcon = themeToggle.querySelector('i');
        
        // Vérifier si un thème est déjà enregistré dans localStorage
        const savedTheme = localStorage.getItem('dashboard-theme');
        if (savedTheme) {
            htmlElement.setAttribute('data-theme', savedTheme);
            if (savedTheme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        }
        
        themeToggle.addEventListener('click', () => {
            if (htmlElement.getAttribute('data-theme') === 'light') {
                htmlElement.setAttribute('data-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                localStorage.setItem('dashboard-theme', 'dark');
            } else {
                htmlElement.setAttribute('data-theme', 'light');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                localStorage.setItem('dashboard-theme', 'light');
            }
        });
    </script>
</body>
</html>