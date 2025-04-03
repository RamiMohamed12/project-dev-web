<?php
// Location: /home/demy/project-dev-web/src/View/admin.php

// --- Authentication Check ---
require_once __DIR__ . '/../Auth/AuthCheck.php';
AuthCheck::checkUserAuth('admin', 'login.php');
// --- End Authentication Check ---

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin');
$userEmail = htmlspecialchars($_SESSION['user_email'] ?? 'N/A');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css">
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
    </style>
</head>
<body>
    <header>
        <h1><i class="fa-solid fa-user-shield"></i> Admin Panel</h1>
        <nav>
            <ul>
                 <li><a href="admin.php" class="active">Dashboard</a></li>
                 <!-- Adjust links based on your user management controllers/views -->
                 <li><a href="../Controller/manageUserController.php?type=admin">Manage Admins</a></li>
                 <li><a href="../Controller/manageUserController.php?type=pilote">Manage Pilotes</a></li>
                 <li><a href="../Controller/manageUserController.php?type=student">Manage Students</a></li>
                 <li><a href="../Controller/companyController.php">Manage Companies</a></li>
                 <li><a href="../Controller/offerController.php">Manage Offers</a></li>
                 <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Welcome, <?= $userName ?>!</h2>
        <div class="user-info">
            <p><strong>Email:</strong> <?= $userEmail ?></p>
            <p><strong>Role:</strong> Administrator</p>
            <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', $_SESSION['logged_in_time'] ?? time()) ?></p>
        </div>
        <p>Use the navigation to manage application data.</p>
        <div class="dashboard-actions">
             <a href="../Controller/manageUserController.php?type=admin"><i class="fa-solid fa-user-gear"></i> Manage Admins</a>
             <a href="../Controller/manageUserController.php?type=pilote"><i class="fa-solid fa-user-tie"></i> Manage Pilotes</a>
             <a href="../Controller/manageUserController.php?type=student"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
             <a href="../Controller/companyController.php"><i class="fa-solid fa-building"></i> Manage Companies</a>
             <a href="../Controller/offerController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a>
        </div>
    </main>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
