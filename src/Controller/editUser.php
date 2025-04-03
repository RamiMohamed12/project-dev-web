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

// --- Basic Auth Check & User Info (Keep existing) ---
if (!AuthSession::isUserLoggedIn()) { header("Location: ../View/login.php?error=" . urlencode("Authentication required.")); exit(); }
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Instantiate Model (Keep existing) ---
try { $userModel = new User($conn); }
catch (InvalidArgumentException $e) { error_log("FATAL editUser: Invalid DB connection: " . $e->getMessage()); die("DB config error."); }
catch (Exception $e) { error_log("FATAL editUser: Model instantiate error: " . $e->getMessage()); die("Critical error."); }

// --- Get Target User ID & Type from URL (Keep existing) ---
if (!isset($_GET['id']) || !isset($_GET['type']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT) || empty($_GET['type'])) { header("Location: userController.php?error=invalid_request_params"); exit(); }
$targetUserId = (int) $_GET['id'];
$targetUserType = $_GET['type'];
$allowedTypes = ['student', 'pilote', 'admin'];
if (!in_array($targetUserType, $allowedTypes)) { header("Location: userController.php?error=invalid_user_type"); exit(); }

// --- Fetch Target User Details (Keep existing) ---
$userDetails = null;
try {
    switch($targetUserType) {
        case 'student': $userDetails = $userModel->readStudent($targetUserId); break;
        case 'pilote':  $userDetails = $userModel->readPilote($targetUserId); break;
        case 'admin':   $userDetails = $userModel->readAdmin($targetUserId); break;
    }
} catch (Exception $e) { error_log("Error fetching target user details editUser (ID: {$targetUserId}, Type: {$targetUserType}): " . $e->getMessage()); header("Location: userController.php?error=fetch_failed"); exit(); }
if (!$userDetails) { header("Location: userController.php?error=user_not_found"); exit(); }

// --- Authorization Check & $isSelfEdit Flag (Keep existing) ---
$canEdit = false; $isSelfEdit = false;
if ($loggedInUserRole === 'admin') { $canEdit = true; if ($targetUserType === 'admin' && $targetUserId == $loggedInUserId) { $isSelfEdit = true; } }
elseif ($loggedInUserRole === 'pilote') { if ($targetUserType === 'student' && isset($userDetails['created_by_pilote_id']) && $userDetails['created_by_pilote_id'] == $loggedInUserId) { $canEdit = true; } elseif ($targetUserType === 'pilote' && $targetUserId == $loggedInUserId) { $canEdit = true; $isSelfEdit = true; } }
elseif ($loggedInUserRole === 'student') { if ($targetUserType === 'student' && $targetUserId == $loggedInUserId) { $canEdit = true; $isSelfEdit = true; } }
if (!$canEdit) { $dashboard = ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote' || $loggedInUserRole === 'student') ? "../View/{$loggedInUserRole}.php" : '../View/login.php'; header("Location: " . $dashboard . "?error=" . urlencode("Permission denied to edit profile.")); exit(); }

// --- Handle Form Submission (POST request to update user) ---
$errorMessage = ''; $successMessage = ''; $result = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {

    if (!$canEdit) { $errorMessage = "Authorization check failed during update submission."; }
    else {
        // --- Initialize picture variables ---
        $profilePictureData = null;
        $profilePictureMime = null;
        $removeProfilePicture = false; // Default to false

        // ***** MODIFICATION START: Only process picture if it's a self-edit *****
        if ($isSelfEdit) {
            // Check if remove checkbox is checked
            $removeProfilePicture = isset($_POST['remove_profile_pic']) && $_POST['remove_profile_pic'] == '1';

            // Process file upload only if remove checkbox is NOT checked
            if (!$removeProfilePicture && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                $fileSize = $_FILES['profile_pic']['size'];
                $maxFileSize = 2 * 1024 * 1024; // 2 MB Limit
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'];

                if ($fileSize == 0) { $errorMessage = "Error: Uploaded file is empty."; }
                elseif ($fileSize > $maxFileSize) { $errorMessage = "Error: File is too large (Max 2MB)."; }
                else {
                    $fileMimeType = mime_content_type($fileTmpPath);
                    if ($fileMimeType && in_array($fileMimeType, $allowedMimeTypes)) {
                        $profilePictureData = file_get_contents($fileTmpPath);
                        if ($profilePictureData === false) { $errorMessage = "Error reading uploaded file."; $profilePictureData = null; }
                        else { $profilePictureMime = $fileMimeType; }
                    } else { $errorMessage = "Error: Invalid file type. Allowed: JPG, PNG, SVG, WebP."; }
                }
            } elseif (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['profile_pic']['error'] != UPLOAD_ERR_OK) {
                 $errorMessage = "Error uploading profile picture. Code: " . $_FILES['profile_pic']['error'];
            }
        } // ***** END MODIFICATION: Only process picture if it's a self-edit *****


        // --- Process other form data (only if no critical error occurred) ---
        if (empty($errorMessage)) {
            $updateData = [ /* Collect name, email, password etc. as before */
                'name' => $_POST['name'] ?? null, 'email' => $_POST['email'] ?? null, 'password' => $_POST['password'] ?? null,
            ];
            if ($targetUserType !== 'admin') { $updateData['location'] = $_POST['location'] ?? null; $updateData['phone_number'] = $_POST['phone'] ?? null; }
            if ($targetUserType === 'student') {
                $updateData['year'] = $_POST['year'] ?? null; $updateData['description'] = $_POST['description'] ?? null; $updateData['date_of_birth'] = $_POST['dob'] ?? null;
                // School field handling (only editable by admin/pilote)
                 if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') { $updateData['school'] = $_POST['school'] ?? null; }
                 else { $updateData['school'] = $userDetails['school'] ?? null; } // Keep existing if student edits self
            }

            // Validation...
            if (empty($updateData['name']) || empty($updateData['email'])) { $errorMessage = "Name and email required."; }
            elseif (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) { $errorMessage = "Invalid email format."; }
            elseif ($targetUserType === 'student' && (empty($updateData['year']) || empty($updateData['date_of_birth'])) ) { $errorMessage = "Year and DOB required for students."; }
            else {
                // --- Call Model Update Method ---
                // Note: $profilePictureData, $profilePictureMime, $removeProfilePicture will be null/false
                // if $isSelfEdit was false, correctly passing no picture changes to the model.
                $result = false;
                try {
                    switch($targetUserType) {
                        case 'student':
                            $result = $userModel->updateStudent( $targetUserId, $updateData['name'], $updateData['email'], $updateData['location'], $updateData['phone_number'], $updateData['date_of_birth'], $updateData['year'], $updateData['description'], $updateData['school'], $updateData['password'], $profilePictureData, $profilePictureMime, $removeProfilePicture );
                            break;
                        case 'pilote':
                            $result = $userModel->updatePilote( $targetUserId, $updateData['name'], $updateData['email'], $updateData['location'], $updateData['phone_number'], $updateData['password'], $profilePictureData, $profilePictureMime, $removeProfilePicture );
                            break;
                        case 'admin':
                            $result = $userModel->updateAdmin( $targetUserId, $updateData['name'], $updateData['email'], $updateData['password'], $profilePictureData, $profilePictureMime, $removeProfilePicture );
                            break;
                    }
                } catch (Exception $e) { error_log("Exception during user update (ID: {$targetUserId}): " . $e->getMessage()); $errorMessage = $userModel->getError() ?: "Unexpected error during update."; $result = false; }

                // --- Handle Update Result ---
                if ($result) {
                    // Conditional Redirect logic (remains the same)
                    if ($isSelfEdit) {
                        $dashboardUrl = '';
                        if ($loggedInUserRole === 'admin') $dashboardUrl = '../View/admin.php';
                        elseif ($loggedInUserRole === 'pilote') $dashboardUrl = '../View/pilote.php';
                        elseif ($loggedInUserRole === 'student') $dashboardUrl = '../View/student.php';
                        if ($dashboardUrl) { header("Location: " . $dashboardUrl . "?profile_update=success"); exit(); }
                        else { error_log("editUser: Unknown role '{$loggedInUserRole}' self-edit redirect."); header("Location: userController.php?update=success"); exit(); }
                    } else { header("Location: userController.php?update=success"); exit(); }
                } elseif (empty($errorMessage)) { $errorMessage = $userModel->getError() ?: "Failed to update user profile."; }
            }
        } // End if no upload error
    } // End if authorized for POST
} // End if POST request

// --- Prepare Data for View Display ---
// Re-fetch details if update failed, to show correct preview state
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($result === false || $result === null) && !empty($errorMessage)) {
    error_log("Update failed for user {$targetUserId}. Re-fetching details.");
    try { $freshUserDetails = null; /* ... re-fetch logic ... */ if ($freshUserDetails) { $userDetails = $freshUserDetails; } } catch (Exception $e) { /* ... log error ... */ }
}

$pageTitle = $isSelfEdit ? "Edit My Profile" : "Edit " . ucfirst($targetUserType);

// --- Include the View ---
include __DIR__ . '/../View/editUserView.php';
?>
