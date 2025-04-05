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
        // Assuming User model can handle reading admin details by ID
        // If not, you might need a specific Admin model or direct query here
        $userModel = new User($conn); // Or an Admin specific model if you have one
        $userDetails = $userModel->readAdmin($loggedInUserId); // Fetch this admin's details

        if ($userDetails) {
            $dbUserName = $userDetails['name'];
            $dbUserEmail = $userDetails['email'];
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
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
    }
}

// Determine final display name/email
$displayName = htmlspecialchars($dbUserName ?? $sessionUserName ?? 'Admin');
$displayEmail = htmlspecialchars($dbUserEmail ?? $sessionUserEmail ?? 'N/A');


// --- Message Handling ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}

// --- Fetch Dashboard Statistics ---
$totalUsers = 0;
$totalCompanies = 0;
$activeOffers = 0;
$recentActivities = []; // Initialize empty array

if (isset($conn)) {
    try {
        // Total Users (Admin + Pilote + Student)
        $stmtUsers = $conn->query("
            SELECT (SELECT COUNT(*) FROM admin) +
                   (SELECT COUNT(*) FROM pilote) +
                   (SELECT COUNT(*) FROM student) AS total
        ");
        $totalUsers = $stmtUsers->fetchColumn() ?: 0;

        // Total Companies
        $stmtCompanies = $conn->query("SELECT COUNT(*) FROM company");
        $totalCompanies = $stmtCompanies->fetchColumn() ?: 0;

        // Active Offers (Assuming all internships in the table are active)
        $stmtOffers = $conn->query("SELECT COUNT(*) FROM internship");
        $activeOffers = $stmtOffers->fetchColumn() ?: 0;

        // Fetch Recent Activity (Latest 5 created/updated items)
        // Order by updated_at DESC for most tables, created_at for application
        // Using LIMIT within subqueries can optimize performance on very large tables
         $sqlActivity = "
         (SELECT 'Admin Added/Updated' as type, name as title, updated_at as activity_time, 'fa-user-shield' as icon, id_admin as item_id, 'admin' as item_type FROM admin ORDER BY updated_at DESC LIMIT 5)
         UNION ALL
         (SELECT 'Pilote Added/Updated' as type, name as title, updated_at as activity_time, 'fa-user-tie' as icon, id_pilote as item_id, 'pilote' as item_type FROM pilote ORDER BY updated_at DESC LIMIT 5)
         UNION ALL
         (SELECT 'Student Added/Updated' as type, name as title, updated_at as activity_time, 'fa-user-graduate' as icon, id_student as item_id, 'student' as item_type FROM student ORDER BY updated_at DESC LIMIT 5)
         UNION ALL
         (SELECT 'Company Added/Updated' as type, name_company as title, updated_at as activity_time, 'fa-building' as icon, id_company as item_id, 'company' as item_type FROM company ORDER BY updated_at DESC LIMIT 5)
         UNION ALL
         (SELECT 'Offer Added/Updated' as type, title as title, updated_at as activity_time, 'fa-file-alt' as icon, id_internship as item_id, 'internship' as item_type FROM internship ORDER BY updated_at DESC LIMIT 5)
         UNION ALL
         (SELECT 'Application Submitted' as type, CONCAT('Application #', id_application) as title, created_at as activity_time, 'fa-file-signature' as icon, id_application as item_id, 'application' as item_type FROM application ORDER BY created_at DESC LIMIT 5)
         ORDER BY activity_time DESC
         LIMIT 5
         ";

        $stmtActivity = $conn->query($sqlActivity);
        $recentActivities = $stmtActivity->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error fetching dashboard data: " . $e->getMessage());
        // Assign error indicators or keep defaults
        $totalUsers = 'Error';
        $totalCompanies = 'Error';
        $activeOffers = 'Error';
        $recentActivities = []; // Keep empty on error
    }
}

/**
 * Helper function to format time difference (optional, but nice)
 * @param string $datetime MySQL timestamp string
 * @param bool $full Return full format or short
 * @return string Formatted time difference
 */
function time_elapsed_string($datetime, $full = false) {
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    } catch (Exception $e) {
        // Handle invalid date format gracefully
        error_log("Error parsing date for time_elapsed_string: " . $datetime . " - " . $e->getMessage());
        // Fallback to simple date format
        try {
            $date = new DateTime($datetime);
            return $date->format('M j, Y H:i');
        } catch (Exception $inner_e) {
            return 'invalid date';
        }
    }
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
        /* --- PASTE YOUR EXISTING CSS HERE --- */
        /* (Your extensive CSS from the original file goes here) */
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
            word-break: break-all; /* Prevent long emails overflowing */
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
            /* Adjust columns based on number of stat cards */
            grid-template-columns: repeat(3, 1fr); /* Changed from 4 to 3 */
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
        /* Adjust welcome card span */
        .welcome-card {
            grid-column: span 2; /* Changed from 3 to 2 */
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

        /* Adjust quick actions span */
        .quick-actions {
            grid-column: span 3; /* Changed from 4 to 3 */
            grid-row: span 1;
        }

        .actions-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--card-border);
        }

        /* Adjust actions grid columns */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); /* Make responsive */
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

        /* Adjust Recent activity span */
        .recent-activity {
             /* Example: Make it span 2 columns */
            grid-column: span 2;
            grid-row: span 2; /* Make it taller */
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
             max-height: 300px; /* Limit height if needed */
             overflow-y: auto; /* Add scrollbar if content exceeds max-height */
        }
        .activity-list::-webkit-scrollbar {
            width: 4px;
        }
        .activity-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .activity-list::-webkit-scrollbar-thumb {
            background-color: var(--text-secondary);
            border-radius: 10px;
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
            margin-right: 0.5rem; /* Add some space before time */
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
            word-break: break-word; /* Break long titles */
        }

        .activity-time {
            color: var(--text-secondary);
            font-size: 0.8rem;
            white-space: nowrap;
            margin-left: auto; /* Push time to the right */
            padding-left: 0.5rem; /* Space between content and time */
        }

        /* Adjust system status span */
        .system-status {
             /* Example: Make it span 1 column */
            grid-column: span 1;
            grid-row: span 1; /* Or span 2 if needed */
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
            /* Adjust status grid columns */
            grid-template-columns: 1fr; /* Stack items */
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

        /* Calendar Card - Adjust span as needed */
        .calendar-card {
            /* Example: Make it span 3 columns */
            grid-column: span 3;
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
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }

        .calendar-day {
            text-align: center;
            flex: 1;
            min-width: 60px; /* Ensure minimum width */
            padding: 0.5rem 0.25rem;
        }

        .day-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .day-number {
            width: 35px; /* Adjust size */
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-weight: 500;
            font-size: 0.9rem; /* Adjust size */
        }

        .day-number.today {
            background: var(--gradient-primary);
            color: white;
        }

        .day-events {
            font-size: 0.75rem; /* Adjust size */
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

        /* Re-apply nav-link styles from sidebar section if needed for top nav */
        /* .navbar .nav-link { ... } */

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
             --bg-dots: var(--bg-dots-dark); /* Ensure grid dots adapt */
        }
        .dark-mode .bg-grid {
             background-image:
                radial-gradient(circle, var(--bg-dots-dark) 1px, transparent 1px);
        }


        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeUp {
            animation: fadeUp 0.6s ease forwards;
            opacity: 0; /* Start hidden */
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        /* Add more delays if needed */


        /* Responsive adjustments */
        @media (max-width: 1200px) {
             /* Adjust grid for medium screens if 3 columns doesn't look right */
             .bento-grid {
                grid-template-columns: repeat(2, 1fr); /* Example: Switch to 2 columns */
            }
            /* Adjust spans accordingly */
            .welcome-card { grid-column: span 1; }
            .time-card { grid-column: span 1; }
            .stats-card { grid-column: span 1; }
            .quick-actions { grid-column: span 2; }
            .recent-activity { grid-column: span 1; grid-row: span 2;}
            .system-status { grid-column: span 1; grid-row: span 1;}
            .calendar-card { grid-column: span 2; }
            .actions-grid { grid-template-columns: repeat(3, 1fr); } /* Or auto-fit */
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.5rem;
                transform: translateX(-100%);
                z-index: 1050; /* Ensure sidebar is above content */
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-header h3, .sidebar-header p, .sidebar .nav-link span {
                display: none;
            }

            .sidebar-header {
                justify-content: center;
                margin-bottom: 2rem;
            }

            .sidebar-header img {
                margin-right: 0;
            }

             .sidebar .nav-link { /* Target sidebar links specifically */
                justify-content: center;
                padding: 1rem;
            }

            .sidebar .nav-link i { /* Target sidebar icons specifically */
                margin-right: 0;
                font-size: 1.5rem;
            }

             .sidebar .logout-btn { /* Target sidebar logout */
                justify-content: center;
                padding: 1rem;
            }

            .sidebar .logout-btn i { /* Target sidebar logout icon */
                margin-right: 0;
            }

            .sidebar .logout-btn span { /* Target sidebar logout text */
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

             /* Keep 2 columns on tablets or adjust as needed */
             .bento-grid {
                 grid-template-columns: repeat(2, 1fr);
             }
             .welcome-card { grid-column: span 2; } /* Make welcome wider */
             .time-card { grid-column: span 1; }
             .stats-card { grid-column: span 1; }
             .quick-actions { grid-column: span 2; }
             .recent-activity { grid-column: span 1; grid-row: span 2;}
             .system-status { grid-column: span 1; grid-row: span 1;}
             .calendar-card { grid-column: span 2; }
             .actions-grid { grid-template-columns: repeat(2, 1fr); } /* Or auto-fit */
        }

        @media (max-width: 768px) {
            .bento-grid {
                grid-template-columns: 1fr; /* Single column on small screens */
                gap: 1rem;
            }

             /* All items span 1 column */
            .welcome-card, .time-card, .stats-card, .quick-actions, .recent-activity, .system-status, .calendar-card {
                grid-column: span 1;
                 grid-row: auto; /* Reset row spans */
            }
             .recent-activity {
                 max-height: none; /* Remove height limit if needed */
                 overflow-y: visible;
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
             .calendar-content {
                 flex-direction: column; /* Stack calendar days */
             }
             .calendar-day {
                 margin-bottom: 0.5rem;
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
                 <!-- Navigation Links -->
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
                    <!-- Ensure the link points correctly to the edit user page for admins -->
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <?php endif; ?>
                
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
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none glass"> <!-- Added glass effect -->
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
                        <p><i class="fas fa-clock me-2"></i> Logged in: <?= date('Y-m-d H:i', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
                    </div>
                </div>

                <!-- Time card -->
                <div class="bento-item glass time-card animate-fadeUp delay-1">
                    <div class="time" id="currentTime">--:--</div>
                    <div class="date" id="currentDate">Loading...</div>
                </div>

                <!-- Stats cards -->
                <div class="bento-item glass stats-card animate-fadeUp delay-1">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <!-- Use fetched data -->
                    <div class="stats-number"><?= htmlspecialchars($totalUsers) ?></div>
                    <div class="stats-label">Total Users</div>
                </div>

                <div class="bento-item glass stats-card animate-fadeUp delay-2">
                    <div class="stats-icon">
                        <i class="fas fa-building"></i>
                    </div>
                     <!-- Use fetched data -->
                    <div class="stats-number"><?= htmlspecialchars($totalCompanies) ?></div>
                    <div class="stats-label">Companies</div>
                </div>

                <div class="bento-item glass stats-card animate-fadeUp delay-3">
                    <div class="stats-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                     <!-- Use fetched data -->
                    <div class="stats-number"><?= htmlspecialchars($activeOffers) ?></div>
                    <div class="stats-label">Active Offers</div>
                </div>

                <!-- REMOVED the "Approved Today" card -->

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
                         <!-- Ensure the link points correctly to the edit user page for admins -->
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
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas <?= htmlspecialchars($activity['icon'] ?? 'fa-question-circle') ?>"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h4><?= htmlspecialchars($activity['type']) ?></h4>
                                        <p><?= htmlspecialchars($activity['title']) ?></p>
                                        <?php /* Optional: Add a link if needed
                                        $link = '#'; // Default link
                                        // Example: Link to edit page based on type
                                        if ($activity['item_type'] === 'company') {
                                            $link = "../Controller/editCompany.php?id=" . htmlspecialchars($activity['item_id']);
                                        } // Add more conditions for other types if needed
                                        echo '<p><a href="' . $link . '">' . htmlspecialchars($activity['title']) . '</a></p>';
                                        */ ?>
                                    </div>
                                    <div class="activity-time">
                                        <?= time_elapsed_string($activity['activity_time']) ?>
                                        <?php /* Or simpler formatting:
                                         try {
                                            $date = new DateTime($activity['activity_time']);
                                            echo $date->format('M j, H:i'); // e.g., Aug 14, 15:30
                                         } catch (Exception $e) { echo 'Invalid date'; }
                                         */
                                        ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="activity-item">
                                <div class="activity-content" style="margin-left: 0; text-align: center;"> <!-- Center if no icon -->
                                    <p>No recent activity found.</p>
                                </div>
                            </li>
                        <?php endif; ?>
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
                                <!-- You could add a check here if $conn exists -->
                                <p><?= isset($conn) ? 'Connected' : 'Connection Error' ?></p>
                            </div>
                        </div>
                        <div class="status-item">
                             <div class="status-icon">
                                <i class="fas fa-shield-alt"></i> <!-- Example: Security Status -->
                            </div>
                            <div class="status-info">
                                <h4>Security</h4>
                                <p>HTTPS Enabled</p> <!-- Example static info -->
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-cogs"></i> <!-- Example: PHP Version -->
                            </div>
                             <div class="status-info">
                                <h4>PHP Version</h4>
                                <p><?= phpversion(); ?></p>
                            </div>
                        </div>
                        <!-- Add more relevant status items if desired -->
                    </div>
                </div>

                 <!-- Calendar preview -->
                <div class="bento-item glass calendar-card animate-fadeUp delay-5">
                    <h3 class="calendar-title"><i class="fas fa-calendar me-2"></i>This Week</h3>
                    <div class="calendar-content" id="calendarWeekPreview">
                        <!-- Calendar days will be populated by JavaScript -->
                        <div class="text-center p-3">Loading Calendar...</div>
                    </div>
                </div>
            </div> <!-- End bento-grid -->
        </div> <!-- End main-container -->
    </div> <!-- End main-wrapper -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');

            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            function applyTheme(theme) {
                if (theme === 'dark') {
                    body.classList.add('dark-mode');
                    icon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    body.classList.remove('dark-mode');
                    icon.classList.replace('fa-sun', 'fa-moon');
                }
            }

            if (savedTheme) {
                applyTheme(savedTheme);
            } else {
                applyTheme(prefersDark ? 'dark' : 'light');
            }

            themeToggle.addEventListener('click', function() {
                const newTheme = body.classList.contains('dark-mode') ? 'light' : 'dark';
                applyTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });

            // Update time and date
            function updateDateTime() {
                const now = new Date();
                const timeElement = document.getElementById('currentTime');
                const dateElement = document.getElementById('currentDate');

                if (timeElement) {
                    timeElement.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }); // Use 24hr format or change options
                }
                if (dateElement) {
                    const options = { weekday: 'long', month: 'short', day: 'numeric' };
                    dateElement.textContent = now.toLocaleDateString([], options);
                }
            }
            updateDateTime(); // Initial call
            setInterval(updateDateTime, 30000); // Update every 30 seconds

            // Simple Calendar Week Preview
             function updateCalendarPreview() {
                const calendarContainer = document.getElementById('calendarWeekPreview');
                if (!calendarContainer) return;

                calendarContainer.innerHTML = ''; // Clear previous content
                const today = new Date();
                const dayOfWeek = today.getDay(); // 0=Sun, 1=Mon, ..., 6=Sat
                const startOfWeek = new Date(today);
                 // Adjust to start week on Monday (optional)
                 const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); // Adjust if Sunday is 0
                 startOfWeek.setDate(diff);


                const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

                for (let i = 0; i < 7; i++) {
                    const currentDay = new Date(startOfWeek);
                    currentDay.setDate(startOfWeek.getDate() + i);

                    const dayDiv = document.createElement('div');
                    dayDiv.classList.add('calendar-day');

                    const nameDiv = document.createElement('div');
                    nameDiv.classList.add('day-name');
                    nameDiv.textContent = days[i]; // Use calculated day name

                    const numberDiv = document.createElement('div');
                    numberDiv.classList.add('day-number');
                    numberDiv.textContent = currentDay.getDate();

                    // Highlight today
                    if (currentDay.toDateString() === today.toDateString()) {
                        numberDiv.classList.add('today');
                    }

                    // You could add dummy event counts here or fetch real ones via AJAX
                    const eventsDiv = document.createElement('div');
                    eventsDiv.classList.add('day-events');
                    // Example: Random events for demo
                    const eventCount = (i === 2) ? Math.floor(Math.random() * 3) + 1 : (Math.random() > 0.7 ? 1 : 0);
                    eventsDiv.textContent = eventCount > 0 ? `${eventCount} event${eventCount > 1 ? 's' : ''}` : 'No events';

                    dayDiv.appendChild(nameDiv);
                    dayDiv.appendChild(numberDiv);
                    dayDiv.appendChild(eventsDiv);
                    calendarContainer.appendChild(dayDiv);
                }
            }
             updateCalendarPreview(); // Initial call


            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContainer = document.querySelector('.main-container'); // Need this to detect clicks outside

            if (sidebarToggle && sidebar && mainContainer) {
                sidebarToggle.addEventListener('click', (event) => {
                     event.stopPropagation(); // Prevent click from immediately closing sidebar
                    sidebar.classList.toggle('show');
                });

                // Close sidebar when clicking outside on mobile
                 mainContainer.addEventListener('click', function(event) {
                     // Check if sidebar is shown and the click was not inside the sidebar or on the toggle button
                    if (sidebar.classList.contains('show') && !sidebar.contains(event.target)) {
                         // Check screen width - only close on mobile viewports where sidebar hides
                         if (window.innerWidth < 992) {
                             sidebar.classList.remove('show');
                         }
                    }
                });
            }

             // Ensure animations trigger after initial load
             // Use requestAnimationFrame for smoother start
             window.requestAnimationFrame(() => {
                 const animatedItems = document.querySelectorAll('.animate-fadeUp');
                 animatedItems.forEach(item => {
                     // Trigger reflow before adding class if needed, but usually direct class add works
                     item.style.animationPlayState = 'running';
                 });
             });

        });
    </script>
</body>
</html>
