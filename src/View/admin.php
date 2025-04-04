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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Make sure style.css is accessible -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Basic Styles - Add or modify as needed */
        body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
        header { background-color: #343a40; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 1.5em; display: flex; align-items: center; }
        .profile-pic-header { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 12px; border: 1px solid #6c757d;}
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; }
        nav ul li { margin-left: 15px; }
        nav ul li a { color: #f8f9fa; text-decoration: none; padding: 5px 10px; transition: background-color 0.2s ease; }
        nav ul li a:hover, nav ul li a.active { color: #ffffff; background-color: #495057; border-radius: 4px; }
        main { padding: 20px; max-width: 1200px; margin: 20px auto; }
        footer { text-align: center; margin-top: 30px; padding: 15px; background-color: #e9ecef; color: #6c757d; font-size: 0.9em; border-top: 1px solid #dee2e6;}
        .user-info { background-color: #ffffff; border: 1px solid #dee2e6; padding: 20px; margin-bottom: 25px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; /* Clearfix */ }
        .user-info .profile-pic-large { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; float: left; margin: 0 20px 10px 0; border: 3px solid #adb5bd; }
        .user-info h2 { margin-top: 0; margin-bottom: 15px; }
        .user-info p { margin-bottom: 8px; color: #495057; }
        .user-info strong { color: #212529; }
        .dashboard-actions { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .dashboard-actions a { display: inline-block; margin: 5px 10px 5px 0; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.2s ease; font-size: 0.95em; }
        .dashboard-actions a:hover { background-color: #0056b3; }
        .dashboard-actions a i { margin-right: 7px; }
        /* Message Styles */
        .message { padding: 12px 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    </style>
</head>
<body>
    <header>
        <h1>
            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="PFP" class="profile-pic-header">
            <i class="fa-solid fa-user-shield"></i> Admin Panel <!-- Added space -->
        </h1>
        <nav>
            <ul>
                 <li><a href="admin.php" class="active">Dashboard</a></li>
                 <li><a href="../Controller/userController.php">Manage Users</a></li>
                 <li><a href="../Controller/companyController.php">Manage Companies</a></li>
                 <li><a href="../Controller/internshipController.php">Manage Offers</a></li>
                 <?php if ($loggedInUserId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin">My Profile</a></li>
                 <?php endif; ?>
                 <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <!-- Display Success Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <div class="user-info">
             <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-large">
             <h2>Welcome, <?= $displayName ?>!</h2>
            <p><strong>Email:</strong> <?= $displayEmail ?></p>
            <p><strong>Role:</strong> Administrator</p>
            <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
        </div>

        <p style="font-size: 1.1em; color: #333;">Use the navigation or buttons below to manage all application data.</p>

        <div class="dashboard-actions">
             <a href="../Controller/userController.php"><i class="fa-solid fa-users-gear"></i> Manage All Users</a>
             <a href="../Controller/companyController.php"><i class="fa-solid fa-building"></i> Manage All Companies</a>
             <a href="../Controller/internshipController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a>
             <?php if ($loggedInUserId): ?>
                 <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin"><i class="fa-solid fa-user-pen"></i> Edit My Profile</a>
             <?php endif; ?>
        </div>
    </main>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
