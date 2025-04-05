<?php
// Location: src/Controller/applicationController.php

// Required includes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Application.php';
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/company.php'; // *** ADDED Company Model ***
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

// Authorization: Only Students can perform most actions here
// Specific actions might be allowed for others later (e.g., viewing details by admin/pilote)
// For now, restrict most actions to students.
if ($loggedInUserRole !== 'student') {
     // Allow specific actions if needed later, otherwise deny
     // Example: if (!in_array($action, ['view_detail_for_admin'])) { ... deny ... }
    header("Location: ../View/login.php?error=" . urlencode("Access Denied: Only students can manage applications."));
    exit();
}
// Ensure student ID is valid
if (!$loggedInUserId) {
    // This shouldn't happen if isUserLoggedIn passed, but good safety check
    AuthSession::destroySession(); // Log out inconsistent state
    header("Location: ../View/login.php?error=" . urlencode("User session error. Please log in again."));
    exit();
}


// Instantiate Models
try {
    $applicationModel = new Application($conn);
    $internshipModel = new Internship($conn);
    $companyModel = new Company($conn); // *** Instantiate Company Model ***
} catch (Exception $e) {
    error_log("FATAL applicationController: Model instantiation error: " . $e->getMessage());
    die("A critical error occurred setting up the application page.");
}


// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = ''; // Will be set per action

// Get action from GET or POST, default to 'myapps' for students
$action = $_GET['action'] ?? ($_POST['action'] ?? 'myapps');

// Populate Messages from GET parameters (after redirects)
if(isset($_GET['success'])) { $successMessage = htmlspecialchars(urldecode($_GET['success'])); }
if(isset($_GET['error'])) { $errorMessage = htmlspecialchars(urldecode($_GET['error'])); }


try {
    switch ($action) {
        // --------------------------------------------------
        // CASE: Show Application Form
        // --------------------------------------------------
        case 'apply':
            $pageTitle = 'Apply for Internship';
            if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
                header("Location: offerController.php?action=view&error=" . urlencode("Invalid internship ID specified."));
                exit();
            }
            $internshipId = (int)$_GET['id'];

            // Check if already applied (using model method)
            $hasApplied = $applicationModel->hasApplied($loggedInUserId, $internshipId);
            if ($hasApplied === false) { // Check for DB error
                 header("Location: offerController.php?action=view&error=" . urlencode($applicationModel->getError() ?: "Error checking application status.")); exit();
            } elseif ($hasApplied === true) {
                 header("Location: offerController.php?action=view&error=" . urlencode("You have already applied for this internship.")); exit();
            }

            // Get internship details (needed for the form view)
            $internshipDetails = $internshipModel->readInternship($internshipId); // Assumes this joins company data
            if (!$internshipDetails) {
                header("Location: offerController.php?action=view&error=" . urlencode("Internship details not found."));
                exit();
            }

            // Show application form view
            include __DIR__ . '/../View/applicationFormView.php';
            break; // End case 'apply'

        // --------------------------------------------------
        // CASE: Process Application Submission
        // --------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: offerController.php?action=view"); // Redirect if not POST
                exit();
            }

            // Validate inputs
            $internshipId = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
            $motivationLetter = trim($_POST['motivation_letter'] ?? '');

            // Basic Validation
             if (!$internshipId) {
                  // Redirect back to offers list if internship ID is missing/invalid in POST
                 header("Location: offerController.php?action=view&error=" . urlencode("Invalid internship ID provided in submission.")); exit();
             }
             if (empty($motivationLetter)) {
                $errorMessage = "Motivation letter is required.";
                // To redisplay form, need internship details again
                $internshipDetails = $internshipModel->readInternship($internshipId);
                if (!$internshipDetails) { header("Location: offerController.php?action=view&error=" . urlencode("Internship not found while handling validation error.")); exit(); }
                $pageTitle = 'Apply for Internship'; // Set title for the view
                include __DIR__ . '/../View/applicationFormView.php';
                exit(); // Stop script after including view
            }

            // Double-check if already applied before processing
            $hasApplied = $applicationModel->hasApplied($loggedInUserId, $internshipId);
             if ($hasApplied === false) {
                  header("Location: offerController.php?action=view&error=" . urlencode($applicationModel->getError() ?: "Error checking application status before submit.")); exit();
             } elseif ($hasApplied === true) {
                  header("Location: offerController.php?action=view&error=" . urlencode("You have already applied for this internship.")); exit();
             }


            // Handle CV upload (Your existing logic seems reasonable)
            $cvFilePath = null; // Database path (relative)
            // $cvFileName = null; // Original filename (Model doesn't seem to use this currently)

            if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/cv/'; // Absolute path for moving file
                $relativeUploadDir = 'uploads/cv/';      // Relative path for storing in DB

                // Create directory if it doesn't exist
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                     // Failed to create directory
                     $errorMessage = "Server error: Cannot create upload directory.";
                     // Fall through to display error on form
                } else {
                     $originalFileName = basename($_FILES['cv_file']['name']);
                     // Sanitize filename for security and filesystem compatibility
                     $safeFileName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $originalFileName);
                     $cvFileExt = strtolower(pathinfo($safeFileName, PATHINFO_EXTENSION));
                     $allowedExts = ['pdf', 'doc', 'docx'];

                     // Validate extension and size
                     if (!in_array($cvFileExt, $allowedExts)) {
                         $errorMessage = "Only PDF, DOC, and DOCX files are allowed for CV.";
                     } elseif ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) { // Example 5MB limit
                         $errorMessage = "CV file is too large (Max 5MB).";
                     } else {
                         // Generate unique filename to prevent overwrites
                         $uniqueFileName = uniqid('cv_') . '_' . $safeFileName;
                         $targetPath = $uploadDir . $uniqueFileName; // Full path to move to

                         // Move uploaded file
                         if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $targetPath)) {
                             $errorMessage = "Failed to upload CV file. Please check permissions.";
                         } else {
                             // Store the relative path for the database
                             $cvFilePath = $relativeUploadDir . $uniqueFileName;
                             // $cvFileName = $originalFileName; // Store original if model uses it
                         }
                     }
                 }

                // If there was an upload error, redisplay the form
                 if (!empty($errorMessage)) {
                     $internshipDetails = $internshipModel->readInternship($internshipId);
                     if (!$internshipDetails) { header("Location: offerController.php?action=view&error=" . urlencode("Internship not found while handling upload error.")); exit(); }
                     $pageTitle = 'Apply for Internship';
                     include __DIR__ . '/../View/applicationFormView.php';
                     exit();
                 }
            } // End CV upload handling

            // Submit application to the database
            // Pass $cvFilePath (which is null if no file or error)
            $result = $applicationModel->createApplication(
                $loggedInUserId,
                $internshipId,
                $motivationLetter,
                $cvFilePath // Pass the relative path or null
                // Pass $cvFileName if your model's createApplication method accepts it
            );

            if ($result) {
                // Success: Redirect to 'My Applications' page with success message
                $successMessage = "Your application has been submitted successfully!";
                header("Location: applicationController.php?action=myapps&success=" . urlencode($successMessage));
                exit();
            } else {
                // Failure: Set error message and redisplay form
                $errorMessage = $applicationModel->getError() ?: "Failed to submit application. Database error.";
                // Delete uploaded file if DB insert failed
                 if ($cvFilePath !== null && isset($targetPath) && file_exists($targetPath)) {
                     unlink($targetPath);
                     error_log("Deleted uploaded CV $cvFilePath due to DB insert failure for application.");
                 }
                // Refetch details for the form
                $internshipDetails = $internshipModel->readInternship($internshipId);
                if (!$internshipDetails) { header("Location: offerController.php?action=view&error=" . urlencode("Internship not found while handling submit error.")); exit(); }
                $pageTitle = 'Apply for Internship';
                include __DIR__ . '/../View/applicationFormView.php';
                exit();
            }
            break; // End case 'submit'

        // --------------------------------------------------
        // CASE: Submit Company Rating
        // --------------------------------------------------
        case 'rate_company':
             // Ensure it's a POST request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                 header("Location: applicationController.php?action=myapps&error=" . urlencode("Invalid request method for rating."));
                 exit();
             }

             // Get and validate data from POST
             $companyId = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
             $ratingValue = filter_input(INPUT_POST, 'rating_value', FILTER_VALIDATE_INT);
             $comment = trim($_POST['comment'] ?? ''); // Get optional comment

             // Basic Validation
             if (!$companyId) {
                  header("Location: applicationController.php?action=myapps&error=" . urlencode("Missing company ID for rating.")); exit();
             }
              if ($ratingValue === false || $ratingValue < 1 || $ratingValue > 5) { // Check if rating is integer 1-5
                  header("Location: applicationController.php?action=myapps&error=" . urlencode("Invalid rating value submitted (must be 1-5).")); exit();
             }

             // Attempt to add rating using the Company model's method
             $result = $companyModel->addRating($companyId, $loggedInUserId, $ratingValue, $comment);

             if ($result) {
                 // Success: Redirect back to My Applications with success message
                 header("Location: applicationController.php?action=myapps&success=" . urlencode("Thank you for rating the company!"));
                 exit();
             } else {
                 // Failure: Redirect back with error message from the model
                 $errorMsg = $companyModel->getError() ?: "Failed to submit rating. Please try again.";
                 header("Location: applicationController.php?action=myapps&error=" . urlencode($errorMsg));
                 exit();
             }
             break; // End case 'rate_company'


        // --------------------------------------------------
        // CASE: View Student's Applications
        // --------------------------------------------------
        case 'myapps':
        default: // Default action for students is to view their applications
            $pageTitle = "My Applications";
            // Fetch applications using the model method
            $applications = $applicationModel->getStudentApplications($loggedInUserId);

            // Check for errors during fetch
            if ($applications === false) {
                 $errorMessage = $applicationModel->getError() ?: "Could not retrieve your applications.";
                 $applications = []; // Ensure it's an empty array for the view
            } else {
                 // *** NEW: Check if student has rated the company for each application ***
                 if (is_array($applications)) {
                     foreach ($applications as $key => &$app) { // Use reference
                         // Make sure company_id exists in the data fetched by getStudentApplications
                         if (!empty($app['company_id'])) {
                             // Check rating status using Company model
                             $app['has_rated'] = $companyModel->hasStudentRatedCompany($loggedInUserId, $app['company_id']);
                              // Handle potential error from hasStudentRatedCompany if necessary
                             if ($app['has_rated'] === false && $companyModel->getError()) {
                                 error_log("Error checking rating status for company {$app['company_id']}: " . $companyModel->getError());
                                 // Decide how to handle: maybe show button anyway or show an error?
                                 // $app['has_rated'] = false; // Default to not rated on error
                             }
                         } else {
                             $app['has_rated'] = false; // Cannot rate if company ID is missing
                             error_log("Warning: company_id missing for application ID: " . ($app['id_application'] ?? 'unknown'));
                         }
                     }
                     unset($app); // Unset reference
                 }
            }

            // Debugging logs (optional)
            error_log("Applications in controller (for myapps view): " . (is_array($applications) ? count($applications) : 'not an array'));

            // Include the view to display the applications
            include __DIR__ . '/../View/myApplicationsView.php';
            break; // End case 'myapps'/default

        // Add other cases like view_detail, download_cv, cancel if implemented

    } // End Switch
} catch (Exception $e) {
    error_log("Unhandled Exception in applicationController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $errorMessage = "An unexpected system error occurred. Please report this issue.";
    // Redirect to a safe fallback page, like the offers view or student dashboard
    header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
    exit();
}
?>