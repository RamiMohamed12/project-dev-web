<?php
// Location: /home/demy/project-dev-web/src/View/pilote.php

// Start session FIRST before any output or session access
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Required Includes & Session ---
require_once __DIR__ . '/../../config/config.php'; // Need $conn
require_once __DIR__ . '/../Auth/AuthSession.php'; // Must be loaded before AuthCheck
require_once __DIR__ . '/../Auth/AuthCheck.php';   // Depends on AuthSession
require_once __DIR__ . '/../Model/user.php';      // To fetch user details

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
$messages = [];

// Handle profile update message
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $messages[] = [
        'type' => 'success',
        'text' => 'Your profile has been updated successfully.'
    ];
}

// Handle fresh login message (using session to track)
if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    $messages[] = [
        'type' => 'success',
        'text' => 'You have successfully logged in as a Pilote.'
    ];
    // Clear the flag so message only shows once
    unset($_SESSION['just_logged_in']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilote Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../View/js/darkMode.js"></script>
    <style>
        /* Additional styles specific to dashboard */
        .user-info {
            background-color: var(--container-bg);
            border: 1px solid var(--border-color);
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: flex;
            gap: 20px;
        }
        
        .user-info .profile-section {
            flex-shrink: 0;
            text-align: center;
        }
        
        .user-info .profile-pic-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid var(--border-color);
        }
        
        .user-info .info-section {
            flex-grow: 1;
        }
        
        .user-info h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--theme-primary);
        }
        
        .user-info p {
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        
        .user-info strong {
            color: var(--theme-primary);
            display: inline-block;
            width: 120px;
        }
        
        .dashboard-welcome {
            font-size: 1.1em;
            color: var(--text-dark);
            margin: 2rem 0;
        }
        
        footer {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            color: var(--text-dark);
            font-size: 0.9em;
            border-top: 1px solid var(--border-color);
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .success-message {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .success-message i {
            color: #0f5132;
            font-size: 1.1rem;
        }

        /* Dashboard Actions */
        .dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }

        .dashboard-actions a {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background-color: var(--theme-primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .dashboard-actions a:hover {
            background-color: var(--theme-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .dashboard-actions a i {
            font-size: 1.1rem;
            color: white;
        }
    </style>
</head>
<body class="role-pilote">
    <div class="sidebar">
        <h1>
            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="PFP" class="profile-pic-header">
            <span><?= htmlspecialchars($displayName) ?></span>
        </h1>
        <nav>
            <ul class="main-menu">
                <li><a href="pilote.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="../Controller/userController.php"><i class="fa-solid fa-users"></i> Manage Students</a></li>
                <li><a href="../Controller/companyController.php"><i class="fa-solid fa-building"></i> Manage Companies</a></li>
                <li><a href="../Controller/offerController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a></li>
                <?php if ($loggedInUserId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=pilote"><i class="fa-solid fa-user-pen"></i> My Profile</a></li>
                <?php endif; ?>
            </ul>
            <ul class="bottom-menu">
                <li><a href="../Controller/logoutController.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <li>
                    <button id="theme-toggle" class="theme-switch">
                        <i class="fa-solid fa-sun sun-icon"></i>
                        <i class="fa-solid fa-moon moon-icon"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container">
            <!-- Display Messages -->
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['type'] ?>-message">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= htmlspecialchars($msg['text']) ?>
                </div>
            <?php endforeach; ?>

            <div class="user-info">
                <div class="profile-section">
                    <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-large">
                </div>
                <div class="info-section">
                    <h2>Welcome, <?= $displayName ?>!</h2>
                    <p><strong>Email:</strong> <?= $displayEmail ?></p>
                    <p><strong>Role:</strong> Pilote</p>
                    <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
                </div>
            </div>

            <p class="dashboard-welcome">Use the navigation in the sidebar to manage your students, companies, and internship offers.</p>

            <footer>
                <p>© <?= date('Y'); ?> Project Dev Web Application</p>
            </footer>
        </div>
    </div>
</body>
</html>
