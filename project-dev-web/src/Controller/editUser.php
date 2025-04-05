<?php
// Location: /home/demy/project-dev-web/src/Controller/editUser.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php';
require_once __DIR__ . '/../Auth/AuthSession.php';
// AuthCheck might be less critical now due to specific role checks below
// require_once __DIR__ . '/../Auth/AuthCheck.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth Check & User Info ---
if (!AuthSession::isUserLoggedIn()) { header("Location: ../View/login.php?error=" . urlencode("Authentication required.")); exit(); }
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Instantiate Model ---
try { $userModel = new User($conn); }
catch (InvalidArgumentException $e) { error_log("FATAL editUser: Invalid DB connection: " . $e->getMessage()); die("DB config error."); }
catch (Exception $e) { error_log("FATAL editUser: Model instantiate error: " . $e->getMessage()); die("Critical error."); }

// --- Get Target User ID & Type ---
if (!isset($_GET['id']) || !isset($_GET['type']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT) || empty($_GET['type'])) { header("Location: userController.php?error=invalid_request_params"); exit(); }
$targetUserId = (int) $_GET['id'];
$targetUserType = $_GET['type'];
$allowedTypes = ['student', 'pilote', 'admin'];
if (!in_array($targetUserType, $allowedTypes)) { header("Location: userController.php?error=invalid_user_type"); exit(); }

// --- Fetch Target User Details (Crucial for preserving data in restricted edits) ---
$userDetails = null;
try {
    switch($targetUserType) {
        case 'student': $userDetails = $userModel->readStudent($targetUserId); break;
        case 'pilote':  $userDetails = $userModel->readPilote($targetUserId); break;
        case 'admin':   $userDetails = $userModel->readAdmin($targetUserId); break;
    }
} catch (Exception $e) { error_log("Error fetching target user details editUser (ID: {$targetUserId}, Type: {$targetUserType}): " . $e->getMessage()); header("Location: userController.php?error=fetch_failed"); exit(); }
if (!$userDetails) { header("Location: userController.php?error=user_not_found"); exit(); }

// --- Authorization Check & $isSelfEdit Flag ---
$canEdit = false; $isSelfEdit = false;
// Admin can edit anyone, self-edit flag set for own profile
if ($loggedInUserRole === 'admin') {
    $canEdit = true;
    if ($targetUserType === 'admin' && $targetUserId == $loggedInUserId) {
        $isSelfEdit = true;
    }
// Pilote can edit students they created, or their own profile
} elseif ($loggedInUserRole === 'pilote') {
    if ($targetUserType === 'student' && isset($userDetails['created_by_pilote_id']) && $userDetails['created_by_pilote_id'] == $loggedInUserId) {
        $canEdit = true;
    } elseif ($targetUserType === 'pilote' && $targetUserId == $loggedInUserId) {
        $canEdit = true;
        $isSelfEdit = true;
    }
// Student can ONLY edit their own profile
} elseif ($loggedInUserRole === 'student') {
    if ($targetUserType === 'student' && $targetUserId == $loggedInUserId) {
        $canEdit = true;
        $isSelfEdit = true;
    }
}
// Redirect if no permission
if (!$canEdit) {
    $dashboard = ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote' || $loggedInUserRole === 'student') ? "../View/{$loggedInUserRole}.php" : '../View/login.php';
    header("Location: " . $dashboard . "?error=" . urlencode("Permission denied to edit profile."));
    exit();
}

// --- Handle Form Submission (POST request to update user) ---
$errorMessage = ''; $successMessage = ''; $result = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {

    // Double-check authorization within POST context (belt and suspenders)
    if (!$canEdit) {
        $errorMessage = "Authorization check failed during update submission.";
    } else {
        // --- Initialize picture variables ---
        $profilePictureData = null;
        $profilePictureMime = null;
        $removeProfilePicture = false;

        // --- Process picture upload/removal ONLY if it's a self-edit ---
        if ($isSelfEdit) {
            $removeProfilePicture = isset($_POST['remove_profile_pic']) && $_POST['remove_profile_pic'] == '1';

            if (!$removeProfilePicture && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
                // File validation logic (Keep existing - check size, type, read)
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
        } // End if ($isSelfEdit) for picture processing

        // --- Process other form data (only if no critical file error occurred) ---
        if (empty($errorMessage)) {
            $updateData = [];

            // ******* SERVER-SIDE RESTRICTION LOGIC *******
            $isStudentSelfEditing = ($isSelfEdit && $loggedInUserRole === 'student');

            // --- Get New Password (relevant for all allowed editors) ---
            // Trim to handle empty spaces, treat empty string as "no change"
            $newPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
            if ($newPassword === '') {
                $newPassword = null; // Use null to indicate no password change to the model
            }

            // --- Assemble Data Based on Role ---
            if ($isStudentSelfEditing) {
                // Student editing self: ONLY Password (if provided) is from POST. Others from original $userDetails.
                 $updateData = [
                     'password'      => $newPassword, // New password or null
                     // Use existing data for restricted fields
                     'name'          => $userDetails['name'],
                     'email'         => $userDetails['email'],
                     'location'      => $userDetails['location'] ?? null,
                     'phone_number'  => $userDetails['phone_number'] ?? null,
                     'date_of_birth' => $userDetails['date_of_birth'],
                     'year'          => $userDetails['year'],
                     'school'        => $userDetails['school'] ?? null, // School is never editable by student
                     'description'   => $userDetails['description'] ?? null,
                 ];

            } else { // Admin/Pilote editing, or non-student self-edit
                 $updateData = [
                    'name'      => $_POST['name'] ?? $userDetails['name'], // Fallback to existing if somehow empty
                    'email'     => $_POST['email'] ?? $userDetails['email'],
                    'password'  => $newPassword, // New password or null
                ];

                if ($targetUserType !== 'admin') { // Common fields for Pilote/Student
                    $updateData['location'] = $_POST['location'] ?? ($userDetails['location'] ?? null);
                    $updateData['phone_number'] = $_POST['phone'] ?? ($userDetails['phone_number'] ?? null);
                }

                if ($targetUserType === 'student') { // Student specific fields
                    $updateData['year'] = $_POST['year'] ?? ($userDetails['year'] ?? null);
                    $updateData['description'] = $_POST['description'] ?? ($userDetails['description'] ?? null);
                    $updateData['date_of_birth'] = $_POST['dob'] ?? ($userDetails['date_of_birth'] ?? null);

                    // School: Only Admin/Pilote can change it when editing a student
                     if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') {
                         $updateData['school'] = $_POST['school'] ?? ($userDetails['school'] ?? null);
                     } else {
                         $updateData['school'] = $userDetails['school'] ?? null; // Keep existing (safety net)
                     }
                }
            }
            // ******* END SERVER-SIDE DATA ASSEMBLY *******


            // --- Validation ---
            // Basic validation needed regardless of who edits
            if (empty($updateData['name']) || empty($updateData['email'])) {
                $errorMessage = "Name and email required.";
            } elseif (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
            }
            // Student specific field validation (only if target is student)
            elseif ($targetUserType === 'student' && !$isStudentSelfEditing) { // Don't re-validate if student self-editing (fields are readonly)
                 if (empty($updateData['year']) || empty($updateData['date_of_birth'])) {
                      $errorMessage = "Year and DOB required for students.";
                 }
            }
            // Password complexity validation (ONLY if a new password was provided)
            if (empty($errorMessage) && $updateData['password'] !== null) {
                 if (strlen($updateData['password']) < 8 || !preg_match('/[A-Z]/', $updateData['password']) || !preg_match('/[0-9]/', $updateData['password'])) {
                      $errorMessage = "New password does not meet complexity requirements (min 8 chars, 1 uppercase, 1 number).";
                      // Do NOT update password if weak
                      $updateData['password'] = null;
                 }
            }


            // --- Proceed only if validation passed ---
            if (empty($errorMessage)) {
                $result = false;
                try {
                    // Call the appropriate model update method with assembled $updateData
                    switch($targetUserType) {
                        case 'student':
                            $result = $userModel->updateStudent(
                                $targetUserId,
                                $updateData['name'],
                                $updateData['email'],
                                $updateData['location'] ?? null,
                                $updateData['phone_number'] ?? null,
                                $updateData['date_of_birth'] ?? null,
                                $updateData['year'] ?? null,
                                $updateData['description'] ?? null,
                                $updateData['school'] ?? null,
                                $updateData['password'], // New password (or null if not changed/invalid)
                                $profilePictureData,    // New picture data (or null)
                                $profilePictureMime,    // New picture mime (or null)
                                $removeProfilePicture   // Flag to remove picture
                            );
                            break;
                        case 'pilote':
                            $result = $userModel->updatePilote(
                                $targetUserId,
                                $updateData['name'],
                                $updateData['email'],
                                $updateData['location'] ?? null,
                                $updateData['phone_number'] ?? null,
                                $updateData['password'],
                                $profilePictureData,
                                $profilePictureMime,
                                $removeProfilePicture
                            );
                            break;
                        case 'admin':
                            $result = $userModel->updateAdmin(
                                $targetUserId,
                                $updateData['name'],
                                $updateData['email'],
                                $updateData['password'],
                                $profilePictureData,
                                $profilePictureMime,
                                $removeProfilePicture
                            );
                            break;
                    }
                } catch (Exception $e) {
                    error_log("Exception during user update (ID: {$targetUserId}): " . $e->getMessage());
                    $errorMessage = $userModel->getError() ?: "Unexpected error during update.";
                    $result = false;
                }

                // --- Handle Update Result ---
                if ($result) {
                    // Redirect logic (Keep existing)
                    if ($isSelfEdit) {
                        $dashboardUrl = '';
                        if ($loggedInUserRole === 'admin') $dashboardUrl = '../View/admin.php';
                        elseif ($loggedInUserRole === 'pilote') $dashboardUrl = '../View/pilote.php';
                        elseif ($loggedInUserRole === 'student') $dashboardUrl = '../View/student.php';
                        if ($dashboardUrl) { header("Location: " . $dashboardUrl . "?profile_update=success"); exit(); }
                        else { error_log("editUser: Unknown role '{$loggedInUserRole}' self-edit redirect."); header("Location: userController.php?update=success"); exit(); }
                    } else { header("Location: userController.php?update=success"); exit(); }
                } elseif (empty($errorMessage)) {
                    // If $result is false but no specific error was set, get error from model
                    $errorMessage = $userModel->getError() ?: "Failed to update user profile.";
                }
            }
        } // End if no upload error
    } // End if authorized for POST
} // End if POST request

// --- Prepare Data for View Display ---
// Re-fetch details only if POST failed AND we want to show the *database* state,
// not the user's failed attempt. Might be better to keep $userDetails as is
// to show the user what they submitted that failed (except password).
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($result === false || $result === null) && !empty($errorMessage)) {
    error_log("Update failed for user {$targetUserId}. Displaying potentially stale data or re-fetching.");
    // Decide if re-fetching is desired or if showing submitted (invalid) data is better
    // For simplicity, we'll keep $userDetails as it was before the include.
    // If re-fetch is needed:
    /*
    $originalUserDetails = $userDetails;
    try {
        $freshUserDetails = null;
        switch($targetUserType) {
            case 'student': $freshUserDetails = $userModel->readStudent($targetUserId); break;
            case 'pilote':  $freshUserDetails = $userModel->readPilote($targetUserId); break;
            case 'admin':   $freshUserDetails = $userModel->readAdmin($targetUserId); break;
        }
        if ($freshUserDetails) { $userDetails = $freshUserDetails; }
        else { $userDetails = $originalUserDetails; error_log("Re-fetch failed for user {$targetUserId} after update error.");}
    } catch (Exception $e) {
        error_log("Exception re-fetching user details after update error (ID: {$targetUserId}): " . $e->getMessage());
        $userDetails = $originalUserDetails;
     }
     */
}

$pageTitle = $isSelfEdit ? "Edit My Profile" : "Edit " . ucfirst($targetUserType);

// --- Include the View ---
// Pass all necessary variables including $isSelfEdit, $loggedInUserRole etc.
include __DIR__ . '/../View/editUserView.php';
?>