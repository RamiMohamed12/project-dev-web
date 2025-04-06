<?php
// Location: src/Controller/applicationController.php

// Required includes
require_once __DIR__ . '/../../config/config.php'; // Ensures $conn is available
require_once __DIR__ . '/../Model/Application.php';
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/user.php'; // For profile picture logic
require_once __DIR__ . '/../Auth/AuthSession.php';
require_once __DIR__ . '/../Auth/AuthCheck.php'; // For checking roles

// Start session if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Basic Auth Check & Role/ID
if (!AuthSession::isUserLoggedIn()) {
    header("Location: ../View/login.php?error=" . urlencode("Authentication required."));
    exit();
}

$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// Check if $conn is set from config.php
if (!isset($conn) || !$conn) {
    error_log("Database connection failed in applicationController.php");
    die("A critical database connection error occurred. Please contact support.");
}

// Instantiate Models
$applicationModel = new Application($conn);
$internshipModel = new Internship($conn);
$userModel = new User($conn); // For profile picture logic

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'Internship Applications'; // General title

// Get action from GET or POST - default depends on role now
$defaultAction = ($loggedInUserRole === 'student') ? 'myapps' : 'manage'; // Default action based on role
$action = $_GET['action'] ?? ($_POST['action'] ?? $defaultAction);

// --- Authorization Check based on Action ---
$allowedStudentActions = ['apply', 'submit', 'myapps'];
$allowedManagementActions = ['manage', 'updateStatus', 'downloadCv'];

if ($loggedInUserRole === 'student' && !in_array($action, $allowedStudentActions)) {
    $_SESSION['error_message'] = "Access Denied.";
    header("Location: ../Controller/offerController.php?action=view"); // Redirect student to offers
    exit();
} elseif (($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') && !in_array($action, $allowedManagementActions)) {
     $_SESSION['error_message'] = "Invalid action requested for your role.";
     AuthCheck::redirectToRoleDashboard($loggedInUserRole); // Use helper to redirect
     exit();
} elseif (!in_array($loggedInUserRole, ['student', 'admin', 'pilote'])) {
     $_SESSION['error_message'] = "Invalid user role.";
     header("Location: ../View/login.php");
     exit();
}
// --- End Authorization Check ---


// Handle messages from session/redirects
if (isset($_SESSION['success_message'])) {
    $successMessage = htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}

// *** FIX: Define allowed statuses based on the database ENUM ***
$allowed_statuses = ['pending', 'accepted', 'rejected'];


try {
    switch ($action) {
        // ====================================
        // == Student Actions ('apply', 'submit', 'myapps') ==
        // ====================================
        case 'apply':
        case 'submit':
            // Double check student role and ID
            if ($loggedInUserRole !== 'student') { AuthCheck::redirectToRoleDashboard($loggedInUserRole); exit(); }
            if (!$loggedInUserId) { AuthCheck::redirectToLogin("Session error."); exit(); }

            if ($action === 'apply') {
                $pageTitle = 'Apply for Internship';
                // Apply Logic... (get ID, check if applied, get details, include form view)
                 if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) { $_SESSION['error_message'] = "Invalid internship ID."; header("Location: ../Controller/offerController.php?action=view"); exit(); }
                 $internshipId = (int)$_GET['id'];
                 if ($applicationModel->hasApplied($loggedInUserId, $internshipId)) { $_SESSION['error_message'] = "Already applied."; header("Location: ../Controller/offerController.php?action=view"); exit(); }
                 $internshipDetails = $internshipModel->readInternship($internshipId);
                 if (!$internshipDetails) { $_SESSION['error_message'] = "Internship not found."; header("Location: ../Controller/offerController.php?action=view"); exit(); }
                 // Pass $conn to the view for profile pic fetch
                 include __DIR__ . '/../View/applicationFormView.php';

            } else { // submit action
                $pageTitle = 'Submit Application';
                 // Submit Logic... (check POST, validate, handle CV, call createApplication, redirect)
                 if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../Controller/offerController.php?action=view"); exit(); }
                 $internshipId = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
                 $motivationLetter = trim($_POST['motivation_letter'] ?? '');
                 if (!$internshipId) { $_SESSION['error_message'] = "Submission failed: Invalid internship ref."; header("Location: ../Controller/offerController.php?action=view"); exit(); }
                 // Fetch details again for redisplay on error AND before create call
                 $internshipDetails = $internshipModel->readInternship($internshipId);
                 if (!$internshipDetails) { $_SESSION['error_message'] = "Submission failed: Internship not found."; header("Location: ../Controller/offerController.php?action=view"); exit(); }
                 if (empty($motivationLetter)) { $errorMessage = "Motivation letter required."; include __DIR__.'/../View/applicationFormView.php'; exit(); } // Redisplay form
                 if ($applicationModel->hasApplied($loggedInUserId, $internshipId)) { $_SESSION['error_message'] = "Already applied."; header("Location: ../Controller/offerController.php?action=view"); exit(); }

                 // CV Upload Handling
                 $cvFilePathForDb = null;
                 if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                     $uploadDir = __DIR__ . '/../../uploads/cv/'; $allowedExts = ['pdf', 'doc', 'docx']; $maxFileSize = 5 * 1024 * 1024;
                     if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) { $errorMessage="Server error: Cannot create upload dir."; include __DIR__.'/../View/applicationFormView.php'; exit();}
                     $cvOriginalName = basename($_FILES['cv_file']['name']); $cvFileExt = strtolower(pathinfo($cvOriginalName, PATHINFO_EXTENSION)); $cvFileSize = $_FILES['cv_file']['size'];
                     if (!in_array($cvFileExt, $allowedExts)) { $errorMessage = "Invalid CV file type."; include __DIR__.'/../View/applicationFormView.php'; exit(); }
                     if ($cvFileSize > $maxFileSize) { $errorMessage = "CV file size exceeds limit."; include __DIR__.'/../View/applicationFormView.php'; exit(); }
                     $safeBaseName = preg_replace("/[^a-zA-Z0-9._-]/", "_", pathinfo($cvOriginalName, PATHINFO_FILENAME));
                     $uniqueFileName = uniqid('cv_' . $loggedInUserId . '_', true) . '.' . $cvFileExt;
                     $absoluteCvPath = $uploadDir . $uniqueFileName; $cvFilePathForDb = 'uploads/cv/' . $uniqueFileName;
                     if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $absoluteCvPath)) { $errorMessage = "Failed to save uploaded CV."; error_log("Failed move to: ".$absoluteCvPath); include __DIR__.'/../View/applicationFormView.php'; exit(); }
                 }
                 // Create Application Call
                 $result = $applicationModel->createApplication($loggedInUserId, $internshipId, $motivationLetter, $cvFilePathForDb);
                 if ($result) { $_SESSION['success_message'] = "Application submitted!"; header("Location: ../Controller/applicationController.php?action=myapps"); exit(); }
                  else { $errorMessage = $applicationModel->getError() ?: "Failed to submit."; include __DIR__ . '/../View/applicationFormView.php'; exit(); } // Redisplay form
            }
            break;

        case 'myapps':
            // Double check student role and ID
            if ($loggedInUserRole !== 'student') { AuthCheck::redirectToRoleDashboard($loggedInUserRole); exit(); }
            if (!$loggedInUserId) { AuthCheck::redirectToLogin("Session error."); exit(); }

            $pageTitle = "My Applications";
            $applications = $applicationModel->getStudentApplications($loggedInUserId);

             if ($applications === false) {
                 $errorMessage = "Could not retrieve your applications due to a database error.";
                 $applications = [];
             }

            // Pass $conn for profile picture logic
            include __DIR__ . '/../View/myApplicationsView.php';
            break;

        // ====================================
        // == Admin/Pilote Actions ('manage', 'updateStatus', 'downloadCv') ==
        // ====================================
        case 'manage':
            // Double check admin/pilote role
            if (!in_array($loggedInUserRole, ['admin', 'pilote'])) { AuthCheck::redirectToLogin(); exit(); }

            $pageTitle = "Manage Student Applications";
            $piloteFilterId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null; // Filter for pilote, null for admin

            $applications = $applicationModel->getApplicationsForManagement($piloteFilterId);

             if ($applications === false) {
                 $errorMessage = "Could not retrieve applications for management."; // Simplified error
                 error_log("Error fetching apps for management (Pilote: $piloteFilterId): ".$applicationModel->getError());
                 $applications = []; // Ensure array for view
             }

            // Pass $conn and $userModel for header, $allowed_statuses for dropdown
            include __DIR__ . '/../View/manageApplicationsView.php';
            break;

        case 'updateStatus':
             // Double check admin/pilote role
             if (!in_array($loggedInUserRole, ['admin', 'pilote'])) { AuthCheck::redirectToLogin(); exit(); }
             if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../Controller/applicationController.php?action=manage"); exit(); }

            // Validate input
            $applicationId = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
            // Get the submitted status EXACTLY as sent (should be lowercase now)
            $newStatus = trim($_POST['new_status'] ?? '');

            if (!$applicationId || empty($newStatus)) {
                $_SESSION['error_message'] = "Invalid input for status update.";
                header("Location: ../Controller/applicationController.php?action=manage");
                exit();
            }

             // Authorization check for Pilote
             if ($loggedInUserRole === 'pilote') {
                $appDetails = $applicationModel->getApplicationById($applicationId);
                if (!$appDetails || $appDetails['created_by_pilote_id'] != $loggedInUserId) {
                     $_SESSION['error_message'] = "Unauthorized action."; // Simpler message
                     header("Location: ../Controller/applicationController.php?action=manage");
                     exit();
                }
            }

            // Attempt to update status (Model now handles validation against its internal list)
            if ($applicationModel->updateStatus($applicationId, $newStatus)) {
                // Use ucwords for display message
                $_SESSION['success_message'] = "Application #{$applicationId} status updated to '" . htmlspecialchars(ucwords($newStatus)) . "'.";
            } else {
                 // Use ucwords for display message if it was an invalid status error
                 $modelError = $applicationModel->getError();
                 if (strpos($modelError, 'Invalid status value') !== false) {
                     $_SESSION['error_message'] = $modelError; // Show the specific validation error
                 } else {
                      $_SESSION['error_message'] = "Failed to update status for application #{$applicationId}."; // Generic DB error
                 }
            }

            header("Location: ../Controller/applicationController.php?action=manage");
            exit();
            break;

        case 'downloadCv':
            // Double check admin/pilote role
            if (!in_array($loggedInUserRole, ['admin', 'pilote'])) { AuthCheck::redirectToLogin(); exit(); }

             $applicationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
             if (!$applicationId) { $_SESSION['error_message'] = "Invalid Application ID."; header("Location: ../Controller/applicationController.php?action=manage"); exit(); }

             $appDetails = $applicationModel->getApplicationById($applicationId);
             if (!$appDetails) { $_SESSION['error_message'] = "Application not found."; header("Location: ../Controller/applicationController.php?action=manage"); exit(); }

             // Authorization check for Pilote
             if ($loggedInUserRole === 'pilote' && $appDetails['created_by_pilote_id'] != $loggedInUserId) { $_SESSION['error_message'] = "Unauthorized action."; header("Location: ../Controller/applicationController.php?action=manage"); exit(); }

             // Check CV Path & Security
             $relativePath = $appDetails['cv'];
             if (empty($relativePath)) { $_SESSION['error_message'] = "No CV uploaded."; header("Location: ../Controller/applicationController.php?action=manage"); exit(); }

             $baseUploadPath = realpath(__DIR__ . '/../../');
             $expectedDir = realpath($baseUploadPath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv');
             $absolutePath = $baseUploadPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
             $realAbsolutePath = realpath($absolutePath); // Resolve path

              // Enhanced Security Check: ensure real path exists and is inside the expected directory
             if ($realAbsolutePath === false || strpos($realAbsolutePath, $expectedDir) !== 0 || !is_file($realAbsolutePath)) {
                 error_log("CV Download Security Fail/Not Found. AppID: $applicationId, AbsPath: $absolutePath, RealPath: " . ($realAbsolutePath ?: 'false') . ", ExpectedDir: $expectedDir");
                 $_SESSION['error_message'] = "CV file error or access denied.";
                 header("Location: ../Controller/applicationController.php?action=manage");
                 exit();
             }

             // Determine mime type & Download Filename
              $finfo = finfo_open(FILEINFO_MIME_TYPE); $mimeType = finfo_file($finfo, $realAbsolutePath) ?: 'application/octet-stream'; finfo_close($finfo);
              $studentId = $appDetails['id_student']; $internshipId = $appDetails['id_internship']; $extension = pathinfo($realAbsolutePath, PATHINFO_EXTENSION);
              $downloadFilename = "CV_Student{$studentId}_App{$applicationId}.{$extension}";

             // Send file
             header('Content-Description: File Transfer'); header('Content-Type: ' . $mimeType); header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
             header('Expires: 0'); header('Cache-Control: must-revalidate'); header('Pragma: public'); header('Content-Length: ' . filesize($realAbsolutePath));
             flush(); readfile($realAbsolutePath);
             exit();
            break;

        default:
            $_SESSION['error_message'] = "Unknown action requested.";
            AuthCheck::redirectToRoleDashboard($loggedInUserRole);
            exit();
    }
} catch (Exception $e) {
    // Catch any unexpected exceptions
    error_log("Unhandled Exception in applicationController (Action: $action, UserID: $loggedInUserId, Role: $loggedInUserRole): " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $_SESSION['error_message'] = "A critical system error occurred.";
    AuthCheck::redirectToRoleDashboard($loggedInUserRole);
    exit();
}
?>
