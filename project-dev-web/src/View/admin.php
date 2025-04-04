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


// --- Message Handling ---
$messages = [];

// Handle profile update message
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $messages[] = [
        'type' => 'success',
        'text' => 'Your profile has been updated successfully.'
    ];
}

// Handle fresh login message
if (isset($_SESSION['just_logged_in']) && $_SESSION['just_logged_in'] === true) {
    $messages[] = [
        'type' => 'success',
        'text' => 'You have successfully logged in as an Administrator.'
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
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../View/js/darkMode.js"></script>
    <style>
        /* Additional styles specific to dashboard */
        .user-info {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            display: flex;
            gap: 20px;
        }
        
        .profile-section {
            flex-shrink: 0;
        }
        
        .info-section {
            flex-grow: 1;
        }
        
        .user-info .profile-pic-large {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-color);
        }
        
        .user-info h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary-navy);
        }
        
        .user-info p {
            margin-bottom: 8px;
            color: var(--text-dark);
            display: flex;
            gap: 8px;
        }
        
        .user-info strong {
            color: var(--primary-navy);
            min-width: 100px;
            display: inline-block;
        }
        
        .message {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .success-message {
            background-color: #e6f4ea;
            color: #1e7e34;
            border: 1px solid #c3e6cb;
        }
        
        .success-message i {
            color: #28a745;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>
            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="PFP" class="profile-pic-header">
            <span><?= htmlspecialchars($displayName) ?></span>
        </h1>
        <nav>
            <ul class="main-menu">
                <li><a href="../View/admin.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="../Controller/userController.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../Controller/companyController.php"><i class="fa-solid fa-building"></i> Manage Companies</a></li>
                <li><a href="../Controller/offerController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a></li>
                <?php if ($loggedInUserId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin"><i class="fa-solid fa-user-pen"></i> My Profile</a></li>
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
                    <p><strong>Role:</strong> Administrator</p>
                    <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
                </div>
            </div>

            <p class="dashboard-welcome">Use the navigation in the sidebar to manage all application data.</p>

            <footer>
                <p>© <?= date('Y'); ?> Project Dev Web Application</p>
            </footer>
        </div>
    </div>
</body>
</html>
