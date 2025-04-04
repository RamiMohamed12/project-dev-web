<?php
// Location: /home/demy/project-dev-web/src/View/student.php

// --- Required Includes & Session ---
require_once __DIR__ . '/../../config/config.php'; // Need $conn
require_once __DIR__ . '/../Auth/AuthCheck.php';
require_once __DIR__ . '/../Auth/AuthSession.php';
require_once __DIR__ . '/../Model/user.php';      // To fetch user details

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Authentication Check ---
AuthCheck::checkUserAuth('student', 'login.php');
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
$dbUserSchool = null; // Variable for school
$defaultPic = '../View/images/default_avatar.png'; // ** ADJUST PATH AS NEEDED **

if ($loggedInUserId && isset($conn)) {
    try {
        $userModel = new User($conn);
        $userDetails = $userModel->readStudent($loggedInUserId); // Fetch this student's details

        if ($userDetails) {
            $dbUserName = $userDetails['name'];
            $dbUserEmail = $userDetails['email'];
            $dbUserSchool = $userDetails['school']; // Get school
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                 $picData = is_resource($userDetails['profile_picture']) ? stream_get_contents($userDetails['profile_picture']) : $userDetails['profile_picture'];
                 if ($picData) {
                     $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                 }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching student details for dashboard (ID: $loggedInUserId): " . $e->getMessage());
    }
}
$displayName = htmlspecialchars($dbUserName ?? $sessionUserName ?? 'Student');
$displayEmail = htmlspecialchars($dbUserEmail ?? $sessionUserEmail ?? 'N/A');
$displaySchool = htmlspecialchars($dbUserSchool ?? 'N/A'); // Display school


// --- Message Handling ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}
if (isset($_GET['success'])) {
    $successMessage = htmlspecialchars($_GET['success']);
}
$errorMessage = '';
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../View/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-theme">
    <header>
        <h1>
             <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="PFP" class="profile-pic-header">
             <i class="fa-solid fa-user-graduate"></i> Student Dashboard
        </h1>
        <nav>
            <ul>
                <li><a href="student.php" class="active">Dashboard</a></li>
                <li><a href="../Controller/offerController.php?action=view">View Offers</a></li>
                <li><a href="../Controller/wishlistController.php?action=view">My Wishlist</a></li>
                <li><a href="../Controller/applicationController.php?action=myapps">My Applications</a></li>
                <?php if ($loggedInUserId): ?>
                <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student">My Profile</a></li>
                <?php endif; ?>
                <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Display Success Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div class="user-info">
             <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-large">
             <h2>Welcome, <?= $displayName ?>!</h2>
            <p><strong>Email:</strong> <?= $displayEmail ?></p>
            <p><strong>School:</strong> <?= $displaySchool ?></p> <!-- Display School -->
            <p><strong>Role:</strong> Student</p>
            <p><strong>Logged in since:</strong> <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
            <!-- You could add Year, Location etc. from $userDetails if needed -->
            <?php if(!empty($userDetails['year'])): ?>
                 <p><strong>Year:</strong> <?= htmlspecialchars($userDetails['year']) ?> Year</p>
            <?php endif; ?>
        </div>

        <p class="dashboard-intro">Access your profile, view available internship offers, and manage your applications.</p>

         <div class="dashboard-actions">
             <a href="../Controller/offerController.php?action=view">
                 <i class="fa-solid fa-list"></i> View Offers
             </a>
             <a href="../Controller/wishlistController.php?action=view">
                 <i class="fa-solid fa-heart"></i> My Wishlist
             </a>
             <a href="../Controller/applicationController.php?action=myapps">
                 <i class="fa-solid fa-folder-open"></i> My Applications
             </a>
              <?php if ($loggedInUserId): ?>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student">
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
