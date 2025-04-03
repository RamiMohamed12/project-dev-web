<?php
// Location: /home/demy/project-dev-web/src/View/admin.php

// --- Authentication Check & Session ---
require_once __DIR__ . '/../Auth/AuthCheck.php';
require_once __DIR__ . '/../Auth/AuthSession.php'; // Include AuthSession for user data

// Start session MUST be before AuthCheck if AuthCheck potentially starts session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
AuthCheck::checkUserAuth('admin', 'login.php');
// --- End Authentication Check ---

// Get Logged-in User Info using AuthSession
$userName = htmlspecialchars(AuthSession::getUserData('user_name') ?? 'Admin');
$userEmail = htmlspecialchars(AuthSession::getUserData('user_email') ?? 'N/A');
$loggedInUserId = AuthSession::getUserData('user_id'); // Get ID for My Profile link

// --- Message Handling ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}
// You could add other success messages here if needed

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Assuming style.css exists -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style> /* Basic styles */
        body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
        header { background-color: #343a40; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 1.5em; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; }
        nav ul li { margin-left: 15px; }
        nav ul li a { color: #f8f9fa; text-decoration: none; padding: 5px 10px; }
        nav ul li a:hover, nav ul li a.active { color: #ffffff; background-color: #495057; border-radius: 4px; }
        main { padding: 20px; }
        footer { text-align: center; margin-top: 30px; padding: 15px; background-color: #e9ecef; color: #6c757d; font-size: 0.9em; border-top: 1px solid #dee2e6;}
        .user-info { background-color: #ffffff; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .dashboard-actions a { display: inline-block; margin: 5px; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.2s ease; }
        .dashboard-actions a:hover { background-color: #0056b3; }
        .dashboard-actions a i { margin-right: 5px; }
        /* Message Styles */
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    </style>
</head>
<body>
    <header>
        <h1><i class="fa-solid fa-user-shield"></i> Admin Panel</h1>
        <nav>
            <ul>
                 <li><a href="admin.php" class="active">Dashboard</a></li>
                 <li><a href="../Controller/userController.php">Manage Users</a></li>
                 <li><a href="../Controller/companyController.php">Manage Companies</a></li>
                 <li><a href="../Controller/offerController.php">Manage Offers</a></li>
                 <!-- ***** ADDED MY PROFILE LINK ***** -->
                 <?php if ($loggedInUserId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin">My Profile</a></li>
                 <?php endif; ?>
                 <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Welcome, <?= $userName ?>!</h2>

        <!-- Display Success Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <div class="user-info">
            <p><strong>Email:</strong> <?= $userEmail ?></p>
            <p><strong>Role:</strong> Administrator</p>
            <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
        </div>

        <p>Use the navigation to manage all application data.</p>

        <div class="dashboard-actions">
             <a href="../Controller/userController.php"><i class="fa-solid fa-users-gear"></i> Manage All Users</a>
             <a href="../Controller/companyController.php"><i class="fa-solid fa-building"></i> Manage All Companies</a>
             <a href="../Controller/offerController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a>
             <!-- ***** ADDED MY PROFILE BUTTON ***** -->
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
