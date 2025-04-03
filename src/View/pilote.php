<?php
// Location: /home/demy/project-dev-web/src/View/pilote.php

// --- Authentication Check & Session ---
require_once __DIR__ . '/../Auth/AuthCheck.php';
require_once __DIR__ . '/../Auth/AuthSession.php'; // Include AuthSession for user data

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
AuthCheck::checkUserAuth('pilote', 'login.php');
// --- End Authentication Check ---

// Get Logged-in User Info
$userName = htmlspecialchars(AuthSession::getUserData('user_name') ?? 'Pilote');
$userEmail = htmlspecialchars(AuthSession::getUserData('user_email') ?? 'N/A');
$userId = AuthSession::getUserData('user_id'); // Used for nav link and button

// --- Message Handling ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}
// Add other messages if needed

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilote Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Assuming style.css exists -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style> /* Basic styles */
        body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
        header { background-color: #6c757d; /* Grey for Pilote */ color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 1.5em; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; }
        nav ul li { margin-left: 15px; }
        nav ul li a { color: #f8f9fa; text-decoration: none; padding: 5px 10px; }
        nav ul li a:hover, nav ul li a.active { color: #ffffff; background-color: #5a6268; border-radius: 4px; }
        main { padding: 20px; }
        footer { text-align: center; margin-top: 30px; padding: 15px; background-color: #e9ecef; color: #6c757d; font-size: 0.9em; border-top: 1px solid #dee2e6;}
        .user-info { background-color: #ffffff; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .dashboard-actions a { display: inline-block; margin: 5px; padding: 10px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.2s ease; }
        .dashboard-actions a:hover { background-color: #5a6268; }
        .dashboard-actions a i { margin-right: 5px; }
        /* Message Styles */
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    </style>
</head>
<body>
    <header>
        <h1><i class="fa-solid fa-user-tie"></i> Pilote Dashboard</h1>
        <nav>
            <ul>
                <li><a href="pilote.php" class="active">Dashboard</a></li>
                <li><a href="../Controller/userController.php">Manage My Students</a></li>
                <li><a href="../Controller/companyController.php">Manage My Companies</a></li>
                <li><a href="../Controller/offerController.php">Manage Offers</a></li>
                 <?php if ($userId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $userId ?>&type=pilote">My Profile</a></li>
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
            <p><strong>Role:</strong> Pilote</p>
            <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
        </div>

        <p>Manage students and companies you have added, and review internship offers.</p>

         <div class="dashboard-actions">
             <a href="../Controller/userController.php">
                 <i class="fa-solid fa-users"></i> Manage My Students
             </a>
             <a href="../Controller/companyController.php">
                 <i class="fa-solid fa-building"></i> Manage My Companies
             </a>
              <a href="../Controller/offerController.php">
                 <i class="fa-solid fa-file-lines"></i> Manage Offers
             </a>
             <?php if ($userId): ?>
                <a href="../Controller/editUser.php?id=<?= $userId ?>&type=pilote">
                    <i class="fa-solid fa-user-pen"></i> Edit My Profile
                </a>
             <?php endif; ?>
         </div>
    </main>

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
