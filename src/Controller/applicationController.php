<?php
// Location: src/Controller/applicationController.php

// Required includes
require_once __DIR__ . '/../../config/config.php'; // Ensures $conn is available
require_once __DIR__ . '/../Model/Application.php';
require_once __DIR__ . '/../Model/Internship.php';
// No need to include Company model here unless needed for other actions
require_once __DIR__ . '/../Auth/AuthSession.php';

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
    // Log critical error and stop
    error_log("Database connection failed in applicationController.php");
    die("A critical database connection error occurred. Please contact support.");
}

// Authorization: Only Students can generally interact here (apply, view own apps)
// Pilote/Admin might view all apps via a different controller or action later.
if ($loggedInUserRole !== 'student') {
    // Redirect non-students appropriately
    header("Location: ../View/login.php?error=" . urlencode("Access Denied: This section is for students."));
    exit();
}

// Ensure student ID is valid
if (!$loggedInUserId) {
    error_log("Student User ID not found in session for applicationController.");
    header("Location: ../View/login.php?error=" . urlencode("Session error. Please login again."));
    exit();
}


// Instantiate Models
$applicationModel = new Application($conn);
$internshipModel = new Internship($conn);

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'Apply for Internship'; // Default title

// Get action from GET or POST
$action = $_GET['action'] ?? ($_POST['action'] ?? 'myapps'); // Default to 'myapps' maybe? Or handle invalid action

// Handle messages from session/redirects
if (isset($_SESSION['success_message'])) {
    $successMessage = htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}
// Allow overriding by GET parameters if needed (e.g., direct link with message)
if (isset($_GET['success'])) {
    $successMessage = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
}


try {
    switch ($action) {
        // View application form
        case 'apply':
            $pageTitle = 'Apply for Internship'; // Set specific title

            if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
                $_SESSION['error_message'] = "Invalid internship ID provided.";
                header("Location: offerController.php?action=view");
                exit();
            }

            $internshipId = (int)$_GET['id'];

            // Check if already applied
            if ($applicationModel->hasApplied($loggedInUserId, $internshipId)) {
                $_SESSION['error_message'] = "You have already applied for this internship.";
                header("Location: offerController.php?action=view");
                exit();
            }

            // Get internship details
            $internshipDetails = $internshipModel->readInternship($internshipId); // Use specific method if available
             if (!$internshipDetails) {
                 $_SESSION['error_message'] = "Internship details not found (ID: $internshipId).";
                 header("Location: offerController.php?action=view");
                 exit();
             }

            // Show application form view
            include __DIR__ . '/../View/applicationFormView.php';
            break;

        // Process application submission
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: offerController.php?action=view"); // Redirect non-POST requests
                exit();
            }

            // Validate inputs
            $internshipId = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
            $motivationLetter = trim($_POST['motivation_letter'] ?? '');

            // Ensure we have a valid internship ID from the form post
            if (!$internshipId) {
                 error_log("Missing or invalid internship_id in application submission.");
                 // Redirect back to offers view with a generic error
                 $_SESSION['error_message'] = "Submission failed: Invalid internship reference.";
                 header("Location: offerController.php?action=view");
                 exit();
            }
             // Fetch details *before* potentially including the form view again on error
             $internshipDetails = $internshipModel->readInternship($internshipId);
             if (!$internshipDetails) {
                 error_log("Internship details not found during submission (ID: $internshipId).");
                 $_SESSION['error_message'] = "Submission failed: Internship no longer exists.";
                 header("Location: offerController.php?action=view");
                 exit();
             }


            if (empty($motivationLetter)) {
                $errorMessage = "Motivation letter is required.";
                // Redisplay form with error message
                $pageTitle = 'Apply for Internship'; // Reset title
                include __DIR__ . '/../View/applicationFormView.php';
                exit();
            }

            // Check if already applied (redundant check, but safe)
            if ($applicationModel->hasApplied($loggedInUserId, $internshipId)) {
                 $_SESSION['error_message'] = "You have already applied for this internship.";
                 header("Location: offerController.php?action=view");
                 exit();
             }

            // Handle CV upload if provided
            $cvFilePathForDb = null; // Path to store in DB (relative)

            if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/cv/'; // Absolute path for moving file
                $allowedExts = ['pdf', 'doc', 'docx'];
                $maxFileSize = 5 * 1024 * 1024; // 5 MB limit

                // Create directory if it doesn't exist
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) { // Use 0775 for permissions
                     $errorMessage = "Server error: Cannot create upload directory.";
                     error_log("Failed to create CV upload directory: " . $uploadDir);
                     $pageTitle = 'Apply for Internship';
                     include __DIR__ . '/../View/applicationFormView.php';
                     exit();
                }

                $cvOriginalName = basename($_FILES['cv_file']['name']);
                $cvFileExt = strtolower(pathinfo($cvOriginalName, PATHINFO_EXTENSION));
                $cvFileSize = $_FILES['cv_file']['size'];

                // Validate extension and size
                if (!in_array($cvFileExt, $allowedExts)) {
                    $errorMessage = "Invalid CV file type. Only PDF, DOC, and DOCX are allowed.";
                } elseif ($cvFileSize > $maxFileSize) {
                     $errorMessage = "CV file size exceeds the limit of " . ($maxFileSize / 1024 / 1024) . " MB.";
                }

                if ($errorMessage) { // If validation failed
                     $pageTitle = 'Apply for Internship';
                     include __DIR__ . '/../View/applicationFormView.php';
                     exit();
                }

                // Generate unique filename to prevent overwrites and sanitize
                $safeBaseName = preg_replace("/[^a-zA-Z0-9._-]/", "_", pathinfo($cvOriginalName, PATHINFO_FILENAME));
                $uniqueFileName = uniqid('cv_' . $loggedInUserId . '_', true) . '.' . $cvFileExt; // More unique name
                $absoluteCvPath = $uploadDir . $uniqueFileName;
                $cvFilePathForDb = 'uploads/cv/' . $uniqueFileName; // Relative path for DB

                // Move uploaded file
                if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $absoluteCvPath)) {
                    $errorMessage = "Failed to save uploaded CV file. Please try again.";
                    error_log("Failed to move uploaded file to: " . $absoluteCvPath);
                     $pageTitle = 'Apply for Internship';
                     include __DIR__ . '/../View/applicationFormView.php';
                     exit();
                }
            } // End CV handling


            // Submit application using the relative path for the DB
            $result = $applicationModel->createApplication(
                $loggedInUserId,
                $internshipId,
                $motivationLetter,
                $cvFilePathForDb // Pass the relative path or null
            );

            if ($result) {
                $_SESSION['success_message'] = "Your application has been submitted successfully!";
                header("Location: applicationController.php?action=myapps"); // Redirect to myapps view
                exit();
            } else {
                // If creation failed, try to retrieve specific error from model
                $errorMessage = $applicationModel->getError() ?: "An unknown error occurred while submitting your application.";
                // Remove uploaded file if DB insert failed? Optional, depends on desired behavior.
                /* if ($cvFilePathForDb && file_exists($absoluteCvPath)) {
                    unlink($absoluteCvPath);
                } */
                $pageTitle = 'Apply for Internship';
                include __DIR__ . '/../View/applicationFormView.php'; // Show form again with error
                exit();
            }
            break;

        // View student's applications
        case 'myapps':
            $pageTitle = "My Applications";

            // Fetch applications for the logged-in student
            $applications = $applicationModel->getStudentApplications($loggedInUserId);

            // DEBUGGING: Check what the model returned
            // error_log("Student ID " . $loggedInUserId . " | Applications fetched: " . print_r($applications, true));

            // Check if fetching applications failed (e.g., DB error in model)
             if ($applications === false || $applications === null) { // Check explicitly if model indicates error, e.g., by returning false
                 $errorMessage = "Could not retrieve your applications due to a database error.";
                 error_log("Error fetching applications for student $loggedInUserId: " . $applicationModel->getError());
                 $applications = []; // Ensure $applications is an array for the view
             } elseif (empty($applications)) {
                 // This is not an error, just means no applications found. The view handles this.
                 error_log("No applications found for student ID: " . $loggedInUserId);
             }


            // *** IMPORTANT: Pass $conn to the view for the profile picture logic ***
            include __DIR__ . '/../View/myApplicationsView.php';
            break;

        default:
             // Handle invalid actions for students
             $_SESSION['error_message'] = "Invalid action requested.";
             header("Location: offerController.php?action=view");
             exit();
    }
} catch (Exception $e) {
    // Catch any unexpected exceptions
    error_log("Unhandled Exception in applicationController (Action: $action, UserID: $loggedInUserId): " . $e->getMessage() . "\n" . $e->getTraceAsString());

    $_SESSION['error_message'] = "An critical system error occurred. Please contact support.";

    // Redirect to a safe page, like the student dashboard or offers view
    if ($loggedInUserRole === 'student') {
        header("Location: offerController.php?action=view");
    } else {
        header("Location: ../View/login.php"); // Fallback redirect
    }
    exit();
}
?>
