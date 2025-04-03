<?php
// Location: /home/demy/project-dev-web/src/View/student.php

// --- Authentication Check ---
require_once __DIR__ . '/../Auth/AuthCheck.php';
AuthCheck::checkUserAuth('student', 'login.php');
// --- End Authentication Check ---

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Student');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? 'N/A');
$userId = $_SESSION['user_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <style> /* Basic styles */
        body { font-family: sans-serif; margin: 0; padding: 0; background-color: #f8f9fa; }
        header { background-color: #17a2b8; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 1.5em; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; }
        nav ul li { margin-left: 15px; }
        nav ul li a { color: #f8f9fa; text-decoration: none; padding: 5px 10px; }
        nav ul li a:hover, nav ul li a.active { color: #ffffff; background-color: #138496; border-radius: 4px; }
        main { padding: 20px; }
        footer { text-align: center; margin-top: 30px; padding: 15px; background-color: #e9ecef; color: #6c757d; font-size: 0.9em; border-top: 1px solid #dee2e6;}
        .user-info { background-color: #ffffff; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .dashboard-actions a { display: inline-block; margin: 5px; padding: 10px 15px; background-color: #17a2b8; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.2s ease; }
        .dashboard-actions a:hover { background-color: #117a8b; }
        .dashboard-actions a i { margin-right: 5px; }
    </style>
</head>
<body>
     <header>
        <h1><i class="fa-solid fa-user-graduate"></i> Student Dashboard</h1>
        <nav>
            <ul>
                <li><a href="student.php" class="active">Dashboard</a></li>
                <?php if ($userId): ?>
                    <li><a href="../Controller/profileController.php?action=edit&type=student">My Profile</a></li> <!-- Adjust -->
                <?php endif; ?>
                <li><a href="../Controller/offerController.php?action=view">View Offers</a></li> <!-- Adjust -->
                <li><a href="../Controller/applicationController.php?action=myapps">My Applications</a></li> <!-- Adjust -->
                <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Welcome, <?= $userName ?>!</h2>
        <div class="user-info">
            <p><strong>Email:</strong> <?= $userEmail ?></p>
            <p><strong>Role:</strong> Student</p>
        </div>
        <p>View internship offers, manage your applications and profile.</p>
         <div class="dashboard-actions">
             <a href="../Controller/offerController.php?action=view"><i class="fa-solid fa-list"></i> View Offers</a>
             <a href="../Controller/applicationController.php?action=myapps"><i class="fa-solid fa-folder-open"></i> My Applications</a>
              <?php if ($userId): ?>
                <a href="../Controller/profileController.php?action=edit&type=student"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
              <?php endif; ?>
         </div>
    </main>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
