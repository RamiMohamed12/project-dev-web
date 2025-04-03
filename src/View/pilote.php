<?php
// Location: /home/demy/project-dev-web/src/View/pilote.php

// --- Authentication Check ---
// This MUST be the very first thing before any HTML or other PHP output
require_once __DIR__ . '/../Auth/AuthCheck.php';

// **** CORRECT ROLE CHECK FOR PILOTE ****
AuthCheck::checkUserAuth('pilote', 'login.php');
// --- End Authentication Check ---

// If the check passes, session variables are available and the user is authorized.
// Start session IF NOT ALREADY STARTED (safe to call again)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user data safely from session
$userName = htmlspecialchars(AuthSession::getUserData('user_name') ?? 'Pilote');
$userEmail = htmlspecialchars(AuthSession::getUserData('user_email') ?? 'N/A');
$userId = AuthSession::getUserData('user_id'); // Get pilote ID for potential profile link etc.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilote Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Make sure style.css exists here or adjust path -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Add specific dashboard styles if needed -->
    <style> /* Basic styles - adapt or use your style.css */
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
    </style>
</head>
<body>
    <header>
        <h1><i class="fa-solid fa-user-tie"></i> Pilote Dashboard</h1>
        <nav>
            <ul>
                <li><a href="pilote.php" class="active">Dashboard</a></li>
                <!-- Links relevant to pilotes -->
                <!-- NOTE: Adjust these links to your actual Controllers/Views -->
                <li><a href="../Controller/piloteController.php?action=viewStudents">Assigned Students</a></li>
                <li><a href="../Controller/piloteController.php?action=viewCompanies">Companies</a></li>
                <li><a href="../Controller/piloteController.php?action=viewOffers">Offers</a></li>
                 <?php if ($userId): ?>
                    <!-- Link to edit own profile -->
                    <li><a href="../Controller/profileController.php?action=edit&type=pilote">My Profile</a></li>
                 <?php endif; ?>
                <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>

    <main>
         <h2>Welcome, <?= $userName ?>!</h2>
        <div class="user-info">
            <p><strong>Email:</strong> <?= $userEmail ?></p>
            <p><strong>Role:</strong> Pilote</p>
            <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
        </div>

        <p>From this dashboard, you can manage students assigned to you, view company information, and review internship offers.</p>

         <!-- Add dashboard widgets or content specific to pilotes here -->
         <div class="dashboard-actions">
             <a href="../Controller/piloteController.php?action=viewStudents">
                 <i class="fa-solid fa-users"></i> Manage Students
             </a>
             <a href="../Controller/piloteController.php?action=viewCompanies">
                 <i class="fa-solid fa-building"></i> View Companies
             </a>
              <a href="../Controller/piloteController.php?action=viewOffers">
                 <i class="fa-solid fa-file-lines"></i> Review Offers
             </a>
             <?php if ($userId): ?>
                <a href="../Controller/profileController.php?action=edit&type=pilote">
                    <i class="fa-solid fa-user-pen"></i> Edit My Profile
                </a>
             <?php endif; ?>
             <!-- Add more relevant action links -->
        </div>

    </main>

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
