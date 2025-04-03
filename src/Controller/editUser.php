<?php
// Location: /home/demy/project-dev-web/src/Controller/editUser.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php';
require_once __DIR__ . '/../Auth/AuthSession.php';
require_once __DIR__ . '/../Auth/AuthCheck.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth Check ---
if (!AuthSession::isUserLoggedIn()) {
    header("Location: ../View/login.php?error=" . urlencode("Authentication required."));
    exit();
}

// --- Get Logged-in User Info ---
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Instantiate Model ---
try {
    $userModel = new User($conn);
} catch (Exception $e) {
    error_log("EditUser: Failed to instantiate User model: " . $e->getMessage());
    die("A critical error occurred during user edit setup.");
}

// --- Check if target user ID and type are provided ---
if (!isset($_GET['id']) || !isset($_GET['type']) || empty($_GET['id']) || empty($_GET['type'])) {
    header("Location: userController.php?error=missing_params");
    exit();
}

$targetUserId = (int) $_GET['id'];
$targetUserType = $_GET['type']; // e.g., 'student', 'pilote', 'admin'
$allowedTypes = ['student', 'pilote', 'admin'];
if (!in_array($targetUserType, $allowedTypes)) {
     header("Location: userController.php?error=invalid_type"); exit();
}


// --- Fetch Target User Details ---
$userDetails = null;
switch($targetUserType) {
    case 'student': $userDetails = $userModel->readStudent($targetUserId); break;
    case 'pilote':  $userDetails = $userModel->readPilote($targetUserId); break;
    case 'admin':   $userDetails = $userModel->readAdmin($targetUserId); break;
}

if (!$userDetails) {
    header("Location: userController.php?error=not_found"); exit();
}

// --- AUTHORIZATION CHECK ---
$canEdit = false;
$isSelfEdit = false; // Flag to check if it's a self-edit

if ($loggedInUserRole === 'admin') {
    $canEdit = true;
    // Check if admin is editing themselves
    if ($targetUserType === 'admin' && $targetUserId == $loggedInUserId) {
        $isSelfEdit = true;
    }
} elseif ($loggedInUserRole === 'pilote') {
    // Pilotes can edit students they created
    if ($targetUserType === 'student' && isset($userDetails['created_by_pilote_id']) && $userDetails['created_by_pilote_id'] == $loggedInUserId) {
        $canEdit = true;
    }
    // Pilotes can edit themselves
    elseif ($targetUserType === 'pilote' && $targetUserId == $loggedInUserId) {
        $canEdit = true;
        $isSelfEdit = true;
    }
} elseif ($loggedInUserRole === 'student') {
     // Students can only edit themselves
     if ($targetUserType === 'student' && $targetUserId == $loggedInUserId) {
         $canEdit = true;
         $isSelfEdit = true;
     }
}

// If not authorized, redirect
if (!$canEdit) {
     // Redirect based on role, maybe? Or always to userController?
     // Redirecting to dashboard seems safer if they somehow got here illegitimately
     $dashboard = ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') ? "../View/{$loggedInUserRole}.php" : '../View/login.php';
     header("Location: " . $dashboard . "?error=auth_edit_failed");
     exit();
}


// --- Handle Form Submission (POST) ---
$errorMessage = '';
$successMessage = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {

    // Re-check authorization just in case
     if (!$canEdit) {
        $errorMessage = "Authorization check failed during update.";
    } else {
        // Collect data from POST
        $updateData = [
            'name' => $_POST['name'] ?? null,
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['password'] ?? null, // Optional new password
        ];
        if ($targetUserType !== 'admin') {
            $updateData['location'] = $_POST['location'] ?? null;
            $updateData['phone_number'] = $_POST['phone'] ?? null;
        }
        if ($targetUserType === 'student') {
            $updateData['year'] = $_POST['year'] ?? null;
            $updateData['description'] = $_POST['description'] ?? null;
            $updateData['date_of_birth'] = $_POST['dob'] ?? null;
        }

        // Basic Validation
        if (empty($updateData['name']) || empty($updateData['email'])) {
            $errorMessage = "Error: Name and email are required.";
        } elseif ($targetUserType === 'student' && (empty($updateData['year']) || empty($updateData['date_of_birth'])) ) {
             $errorMessage = "Error: Year and Date of Birth are required for students.";
        } else {
            // Call appropriate update method
            $result = false;
            switch($targetUserType) {
                case 'student':
                    $result = $userModel->updateStudent( $targetUserId, $updateData['name'], $updateData['email'], $updateData['location'], $updateData['phone_number'], $updateData['date_of_birth'], $updateData['year'], $updateData['description'], $updateData['password'] );
                    break;
                case 'pilote':
                    $result = $userModel->updatePilote( $targetUserId, $updateData['name'], $updateData['email'], $updateData['location'], $updateData['phone_number'], $updateData['password'] );
                    break;
                case 'admin':
                    $result = $userModel->updateAdmin( $targetUserId, $updateData['name'], $updateData['email'], $updateData['password'] );
                    break;
            }

            if ($result) {
                // ***** CONDITIONAL REDIRECT *****
                if ($isSelfEdit) {
                    // Redirect back to the user's own dashboard after self-edit
                    $dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php';
                     // Add check for student role if they can self-edit
                     if ($loggedInUserRole === 'student') $dashboardUrl = '../View/student.php';

                    header("Location: " . $dashboardUrl . "?profile_update=success");
                    exit();
                } else {
                    // Redirect back to the user list if an admin edited someone else
                    header("Location: userController.php?update=success");
                    exit();
                }
                // ***** END CONDITIONAL REDIRECT *****

            } else {
                $errorMessage = $userModel->getError() ?: "Error updating user.";
            }
        }
    }
    // If update failed, we fall through to redisplay the form with the error message
}

// --- Prepare for View ---
$pageTitle = "Edit " . ucfirst($targetUserType);
// Pass $isSelfEdit to the view if you need different titles/text, e.g., "Edit My Profile" vs "Edit User"
// $pageTitle = $isSelfEdit ? "Edit My Profile" : "Edit " . ucfirst($targetUserType);


// --- Include the View ---
// The view file itself ('editUserView.php') does not need changes for this logic.
include __DIR__ . '/../View/editUserView.php';

?>
