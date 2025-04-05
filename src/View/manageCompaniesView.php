<?php
// Location: /home/demy/project-dev-web/src/View/manageCompaniesView.php
// Included by companyController.php
// Assumes variables: $companies, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted.");
}

// URL for the appropriate dashboard
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Light Theme Colors */
            --bg-primary-light: #f8f9fc;
            --bg-secondary-light: #ffffff;
            --text-primary-light: #1a1e2c;
            --text-secondary-light: #4a5568;
            --card-border-light: #e2e8f0;
            --card-shadow-light: 0 4px 20px rgba(0, 0, 0, 0.05);
            --navbar-bg-light: rgba(255, 255, 255, 0.8);
            --gradient-primary-light: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-light: linear-gradient(135deg, #3b82f6, #2dd4bf);
            --input-bg-light: #f1f5f9;
            --input-border-light: #e2e8f0;
            --button-hover-light: #f1f5f9;
            --bg-gradient-spot1-light: rgba(99, 102, 241, 0.15);
            --bg-gradient-spot2-light: rgba(139, 92, 246, 0.15);
            --bg-dots-light: rgba(99, 102, 241, 0.15);
            --glass-bg-light: rgba(255, 255, 255, 0.7);
            --glass-border-light: rgba(255, 255, 255, 0.5);
            
            /* Dark Theme Colors */
            --bg-primary-dark: #13151e;
            --bg-secondary-dark: #1a1e2c;
            --text-primary-dark: #f1f5f9;
            --text-secondary-dark: #a0aec0;
            --card-border-dark: #2d3748;
            --card-shadow-dark: 0 4px 20px rgba(0, 0, 0, 0.2);
            --navbar-bg-dark: rgba(26, 30, 44, 0.8);
            --gradient-primary-dark: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-dark: linear-gradient(135deg, #3b82f6, #2dd4bf);
            --input-bg-dark: #2d3748;
            --input-border-dark: #4a5568;
            --button-hover-dark: #2d3748;
            --bg-gradient-spot1-dark: rgba(99, 102, 241, 0.2);
            --bg-gradient-spot2-dark: rgba(139, 92, 246, 0.2);
            --bg-dots-dark: rgba(139, 92, 246, 0.15);
            --glass-bg-dark: rgba(26, 30, 44, 0.7);
            --glass-border-dark: rgba(45, 55, 72, 0.5);
            
            /* Active theme (default to light) */
            --bg-primary: var(--bg-primary-light);
            --bg-secondary: var(--bg-secondary-light);
            --text-primary: var(--text-primary-light);
            --text-secondary: var(--text-secondary-light);
            --card-border: var(--card-border-light);
            --card-shadow: var(--card-shadow-light);
            --gradient-primary: var(--gradient-primary-light);
            --gradient-accent: var(--gradient-accent-light);
            --input-bg: var(--input-bg-light);
            --input-border: var(--input-border-light);
            --button-hover: var(--button-hover-light);
            --bg-gradient-spot1: var(--bg-gradient-spot1-light);
            --bg-gradient-spot2: var(--bg-gradient-spot2-light);
            --glass-bg: var(--glass-bg-light);
            --glass-border: var(--glass-border-light);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Main Layout */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Background elements */
        .bg-gradient-spot {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }
        
        .bg-gradient-spot-1 {
            width: 40vw;
            height: 40vw;
            background: var(--bg-gradient-spot1);
            top: -10%;
            left: -10%;
        }
        
        .bg-gradient-spot-2 {
            width: 30vw;
            height: 30vw;
            background: var(--bg-gradient-spot2);
            bottom: -5%;
            right: -5%;
        }
        
        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 40px 40px;
            background-image: 
                radial-gradient(circle, var(--bg-dots-light) 1px, transparent 1px);
            z-index: -1;
            opacity: 0.4;
        }

        /* Sidebar styles */
        .sidebar {
            width: 280px;
            min-height: 100vh;
            padding: 2rem 1.5rem;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid var(--glass-border);
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            padding: 0 0.5rem;
        }
        
        .sidebar-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--card-border);
            margin-right: 1rem;
        }
        
        .sidebar-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        
        .sidebar-header p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link i {
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: var(--gradient-primary);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            transform: translateY(-3px);
        }
        
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 0.5rem;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 1px solid var(--card-border);
        }
        
        .logout-btn i {
            margin-right: 1rem;
        }
        
        .logout-btn:hover {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* Main content */
        .main-container {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--text-secondary) transparent;
        }
        
        .main-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .main-container::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .main-container::-webkit-scrollbar-thumb {
            background-color: var(--text-secondary);
            border-radius: 10px;
        }

        /* Header styles */
        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }
        
        .page-header .breadcrumb {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .page-header .breadcrumb li {
            display: flex;
            align-items: center;
        }
        
        .page-header .breadcrumb li:not(:last-child)::after {
            content: '/';
            margin: 0 0.5rem;
            color: var(--text-secondary);
        }
        
        .page-header .breadcrumb a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .page-header .breadcrumb a:hover {
            color: var(--text-primary);
        }

        /* Alert/message styling */
        .message {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            animation: fadeUp 0.6s ease forwards;
            border: 1px solid transparent;
        }
        
        .message i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }
        
        .success-message {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.2);
        }

        /* Card styling */
        .card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: var(--input-bg);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .card-header h2 i {
            margin-right: 0.75rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            background: var(--input-bg);
            border-top: 1px solid var(--card-border);
            padding: 1rem 1.5rem;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
        }
        
        .btn-warning:hover, .btn-danger:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 6px;
        }

        /* Company Card List */
        .company-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }
        
        .company-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .company-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .company-card-header {
            padding: 1.25rem;
            background: var(--input-bg);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
        }
        
        .company-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .company-info {
            flex: 1;
            overflow: hidden;
        }
        
        .company-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 0.25rem;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .company-info p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .company-card-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .company-detail {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
        }
        
        .company-detail:last-child {
            margin-bottom: 0;
        }
        
        .company-detail i {
            margin-right: 0.75rem;
            color: var(--text-secondary);
            width: 16px;
            text-align: center;
            margin-top: 0.2rem;
        }
        
        .company-detail-content {
            flex: 1;
            overflow: hidden;
        }
        
        .company-detail-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .company-detail-value {
            font-weight: 500;
            color: var(--text-primary);
            word-break: break-word;
        }
        
        .company-card-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .company-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .company-id i {
            margin-right: 0.5rem;
        }
        
        .company-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .website-link {
            display: inline-flex;
            align-items: center;
            color: #6366f1;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .website-link i {
            margin-right: 0.5rem;
        }
        
        .website-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        /* Theme toggle button */
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            z-index: 100;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        /* Navbar for mobile */
        .navbar {
            display: none;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: var(--navbar-bg-light);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 1.5rem;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
        }
        
        .navbar-brand img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.75rem;
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            color: var(--text-primary);
            background: var(--input-bg);
            border-radius: 8px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        /* Dark mode */
        .dark-mode {
            --bg-primary: var(--bg-primary-dark);
            --bg-secondary: var(--bg-secondary-dark);
            --text-primary: var(--text-primary-dark);
            --text-secondary: var(--text-secondary-dark);
            --card-border: var(--card-border-dark);
            --card-shadow: var(--card-shadow-dark);
            --input-bg: var(--input-bg-dark);
            --input-border: var(--input-border-dark);
            --button-hover: var(--button-hover-dark);
            --bg-gradient-spot1: var(--bg-gradient-spot1-dark);
            --bg-gradient-spot2: var(--bg-gradient-spot2-dark);
            --glass-bg: var(--glass-bg-dark);
            --glass-border: var(--glass-border-dark);
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fade-in {
            opacity: 0;
            animation: fadeUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        
        /* Media queries */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.5rem;
                transform: translateX(-100%);
                z-index: 1050;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-header h3, .sidebar-header p, .nav-link span {
                display: none;
            }
            
            .sidebar-header {
                justify-content: center;
                margin-bottom: 2rem;
            }
            
            .sidebar-header img {
                margin-right: 0;
            }
            
            .nav-link {
                justify-content: center;
                padding: 1rem;
            }
            
            .nav-link i {
                margin-right: 0;
                font-size: 1.5rem;
            }
            
            .logout-btn {
                justify-content: center;
                padding: 1rem;
            }
            
            .logout-btn i {
                margin-right: 0;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .main-container {
                margin-left: 0;
                width: 100%;
            }
            
            .navbar {
                display: flex;
            }
            
            .company-list {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .company-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>
    
    <!-- Theme toggle button -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../View/images/default_avatar.png" alt="Profile Picture">
                <div>
                    <h3>Admin Panel</h3>
                    <p>Company Management</p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= $dashboardUrl ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="companyController.php" class="nav-link active">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <?php if ($loggedInUserRole === 'admin'): ?>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/settingsController.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="../Controller/logoutController.php" class="logout-btn">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="main-container">
            <!-- Top navbar for mobile -->
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none">
                <a class="navbar-brand" href="#">
                    <img src="../View/images/default_avatar.png" alt="Profile">
                    <span><i class="fas fa-building me-2"></i>Company Management</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fas fa-building me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                        <li>Company Management</li>
                    </ul>
                </div>
            </div>
            
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Add Company Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-building-circle-arrow-right"></i> Add New Company</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_name"><i class="fa-regular fa-building"></i> Company Name:</label>
                                    <input type="text" class="form-control" id="add_name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                                    <input type="text" class="form-control" id="add_location" name="location" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_email"><i class="fa-regular fa-envelope"></i> Email:</label>
                                    <input type="email" class="form-control" id="add_email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                                    <input type="text" class="form-control" id="add_phone" name="phone" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_url"><i class="fa-solid fa-link"></i> Website URL (Optional):</label>
                                    <input type="url" class="form-control" id="add_url" name="url" placeholder="https://www.example.com">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Company</button>
                    </form>
                </div>
            </div>

            <!-- Company List Card -->
            <div class="card fade-in delay-1">
                <div class="card-header">
                    <h2><i class="fa-solid fa-list-ul"></i> Company List</h2>
                </div>
                <div class="card-body">
                    <?php if (is_array($companies) && !empty($companies)): ?>
                        <div class="company-list">
                            <?php foreach ($companies as $company):
                                // Determine if current user can modify this company
                                $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($company['created_by_pilote_id']) && $company['created_by_pilote_id'] == $loggedInUserId));
                                // Get first letter of company name for logo
                                $logoLetter = strtoupper(substr($company['name_company'], 0, 1));
                            ?>
                                <div class="company-card fade-in">
                                    <div class="company-card-header">
                                        <div class="company-logo">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="company-info">
                                            <h3 title="<?= htmlspecialchars($company['name_company']) ?>"><?= htmlspecialchars($company['name_company']) ?></h3>
                                            <p><?= htmlspecialchars($company['location']) ?></p>
                                        </div>
                                    </div>
                                    <div class="company-card-body">
                                        <div class="company-detail">
                                            <i class="fas fa-envelope"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Email</div>
                                                <div class="company-detail-value"><?= htmlspecialchars($company['email']) ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="company-detail">
                                            <i class="fas fa-phone"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Phone</div>
                                                <div class="company-detail-value"><?= htmlspecialchars($company['phone_number']) ?></div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($company['company_url'])): ?>
                                        <div class="company-detail">
                                            <i class="fas fa-globe"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Website</div>
                                                <div class="company-detail-value">
                                                    <a href="<?= htmlspecialchars($company['company_url']) ?>" target="_blank" rel="noopener noreferrer" class="website-link">
                                                        <i class="fas fa-external-link-alt"></i>Visit Website
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($loggedInUserRole === 'admin'): ?>
                                        <div class="company-detail">
                                            <i class="fas fa-user-plus"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Created By</div>
                                                <div class="company-detail-value"><?= htmlspecialchars($company['created_by_pilote_id'] ?? 'Admin/Old') ?></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="company-card-footer">
                                        <div class="company-id">
                                            <i class="fas fa-id-card"></i> ID: <?= htmlspecialchars($company['id_company']) ?>
                                        </div>
                                        <div class="company-actions">
                                            <?php if ($canModify): ?>
                                                <a href="editCompany.php?id=<?= $company['id_company'] ?>" class="btn btn-warning btn-sm" title="Edit Company">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete company <?= htmlspecialchars(addslashes($company['name_company'])) ?>? Check related internships.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $company['id_company'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Company">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="company-id">
                                                    <i class="fas fa-eye"></i> View Only
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-building-circle-xmark"></i>
                            <h3>No companies found</h3>
                            <p>
                                <?= ($loggedInUserRole === 'pilote') ? 'You haven\'t created any companies yet.' : 'No companies have been added to the system yet.' ?>
                                Add your first company using the form above.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');
            
            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }
            
            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                
                if (body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('theme', 'dark');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('theme', 'light');
                }
            });
            
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target) && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                });
            }
            
            // Add animation delay to company cards for staggered appearance
            const companyCards = document.querySelectorAll('.company-card');
            companyCards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 + index * 0.05}s`;
            });
        });
    </script>
</body>
</html>