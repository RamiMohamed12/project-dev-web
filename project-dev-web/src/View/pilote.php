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
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilote Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="pilote-layout">
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
            <h2>Pilote Panel</h2>
        </div>
        <div class="sidebar-menu">
            <a href="pilote.php" class="active">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="../Controller/userController.php">
                <i class="fa-solid fa-users"></i>
                <span>Manage My Students</span>
            </a>
            <a href="../Controller/companyController.php">
                <i class="fa-solid fa-building"></i>
                <span>Manage My Companies</span>
            </a>
            <a href="../Controller/internshipController.php">
                <i class="fa-solid fa-briefcase"></i>
                <span>Manage Offers</span>
            </a>
            <?php if ($loggedInUserId): ?>
            <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=pilote">
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
                <h3>Manage My Students</h3>
                <p>Add, edit, or remove students from the system</p>
                <a href="../Controller/userController.php" class="card-link">Manage My Students →</a>
            </div>

            <div class="dashboard-card">
                <i class="fa-solid fa-building"></i>
                <h3>Manage My Companies</h3>
                <p>Handle company registrations and details</p>
                <a href="../Controller/companyController.php" class="card-link">Manage My Companies →</a>
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
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=pilote" class="card-link">Edit Profile →</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
