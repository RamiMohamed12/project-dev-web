<?php
// --- Required Includes & Session ---
require_once __DIR__ . '/../../config/config.php'; // Need $conn for DB access
require_once __DIR__ . '/../Auth/AuthCheck.php';   // Checks if user is allowed here
require_once __DIR__ . '/../Auth/AuthSession.php'; // For getting session data
require_once __DIR__ . '/../Model/user.php';      // To fetch user details from DB

// Start session VERY FIRST
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Authentication Check ---
AuthCheck::checkUserAuth('admin', 'login.php');
// --- End Authentication Check ---

// Get Logged-in User Info from Session initially
$loggedInUserId = AuthSession::getUserData('user_id');
$sessionUserName = AuthSession::getUserData('user_name');
$sessionUserEmail = AuthSession::getUserData('user_email');

// --- Fetch Full User Details (including profile pic) from Database ---
$userDetails = null;
$profilePicSrc = null;
$dbUserName = null;
$dbUserEmail = null;
$defaultPic = '../View/images/default_avatar.png'; // ** ADJUST PATH AS NEEDED **

if ($loggedInUserId && isset($conn)) {
    try {
        $userModel = new User($conn);
        $userDetails = $userModel->readAdmin($loggedInUserId); // Fetch this admin's details

        if ($userDetails) {
            // Use name/email from DB if available
            $dbUserName = $userDetails['name'];
            $dbUserEmail = $userDetails['email'];

            // Generate profile picture source if data exists
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                 // PDO might return BLOB as resource stream or string depending on config/driver
                 // Assuming string or easily readable data here. Adjust if needed.
                 $picData = is_resource($userDetails['profile_picture'])
                            ? stream_get_contents($userDetails['profile_picture'])
                            : $userDetails['profile_picture'];
                 if ($picData) {
                    $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                 }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching admin details for dashboard (ID: $loggedInUserId): " . $e->getMessage());
        // Silently fail, fall back to session data
    }
}

// Determine final display name/email (prefer DB, fallback to session)
$displayName = htmlspecialchars($dbUserName ?? $sessionUserName ?? 'Admin');
$displayEmail = htmlspecialchars($dbUserEmail ?? $sessionUserEmail ?? 'N/A');


// --- Message Handling (e.g., after profile update) ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Navigui</title>
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
        
        /* Futuristic background elements */
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
        
        /* Glassmorphism effect */
        .glass {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--card-shadow);
        }
        
        /* Main Layout */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            height: 100vh;
            padding: 2rem 1.5rem;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
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
        
        /* Bento grid layout */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-auto-rows: minmax(100px, auto);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .bento-item {
            padding: 1.5rem;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .bento-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        /* Grid layout */
        .welcome-card {
            grid-column: span 3;
            grid-row: span 1;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid transparent;
            background: var(--gradient-primary);
            background-origin: border-box;
            background-clip: content-box, border-box;
        }
        
        .profile-details h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .profile-details p {
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .profile-details p i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .time-card {
            grid-column: span 1;
            grid-row: span 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .time-card .time {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .time-card .date {
            color: var(--text-secondary);
        }
        
        .stats-card {
            grid-column: span 1;
            grid-row: span 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stats-number {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .quick-actions {
            grid-column: span 4;
            grid-row: span 1;
        }
        
        .actions-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--card-border);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 1rem;
            border-radius: 16px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            color: var(--text-primary);
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .action-btn i {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }
        
        .action-btn span {
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            background: var(--gradient-primary);
            border-color: transparent;
            color: white;
        }
        
        .action-btn:hover i {
            color: white;
        }
        
        .recent-activity {
            grid-column: span 2;
            grid-row: span 2;
        }
        
        .activity-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--card-border);
        }
        
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid var(--card-border);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: var(--bg-gradient-spot1);
            color: #6366f1;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-content h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .activity-content p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .activity-time {
            color: var(--text-secondary);
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .system-status {
            grid-column: span 2;
            grid-row: span 1;
        }
        
        .status-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--card-border);
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .status-item {
            background: var(--input-bg);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
        }
        
        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            background: var(--bg-gradient-spot1);
            color: #6366f1;
            flex-shrink: 0;
        }
        
        .status-info {
            flex: 1;
        }
        
        .status-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .status-info p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin-bottom: 0;
        }
        
        .calendar-card {
            grid-column: span 2;
            grid-row: span 1;
        }
        
        .calendar-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--card-border);
        }
        
        .calendar-content {
            display: flex;
            justify-content: space-between;
        }
        
        .calendar-day {
            text-align: center;
            flex: 1;
        }
        
        .day-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .day-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-weight: 500;
        }
        
        .day-number.today {
            background: var(--gradient-primary);
            color: white;
        }
        
        .day-events {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        
        /* Alert styling */
        .alert {
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
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
        
        /* Navbar styles */
        .navbar {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: var(--navbar-bg-light);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 2rem;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: var(--text-primary);
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
        
        .nav-link {
            color: var(--text-secondary);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .nav-link i {
            margin-right: 0.5rem;
        }
        
        .nav-link.active, .nav-link:hover {
            color: var(--text-primary);
            background-color: var(--input-bg);
        }
        
        /* Dark mode styles */
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
            --navbar-bg: var(--navbar-bg-dark);
        }
        
        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeUp {
            animation: fadeUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .bento-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .welcome-card {
                grid-column: span 2;
            }
            
            .quick-actions {
                grid-column: span 3;
            }
            
            .actions-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .recent-activity, .system-status, .calendar-card {
                grid-column: span 3;
            }
        }
        
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
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .bento-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .welcome-card, .quick-actions, .recent-activity, .system-status, .calendar-card {
                grid-column: span 2;
            }
            
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .bento-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .welcome-card, .time-card, .stats-card, .quick-actions, .recent-activity, .system-status, .calendar-card {
                grid-column: span 1;
            }
            
            .actions-grid, .status-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-card {
                flex-direction: column;
                text-align: center;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .navbar-brand span {
                font-size: 0.9rem;
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
        <aside class="sidebar glass animate-fadeUp">
            <div class="sidebar-header">
                <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture">
                <div>
                    <h3><?= $displayName ?></h3>
                    <p><?= $displayEmail ?></p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin.php" class="nav-link active">
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
                    <a href="../Controller/internshipController.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <?php if ($loggedInUserId): ?>
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin" class="nav-link">
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
        
        <!-- Main content -->
        <div class="main-container">
            <!-- Top navbar for mobile -->
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">
                        <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile">
                        <span><i class="fas fa-user-shield me-2"></i>Admin Panel</span>
                    </a>
                    <button class="navbar-toggler" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </nav>
            
            <!-- Success message -->
            <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success animate-fadeUp" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
            </div>
            <?php endif; ?>
            
            <!-- Bento grid layout -->
            <div class="bento-grid">
                <!-- Welcome card -->
                <div class="bento-item glass welcome-card animate-fadeUp">
                    <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-image">
                    <div class="profile-details">
                        <h2>Welcome, <?= $displayName ?>!</h2>
                        <p><i class="fas fa-envelope me-2"></i> <?= $displayEmail ?></p>
                        <p><i class="fas fa-user-tag me-2"></i> Administrator</p>
                        <p><i class="fas fa-clock me-2"></i> Logged in since: <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
                    </div>
                </div>
                
                <!-- Time card -->
                <div class="bento-item glass time-card animate-fadeUp delay-1">
                    <div class="time" id="currentTime">12:00</div>
                    <div class="date" id="currentDate">Monday, Jan 1</div>
                </div>
                
                <!-- Stats cards -->
                <div class="bento-item glass stats-card animate-fadeUp delay-1">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number">150</div>
                    <div class="stats-label">Total Users</div>
                </div>
                
                <div class="bento-item glass stats-card animate-fadeUp delay-2">
                    <div class="stats-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stats-number">45</div>
                    <div class="stats-label">Companies</div>
                </div>
                
                <div class="bento-item glass stats-card animate-fadeUp delay-3">
                    <div class="stats-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stats-number">78</div>
                    <div class="stats-label">Active Offers</div>
                </div>
                
                <div class="bento-item glass stats-card animate-fadeUp delay-4">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">24</div>
                    <div class="stats-label">Approved Today</div>
                </div>
                
                <!-- Quick actions -->
                <div class="bento-item glass quick-actions animate-fadeUp delay-2">
                    <h3 class="actions-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="../Controller/userController.php" class="action-btn">
                            <i class="fas fa-users-gear"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="../Controller/companyController.php" class="action-btn">
                            <i class="fas fa-building"></i>
                            <span>Manage Companies</span>
                        </a>
                        <a href="../Controller/internshipController.php" class="action-btn">
                            <i class="fas fa-file-alt"></i>
                            <span>Manage Offers</span>
                        </a>
                        <?php if ($loggedInUserId): ?>
                        <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin" class="action-btn">
                            <i class="fas fa-user-pen"></i>
                            <span>Edit Profile</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent activity -->
                <div class="bento-item glass recent-activity animate-fadeUp delay-3">
                    <h3 class="activity-title"><i class="fas fa-history me-2"></i>Recent Activity</h3>
                    <ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <h4>New User Registered</h4>
                                <p>John Doe created a new student account</p>
                            </div>
                            <div class="activity-time">2 hours ago</div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Company Updated</h4>
                                <p>Acme Corp updated their company profile</p>
                            </div>
                            <div class="activity-time">3 hours ago</div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="activity-content">
                                <h4>New Internship Offer</h4>
                                <p>TechStart posted a new web development internship</p>
                            </div>
                            <div class="activity-time">5 hours ago</div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Offer Approved</h4>
                                <p>You approved DataSys's data analyst internship</p>
                            </div>
                            <div class="activity-time">Yesterday</div>
                        </li>
                    </ul>
                </div>
                
                <!-- System status -->
                <div class="bento-item glass system-status animate-fadeUp delay-4">
                    <h3 class="status-title"><i class="fas fa-server me-2"></i>System Status</h3>
                    <div class="status-grid">
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="status-info">
                                <h4>Database</h4>
                                <p>Connected • 45ms response</p>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <div class="status-info">
                                <h4>Server Load</h4>
                                <p>Normal • 23% CPU</p>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-memory"></i>
                            </div>
                            <div class="status-info">
                                <h4>Memory Usage</h4>
                                <p>Good • 2.1GB / 8GB</p>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-hdd"></i>
                            </div>
                            <div class="status-info">
                                <h4>Storage</h4>
                                <p>75% Free • 186GB available</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Calendar preview -->
                <div class="bento-item glass calendar-card animate-fadeUp delay-5">
                    <h3 class="calendar-title"><i class="fas fa-calendar me-2"></i>Calendar</h3>
                    <div class="calendar-content">
                        <div class="calendar-day">
                            <div class="day-name">Mon</div>
                            <div class="day-number">12</div>
                            <div class="day-events">2 events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Tue</div>
                            <div class="day-number">13</div>
                            <div class="day-events">1 event</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Wed</div>
                            <div class="day-number today">14</div>
                            <div class="day-events">4 events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Thu</div>
                            <div class="day-number">15</div>
                            <div class="day-events">No events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Fri</div>
                            <div class="day-number">16</div>
                            <div class="day-events">1 event</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Sat</div>
                            <div class="day-number">17</div>
                            <div class="day-events">No events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Sun</div>
                            <div class="day-number">18</div>
                            <div class="day-events">No events</div>
                        </div>
                    </div>
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
            
            // Update time and date
            function updateDateTime() {
                const now = new Date();
                
                // Update time
                const timeElement = document.getElementById('currentTime');
                if (timeElement) {
                    timeElement.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                
                // Update date
                const dateElement = document.getElementById('currentDate');
                if (dateElement) {
                    const options = { weekday: 'long', month: 'short', day: 'numeric' };
                    dateElement.textContent = now.toLocaleDateString([], options);
                }
            }
            
            // Initial update
            updateDateTime();
            
            // Update every minute
            setInterval(updateDateTime, 60000);
            
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
            
            // Add hover animations to bento items
            const bentoItems = document.querySelectorAll('.bento-item');
            
            bentoItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'var(--card-shadow)';
                });
            });
        });
    </script>
</body>
</html>