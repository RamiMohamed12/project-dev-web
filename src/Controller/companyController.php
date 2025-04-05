<?php
// Location: /home/demy/project-dev-web/src/Controller/companyController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php'; // Use Company model
require_once __DIR__ . '/../Model/Internship.php'; // Added for potential future use, not strictly needed for rating
require_once __DIR__ . '/../Auth/AuthSession.php';
// AuthCheck.php is not directly used here, AuthSession is sufficient

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Basic Auth Check (Is anyone logged in?) ---
if (!AuthSession::isUserLoggedIn()) {
    header("Location: ../View/login.php?error=" . urlencode("Authentication required."));
    exit();
}
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Instantiate Model ---
try {
    $companyModel = new Company($conn);
} catch (Exception $e) {
    error_log("Company controller model instantiation error: " . $e->getMessage());
    // Redirect to a generic error page or dashboard might be better than die()
    // For now, keeping die for critical failure
    die("Critical error initializing company data access.");
}

// --- Determine Action ---
// Rating action comes via GET parameter, others via POST or default GET
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list'); // Default to 'list' for management view

// --- Init View Vars (Mostly for management view) ---
$companies = [];
$pageTitle = "Company Management"; // Default title
$errorMessage = '';
$successMessage = '';

// Check for messages passed via GET params from redirects (keep this)
if(isset($_GET['update']) && $_GET['update'] == 'success') { $successMessage = "Company updated successfully."; }
if(isset($_GET['delete']) && $_GET['delete'] == 'success') { $successMessage = "Company deleted successfully."; }
if(isset($_GET['add']) && $_GET['add'] == 'success') { $successMessage = "Company added successfully."; }
// Add messages for rating
if(isset($_GET['rate']) && $_GET['rate'] == 'success') { $successMessage = "Rating submitted successfully."; }
if(isset($_GET['rate']) && $_GET['rate'] == 'error') { $errorMessage = htmlspecialchars(urldecode($_GET['error_msg'] ?? "Failed to submit rating.")); }
// Generic error message handling
if(isset($_GET['error'])) { $errorMessage = htmlspecialchars(urldecode($_GET['error'])); }


// --- ACTION ROUTING AND AUTHORIZATION ---

switch ($action) {

    // --- ACTION: Rate Company (Student Only) ---
    case 'rate':
        // ** Authorization Check: Must be a student **
        if ($loggedInUserRole !== 'student') {
            AuthSession::destroySession(); // Log out non-students trying this
            header("Location: ../View/login.php?error=" . urlencode("Access Denied. Rating is for students only."));
            exit();
        }

        // ** Handle POST request for rating submission **
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get data from POST
            $companyId = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
            $ratingValue = filter_input(INPUT_POST, 'rating_value', FILTER_VALIDATE_INT);
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS); // Sanitize comment
            // $studentIdFromForm = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT); // Get from form too for potential logging/debugging

            // ** Security Check: Ensure the student ID from form matches session **
            // Use $loggedInUserId from session, don't rely solely on hidden form field
            $studentId = $loggedInUserId;

            // Basic Validation
            $rateError = '';
            if (!$companyId) {
                $rateError = "Invalid company specified.";
            } elseif (!$ratingValue || $ratingValue < 1 || $ratingValue > 5) {
                $rateError = "Invalid rating value (must be 1-5).";
            } elseif (!$studentId) {
                 $rateError = "Could not verify student identity."; // Should not happen if logged in
                 error_log("Company Rating Error: Logged-in student ID missing in session during rating attempt.");
            }

            // If validation passes, attempt to add rating
            if (empty($rateError)) {
                $result = $companyModel->addRating($companyId, $studentId, $ratingValue, $comment ?: null); // Pass sanitized comment or null

                if ($result) {
                    $successMessage = "Rating submitted successfully.";
                    // Redirect back to the offers page (preserving filters if possible)
                    $returnUrl = $_SESSION['rating_return_url'] ?? '../Controller/offerController.php?action=view';
                    unset($_SESSION['rating_return_url']); // Clean up session variable
                    header("Location: " . $returnUrl . "&rate=success"); // Append success param
                    exit();
                } else {
                    // Get error from model (e.g., "already rated", DB error)
                    $rateError = $companyModel->getError() ?: "An unknown error occurred while submitting the rating.";
                }
            }

            // If we reach here, there was an error (validation or submission)
            // Redirect back with error message
            $returnUrl = $_SESSION['rating_return_url'] ?? '../Controller/offerController.php?action=view';
            unset($_SESSION['rating_return_url']); // Clean up session variable
            header("Location: " . $returnUrl . "&rate=error&error_msg=" . urlencode($rateError));
            exit();

        } else {
            // If someone tries to access companyController.php?action=rate directly via GET
            header("Location: ../Controller/offerController.php?action=view&error=" . urlencode("Invalid request method for rating."));
            exit();
        }
        break; // End case 'rate'

    // --- ACTION: Add Company (Admin/Pilote Only) ---
    case 'add':
        // ** Authorization Check: Must be admin or pilote **
        if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
            AuthSession::destroySession();
            header("Location: ../View/login.php?error=" . urlencode("Access Denied."));
            exit();
        }

        // Handle POST request
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Retrieve all potential fields
            $name = $_POST['name'] ?? '';
            $location = $_POST['location'] ?? '';
            $description = $_POST['description'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $url = $_POST['url'] ?? null;

            $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;

            // Basic required field validation
            if (empty($name) || empty($location) || empty($email) || empty($phone)) {
                 $errorMessage = "Error: Name, Location, Email, and Phone are required.";
                 // Fall through to load the management view with the error message
            } else {
                // Attempt to create the company
                $result = $companyModel->create($name, $location, $description, $email, $phone, $url, $creatorPiloteId);

                if ($result) {
                    header("Location: companyController.php?action=list&add=success"); // Redirect on success TO THE LIST VIEW
                    exit();
                } else {
                    $errorMessage = $companyModel->getError() ?: "Error: Could not add company. Please check details.";
                    // Fall through to load the management view with the error message
                }
            }
        } else {
             // If accessed via GET, just proceed to load the default management view below
             $action = 'list'; // Force list action
             goto load_management_view; // Jump to view loading
        }
        break; // End case 'add'


    // --- ACTION: Delete Company (Admin/Pilote Only) ---
    case 'delete':
         // ** Authorization Check: Must be admin or pilote **
         if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
             AuthSession::destroySession();
             header("Location: ../View/login.php?error=" . urlencode("Access Denied."));
             exit();
         }

        // Handle POST request
         if ($_SERVER["REQUEST_METHOD"] == "POST") {
             $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;

             if ($idToDelete <= 0) {
                  $errorMessage = "Error: Invalid ID provided for deletion.";
                  // Fall through to load the management view with the error message
             } else {
                 // Authorization check before deleting (Admin or Owning Pilote)
                 $allowedToDelete = false;
                 $companyDetails = $companyModel->read($idToDelete);

                 if (!$companyDetails) {
                      $errorMessage = "Error: Company not found (ID: $idToDelete).";
                 } else {
                      if ($loggedInUserRole === 'admin') {
                          $allowedToDelete = true;
                     } elseif ($loggedInUserRole === 'pilote' && isset($companyDetails['created_by_pilote_id']) && $companyDetails['created_by_pilote_id'] == $loggedInUserId) {
                         $allowedToDelete = true;
                     } else {
                         $errorMessage = "Error: You do not have permission to delete this company.";
                     }
                 }

                 if ($allowedToDelete) {
                     $result = $companyModel->delete($idToDelete);
                      if ($result) {
                         header("Location: companyController.php?action=list&delete=success"); // Redirect TO LIST VIEW
                         exit();
                     } else {
                         $errorMessage = $companyModel->getError() ?: "Error: Could not delete company.";
                         // Fall through to load the management view with the error message
                     }
                 }
                 // If not allowed, $errorMessage is already set, fall through to load view
             }
         } else {
             // If accessed via GET, just proceed to load the default management view below
              $action = 'list'; // Force list action
              goto load_management_view; // Jump to view loading
         }
         break; // End case 'delete'


    // --- DEFAULT ACTION: List Companies (Admin/Pilote Only) ---
    case 'list':
    default: // Handles 'list' and any other unspecified action
        // ** Authorization Check: Must be admin or pilote for management view **
        if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
            // If a student somehow ends up here without a specific action like 'rate'
            AuthSession::destroySession();
            header("Location: ../View/login.php?error=" . urlencode("Access Denied to company management."));
            exit();
        }

        load_management_view: // Label for goto jump after failed add/delete POST or GET access

        // --- Fetch Data for Display (GET request or after failed POST) ---
        try {
            if ($loggedInUserRole === 'admin') {
                $companies = $companyModel->readAll();
                $pageTitle = "Manage All Companies";
            } elseif ($loggedInUserRole === 'pilote') {
                $companies = $companyModel->readAll($loggedInUserId);
                $pageTitle = "Manage My Companies";
            }

            if ($companies === false) {
                $errorMessage = $companyModel->getError() ?: "Error fetching company data.";
                error_log("Error in companyController fetching data: " . $errorMessage);
                $companies = [];
            }
        } catch (Exception $e) {
             error_log("Exception fetching company data in companyController: " . $e->getMessage());
             $errorMessage = "An unexpected error occurred while retrieving the company list.";
             $companies = [];
        }

        // --- Include the Management View ---
        include __DIR__ . '/../View/manageCompaniesView.php';
        exit(); // Stop script execution after including the view

} // End switch ($action)

// --- Fallback / Safety Net ---
// If the script reaches here without exiting (e.g., action wasn't handled or fell through incorrectly)
// redirect to a default safe page based on role.
error_log("CompanyController reached end without explicit action handling for action: " . $action);
if ($loggedInUserRole === 'student') {
    header("Location: ../Controller/offerController.php?action=view");
} elseif ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') {
    header("Location: companyController.php?action=list"); // Go to management view
} else {
    header("Location: ../View/login.php"); // Fallback to login
}
exit();

?>
