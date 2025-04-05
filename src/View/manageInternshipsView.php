<?php

// Location: /home/demy/project-dev-web/src/View/manageInternshipsView.php
// Included by internshipController.php (action=list for Admin/Pilote)
// Assumes variables:
// $internships (array of offers), $companiesList (array for dropdown)
// $loggedInUserRole ('admin' or 'pilote'), $loggedInUserId (int)
// $pageTitle, $errorMessage, $successMessage

// Prevent direct access / access by students
if (!isset($loggedInUserRole) || !in_array($loggedInUserRole, ['admin', 'pilote']) || !isset($loggedInUserId)) {
    die("Access Denied or required data missing.");
}

$defaultCompanyPic = 'images/default_company.png'; // ** Relative path from THIS file's location in View folder **
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
            box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4);
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
        }

        /* Internship card styles */
        .internship-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.25rem;
        }
        
        .internship-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .internship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .internship-card-header {
            padding: 1.25rem;
            background: var(--input-bg);
            border-bottom: 1px solid var(--card-border);
        }
        
        .internship-card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
            color: var(--text-primary);
        }
        
        .internship-card-company {
            display: flex;
            align-items: center;
            margin-top: 0.75rem;
        }
        
        .company-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-weight: 500;
            color: var(--text-primary);
            margin: 0;
            font-size: 0.95rem;
        }
        
        .company-location {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin: 0;
        }
        
        .internship-card-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .internship-description {
            color: var(--text-primary);
            margin-bottom: 1.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            line-height: 1.5;
        }
        
        .internship-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .internship-detail:last-child {
            margin-bottom: 0;
        }
        
        .internship-detail i {
            width: 18px;
            margin-right: 0.75rem;
            color: var(--text-secondary);
            text-align: center;
        }
        
        .internship-detail-value {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .internship-detail-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-right: 0.5rem;
        }
        
        .internship-card-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .internship-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .internship-id i {
            margin-right: 0.5rem;
        }
        
        .internship-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .no-permission {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .no-permission i {
            margin-right: 0.5rem;
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
            
            .internship-list {
                grid-template-columns: 1fr;
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
                    <p>Internship Management</p>
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
                    <a href="../Controller/userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link active">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
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
                    <span><i class="fas fa-file-alt me-2"></i>Internship Management</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                        <li>Internship Management</li>
                    </ul>
                </div>
            </div>
            
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Add Internship Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-plus-circle"></i> Add New Internship Offer</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="internshipController.php">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_id_company"><i class="fa-regular fa-building"></i> Company:</label>
                                    <select class="form-control" id="add_id_company" name="id_company" required>
                                        <option value="" disabled selected>-- Select Company --</option>
                                        <?php if (is_array($companiesList) && !empty($companiesList)): ?>
                                            <?php foreach ($companiesList as $company): ?>
                                                <option value="<?= htmlspecialchars($company['id_company']) ?>">
                                                    <?= htmlspecialchars($company['name_company']) ?> (ID: <?= htmlspecialchars($company['id_company']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No companies available</option>
                                            <?php if($loggedInUserRole === 'pilote'): ?>
                                                <option value="" disabled>(You must create companies first)</option>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if ($loggedInUserRole === 'pilote' && empty($companiesList)): ?>
                                        <small class="text-muted">You can only select companies you have created. <a href="companyController.php">Manage Companies</a></small>
                                    <?php elseif(empty($companiesList) && $loggedInUserRole === 'admin'): ?>
                                        <small class="text-muted">No companies found. <a href="companyController.php">Add Companies</a></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_title"><i class="fa-solid fa-heading"></i> Offer Title:</label>
                                    <input type="text" class="form-control" id="add_title" name="title" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="4" required></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_remuneration"><i class="fa-solid fa-euro-sign"></i> Remuneration (€/month, optional):</label>
                                    <input type="number" class="form-control" step="0.01" min="0" id="add_remuneration" name="remuneration" placeholder="e.g., 550.50">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_offre_date"><i class="fa-regular fa-calendar-check"></i> Offer Available Date:</label>
                                    <input type="date" class="form-control" id="add_offre_date" name="offre_date" required value="<?= date('Y-m-d'); // Default to today ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Internship Offer</button>
                    </form>
                </div>
            </div>

            <!-- Internship List Card -->
            <div class="card fade-in delay-1">
                <div class="card-header">
                    <h2><i class="fa-solid fa-list-ul"></i> Current Internship Offers</h2>
                </div>
                <div class="card-body">
                    <?php if (is_array($internships) && !empty($internships)): ?>
                        <div class="internship-list">
                            <?php foreach ($internships as $offer): ?>
                                <?php
                                    // Determine if current user can modify this offer
                                    $canModify = false;
                                    if ($loggedInUserRole === 'admin') {
                                        $canModify = true;
                                    } elseif ($loggedInUserRole === 'pilote') {
                                        // Allow if pilote created the COMPANY or the INTERNSHIP
                                        if ((isset($offer['company_creator_id']) && $offer['company_creator_id'] == $loggedInUserId) ||
                                            (isset($offer['created_by_pilote_id']) && $offer['created_by_pilote_id'] == $loggedInUserId)) {
                                            $canModify = true;
                                        }
                                    }
                                    // Format remuneration for display
                                    $formattedRemuneration = $offer['remuneration'] !== null ? number_format((float)$offer['remuneration'], 2, ',', ' ') . ' €' : 'Not specified';
                                ?>
                                <div class="internship-card fade-in">
                                    <div class="internship-card-header">
                                        <h3><?= htmlspecialchars($offer['title']) ?></h3>
                                        <div class="internship-card-company">
                                            <div class="company-logo">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div class="company-info">
                                                <p class="company-name"><?= htmlspecialchars($offer['name_company'] ?? 'N/A') ?></p>
                                                <p class="company-location"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($offer['company_location'] ?? 'Location not specified') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="internship-card-body">
                                        <div class="internship-description">
                                            <?= nl2br(htmlspecialchars($offer['description'])) ?>
                                        </div>
                                        
                                        <div class="internship-detail">
                                            <i class="fas fa-euro-sign"></i>
                                            <span class="internship-detail-label">Remuneration:</span>
                                            <span class="internship-detail-value"><?= $formattedRemuneration ?></span>
                                        </div>
                                        
                                        <div class="internship-detail">
                                            <i class="fas fa-calendar"></i>
                                            <span class="internship-detail-label">Available since:</span>
                                            <span class="internship-detail-value"><?= htmlspecialchars($offer['offre_date']) ?></span>
                                        </div>
                                        
                                        <?php if ($loggedInUserRole === 'admin'): ?>
                                            <div class="internship-detail">
                                                <i class="fas fa-user"></i>
                                                <span class="internship-detail-label">Offer Creator:</span>
                                                <span class="internship-detail-value">
                                                    <?php
                                                        $offerCreatorText = 'Admin';
                                                        if (isset($offer['created_by_pilote_id']) && $offer['created_by_pilote_id']) {
                                                            $offerCreatorText = 'Pilote: ' . htmlspecialchars($offer['created_by_pilote_id']);
                                                        }
                                                        echo $offerCreatorText;
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="internship-detail">
                                                <i class="fas fa-user-tie"></i>
                                                <span class="internship-detail-label">Company Manager:</span>
                                                <span class="internship-detail-value">
                                                    <?php
                                                        $companyManagerText = 'Admin/Old';
                                                        if (isset($offer['company_creator_id']) && $offer['company_creator_id'] !== null) {
                                                            $companyManagerText = 'Pilote: ' . htmlspecialchars($offer['company_creator_id']);
                                                        } elseif (isset($offer['company_creator_id']) && $offer['company_creator_id'] === null) {
                                                            $companyManagerText = 'Admin';
                                                        }
                                                        echo $companyManagerText;
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="internship-card-footer">
                                        <div class="internship-id">
                                            <i class="fas fa-id-card"></i> ID: <?= htmlspecialchars($offer['id_internship']) ?>
                                        </div>
                                        <div class="internship-actions">
                                            <?php if ($canModify): ?>
                                                <a href="internshipController.php?action=edit&id=<?= $offer['id_internship'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <form method="post" action="internshipController.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $offer['id_internship'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this internship offer?');">
                                                        <i class="fa-solid fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="no-permission">
                                                    <i class="fa-solid fa-lock"></i> No permission
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-circle-xmark"></i>
                            <h3>No internship offers found</h3>
                            <p>
                                <?= ($loggedInUserRole === 'pilote') ? 'No offers match your criteria.' : 'No offers have been added to the system yet.' ?>
                                Add your first internship offer using the form above.
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
            
            // Add animation delay to internship cards for staggered appearance
            const internshipCards = document.querySelectorAll('.internship-card');
            internshipCards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 + index * 0.05}s`;
            });
        });
    </script>
</body>
</html>