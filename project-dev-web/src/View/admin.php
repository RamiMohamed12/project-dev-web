<?php
// Location: /home/demy/project-dev-web/src/View/admin.php

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
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
</head>
<body class="admin-layout">
    <!-- Navbar -->
    <nav class="top-navbar">
        <div class="nav-left">
            <h1>Welcome, <?= htmlspecialchars($displayName) ?>!</h1>
        </div>
        <div class="nav-right">
            <span class="nav-email"><?= htmlspecialchars($displayEmail) ?></span>
            
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture">
            <h2>Admin Panel</h2>
        </div>
        <div class="sidebar-menu">
            <a href="admin.php" class="active">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="../Controller/userController.php">
                <i class="fa-solid fa-users"></i>
                <span>Manage Users</span>
            </a>
            <a href="../Controller/companyController.php">
                <i class="fa-solid fa-building"></i>
                <span>Manage Companies</span>
            </a>
            <a href="../Controller/internshipController.php">
                <i class="fa-solid fa-briefcase"></i>
                <span>Manage Offers</span>
            </a>
            <?php if ($loggedInUserId): ?>
            <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin">
                <i class="fa-solid fa-user-gear"></i>
                <span>My Profile</span>
            </a>
            <?php endif; ?>
            <a href="../Controller/logoutController.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message">
                <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-actions">
            <div class="dashboard-card">
                <i class="fa-solid fa-users"></i>
                <h3>Manage Users</h3>
                <p>Add, edit, or remove users from the system</p>
                <a href="../Controller/userController.php" class="card-link">Manage Users →</a>
            </div>

            <div class="dashboard-card">
                <i class="fa-solid fa-building"></i>
                <h3>Manage Companies</h3>
                <p>Handle company registrations and details</p>
                <a href="../Controller/companyController.php" class="card-link">Manage Companies →</a>
            </div>

            <div class="dashboard-card">
                <i class="fa-solid fa-briefcase"></i>
                <h3>Manage Offers</h3>
                <p>Control internship offers and opportunities</p>
                <a href="../Controller/internshipController.php" class="card-link">Manage Offers →</a>
            </div>

            <?php if ($loggedInUserId): ?>
            <div class="dashboard-card">
                <i class="fa-solid fa-user-gear"></i>
                <h3>My Profile</h3>
                <p>Update your personal information</p>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin" class="card-link">Edit Profile →</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>

    <script>
    // Sidebar Toggle Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const footer = document.getElementById('footer');
        const toggleBtn = document.getElementById('toggleSidebar');
        const mobileToggle = document.getElementById('mobileToggle');
        
        // Check for saved sidebar state
        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'collapsed') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            footer.classList.add('expanded');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        }
        
        // Toggle sidebar on button click
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            footer.classList.toggle('expanded');
            
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('sidebarState', 'collapsed');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            } else {
                localStorage.setItem('sidebarState', 'expanded');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            }
        });
        
        // Mobile toggle
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
        });
        
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

        // Check for saved dark mode preference
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme) {
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'dark') {
                darkModeToggle.checked = true;
            }
        } else {
            // If no saved preference, use system preference
            if (prefersDarkScheme.matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
                darkModeToggle.checked = true;
            }
        }

        // Listen for toggle changes
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    });
    </script>
</body>
</html>

