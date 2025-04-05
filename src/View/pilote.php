<?php
// Location: /home/demy/project-dev-web/src/View/pilote.php

// --- Required Includes & Session ---
require_once __DIR__ . '/../../config/config.php'; // Need $conn
require_once __DIR__ . '/../Auth/AuthCheck.php';
require_once __DIR__ . '/../Auth/AuthSession.php';
require_once __DIR__ . '/../Model/user.php';      // To fetch user details

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Authentication Check ---
AuthCheck::checkUserAuth('pilote', 'login.php');
// --- End Authentication Check ---

// Get Logged-in User Info from Session initially
$loggedInUserId = AuthSession::getUserData('user_id');
$sessionUserName = AuthSession::getUserData('user_name');
$sessionUserEmail = AuthSession::getUserData('user_email');

// --- Fetch Full User Details from Database ---
$userDetails = null;
$profilePicSrc = null;
$dbUserName = null;
$dbUserEmail = null;
$defaultPic = '../View/images/default_avatar.png'; // ** ADJUST PATH AS NEEDED **

if ($loggedInUserId && isset($conn)) {
    try {
        $userModel = new User($conn);
        $userDetails = $userModel->readPilote($loggedInUserId); // Fetch this pilote's details

        if ($userDetails) {
            $dbUserName = $userDetails['name'];
            $dbUserEmail = $userDetails['email'];
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                $picData = is_resource($userDetails['profile_picture']) ? stream_get_contents($userDetails['profile_picture']) : $userDetails['profile_picture'];
                if ($picData) {
                   $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching pilote details for dashboard (ID: $loggedInUserId): " . $e->getMessage());
    }
}
$displayName = htmlspecialchars($dbUserName ?? $sessionUserName ?? 'Pilote');
$displayEmail = htmlspecialchars($dbUserEmail ?? $sessionUserEmail ?? 'N/A');

// --- Message Handling ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}

// Define current page for sidebar highlighting
$currentPage = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilote Dashboard</title>
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
            --bg-dots: var(--bg-dots-light);
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
                radial-gradient(circle, var(--bg-dots) 1px, transparent 1px);
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
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid var(--glass-border);
            overflow-y: auto;
            overflow-x: hidden;
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
            transition: margin-left 0.3s ease-in-out;
        }

        /* Header styles */
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        /* Card styling */
        .card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
            color: #6366f1;
        }
        
        .card-body {
            padding: 1.5rem;
        }

        /* Dashboard cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.25rem;
            background: var(--gradient-primary);
            color: white;
            font-size: 1.5rem;
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }
        
        .stat-info p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin: 0;
        }

        /* User profile card */
        .user-profile-card {
            display: flex;
            flex-direction: column;
            background: var(--bg-secondary);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .profile-header {
            position: relative;
            height: 140px;
            background: var(--gradient-primary);
        }
        
        .profile-header-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
        }
        
        .profile-avatar {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 5px solid var(--bg-secondary);
            object-fit: cover;
            background-color: var(--bg-secondary);
        }
        
        .profile-body {
            padding: 60px 1.5rem 1.5rem;
            text-align: center;
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .profile-role {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        
        .profile-info-item {
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            gap: 0.5rem;
        }
        
        .profile-info-item i {
            color: #6366f1;
            width: 20px;
        }
        
        .edit-profile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background: var(--gradient-primary);
            color: white;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .edit-profile-btn i {
            margin-right: 0.5rem;
        }
        
        .edit-profile-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4);
            color: white;
        }

        /* Action buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-top: 1rem;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
            color: var(--text-primary);
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
            color: var(--text-primary);
        }
        
        .action-btn i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 16px;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover i {
            background: var(--gradient-primary);
            color: white;
            transform: scale(1.1);
        }
        
        .action-btn span {
            font-weight: 600;
            font-size: 1rem;
            text-align: center;
        }

        /* Welcome message card */
        .welcome-card {
            position: relative;
            background: var(--gradient-primary);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
            overflow: hidden;
        }
        
        .welcome-card h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }
        
        .welcome-card p {
            opacity: 0.9;
            max-width: 600px;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
            font-size: 1.05rem;
        }
        
        .welcome-bg {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 15rem;
            line-height: 1;
            opacity: 0.1;
            color: white;
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

        /* Footer */
        footer {
            text-align: center;
            padding: 1.5rem 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 2rem;
            border-top: 1px solid var(--card-border);
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
            box-shadow: 0 4px 15px rgba(99, 102, 141, 0.3);
            z-index: 100;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4);
        }

        /* Navbar for mobile */
        .navbar {
            display: none;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: var(--navbar-bg-light);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 99;
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
            padding: 0.5rem 0.75rem;
            color: var(--text-primary);
            background: var(--input-bg);
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .navbar-toggler:hover {
            background-color: var(--button-hover);
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
            --bg-dots: var(--bg-dots-dark);
            --glass-bg: var(--glass-bg-dark);
            --glass-border: var(--glass-border-dark);
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 90;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            display: block;
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }

        @keyframes slideOut {
            from { transform: translateX(0); }
            to { transform: translateX(-100%); }
        }
        
        .fade-in {
            opacity: 0;
            animation: fadeUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Media queries for responsiveness */
        @media (max-width: 992px) {
            .sidebar {
                width: 280px; /* Keep full width for sliding effect */
                transform: translateX(-100%);
                z-index: 1050;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
            
            .sidebar.show {
                transform: translateX(0);
                animation: slideIn 0.3s ease-in-out forwards;
            }
            
            .sidebar.hide {
                animation: slideOut 0.3s ease-in-out forwards;
            }
            
            .main-container {
                margin-left: 0;
                width: 100%;
            }
            
            .navbar {
                display: flex;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .user-profile-card {
                margin-bottom: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 1rem;
            }
            
            .welcome-card {
                padding: 1.5rem;
            }
            
            .welcome-card h2 {
                font-size: 1.5rem;
            }
            
            .profile-header {
                height: 120px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                bottom: -40px;
            }
            
            .profile-body {
                padding-top: 50px;
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
                <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture">
                <div>
                    <h3>Pilote Panel</h3>
                    <p><?= $displayName ?></p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="pilote.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Students</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/offerController.php" class="nav-link">
                        <i class="fas fa-file-lines"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/offerController.php?action=view" class="nav-link">
                        <i class="fas fa-briefcase"></i>
                        <span>Browse Offers</span>
                    </a>
                </li>
                <?php if ($loggedInUserId): ?>
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=pilote" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <?php endif; ?>
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

        <!-- Sidebar overlay for mobile -->
        <div class="sidebar-overlay"></div>

        <!-- Main content -->
        <div class="main-container">
            <!-- Top navbar for mobile -->
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none">
                <a class="navbar-brand" href="#">
                    <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile">
                    <span><?= $currentPage ?></span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Success message -->
            <?php if (!empty($successMessage)): ?>
                <div class="message success-message fade-in">
                    <i class="fa-solid fa-check-circle"></i> 
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>
            
            <!-- Welcome message -->
            <div class="welcome-card fade-in">
                <div class="welcome-bg">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h2>Welcome back, <?= $displayName ?>!</h2>
                <p>From your pilote dashboard, you can manage your students, companies, and internship offers. Use the quick actions below to navigate through your tools.</p>
            </div>
            
            <div class="row">
                <!-- User profile card -->
                <div class="col-lg-4 mb-4">
                    <div class="user-profile-card fade-in delay-1">
                        <div class="profile-header">
                            <div class="profile-header-overlay"></div>
                            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-avatar">
                        </div>
                        <div class="profile-body">
                            <h2 class="profile-name"><?= $displayName ?></h2>
                            <div class="profile-role">Pilote</div>
                            <div class="profile-info">
                                <div class="profile-info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?= $displayEmail ?></span>
                                </div>
                                <div class="profile-info-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Logged in since: <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></span>
                                </div>
                            </div>
                            <?php if ($loggedInUserId): ?>
                                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=pilote" class="edit-profile-btn">
                                    <i class="fas fa-user-pen"></i> Edit Profile
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick actions -->
                <div class="col-lg-8">
                    <div class="card fade-in delay-2">
                        <div class="card-header">
                            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                        </div>
                        <div class="card-body">
                            <div class="action-buttons">
                                <a href="../Controller/userController.php" class="action-btn">
                                    <i class="fas fa-users"></i>
                                    <span>Manage Students</span>
                                </a>
                                <a href="../Controller/companyController.php" class="action-btn">
                                    <i class="fas fa-building"></i>
                                    <span>Manage Companies</span>
                                </a>
                                <a href="../Controller/offerController.php" class="action-btn">
                                    <i class="fas fa-file-lines"></i>
                                    <span>Manage Offers</span>
                                </a>
                                <?php if ($loggedInUserId): ?>
                                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=pilote" class="action-btn">
                                    <i class="fas fa-user-pen"></i>
                                    <span>Edit Profile</span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer>
                <p>© <?= date('Y'); ?> Project Dev Web Application - Current time: <?= date('Y-m-d H:i:s') ?></p>
            </footer>
        </div>
    </div>

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
            
            // Sidebar toggle for mobile with overlay
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            
            if (sidebarToggle && sidebar && sidebarOverlay) {
                sidebarToggle.addEventListener('click', function() {
                    if (sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        sidebar.classList.add('hide');
                        sidebarOverlay.classList.remove('show');
                        
                        // Remove the hide class after animation completes
                        setTimeout(() => {
                            sidebar.classList.remove('hide');
                        }, 300);
                    } else {
                        sidebar.classList.add('show');
                        sidebarOverlay.classList.add('show');
                    }
                });
                
                // Close sidebar when clicking on overlay
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebar.classList.add('hide');
                    sidebarOverlay.classList.remove('show');
                    
                    // Remove the hide class after animation completes
                    setTimeout(() => {
                        sidebar.classList.remove('hide');
                    }, 300);
                });
                
                // Handle window resize events
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 992 && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>
