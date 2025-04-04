<?php
// Location: /home/demy/project-dev-web/src/Controller/companyController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php'; // Use Company model
require_once __DIR__ . '/../Auth/AuthSession.php';
// require_once __DIR__ . '/../Auth/AuthCheck.php'; // Specific checks done below

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Basic Auth Check & Role/ID ---
if (!AuthSession::isUserLoggedIn()) { header("Location: ../View/login.php?error=" . urlencode("Auth required.")); exit(); }
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');
if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') { AuthSession::destroySession(); header("Location: ../View/login.php?error=" . urlencode("Access Denied.")); exit(); }

// --- Instantiate Model ---
try { $companyModel = new Company($conn); }
catch (Exception $e) { error_log("Company controller model error: " . $e->getMessage()); die("Critical error (CCM)."); }

// --- Init View Vars ---
$companies = [];
$pageTitle = "Company Management";
$errorMessage = ''; // Initialize empty
$successMessage = ''; // Initialize empty

// Check for messages passed via GET params from redirects
if(isset($_GET['update']) && $_GET['update'] == 'success') { $successMessage = "Company updated successfully."; }
if(isset($_GET['delete']) && $_GET['delete'] == 'success') { $successMessage = "Company deleted successfully."; }
if(isset($_GET['add']) && $_GET['add'] == 'success') { $successMessage = "Company added successfully."; }
if(isset($_GET['error'])) { $errorMessage = htmlspecialchars(urldecode($_GET['error'])); }


// --- Handle POST Actions (Add, Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACTION: Add Company ---
    if ($action == 'add') {
        // Retrieve all potential fields
        $name = $_POST['name'] ?? '';
        $location = $_POST['location'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $url = $_POST['url'] ?? null; // ***** GET URL FIELD *****

        $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;

        // Basic required field validation
        if (empty($name) || empty($location) || empty($email) || empty($phone)) {
             $errorMessage = "Error: Name, Location, Email, and Phone are required.";
        } else {
            // Attempt to create the company (model handles further validation like email/url format)
            $result = $companyModel->create(
                $name, $location, $description, $email, $phone,
                $url, // ***** PASS URL TO MODEL *****
                $creatorPiloteId
            );

            if ($result) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?add=success"); // Redirect on success
                exit();
            } else {
                // If create failed, get error from model
                $errorMessage = $companyModel->getError() ?: "Error: Could not add company. Please check details.";
            }
        }
    }
    // --- ACTION: Delete Company ---
    elseif ($action == 'delete') {
        $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idToDelete <= 0) {
             $errorMessage = "Error: Invalid ID provided for deletion.";
        } else {
            // Authorization check before deleting
            $allowedToDelete = false;
            $companyDetails = null;
            try {
                $companyDetails = $companyModel->read($idToDelete); // Fetch details to check owner
            } catch (Exception $e) {
                error_log("Error fetching company for delete check (ID: $idToDelete): " . $e->getMessage());
                $errorMessage="Error verifying company before deletion.";
            }

            if (!$companyDetails && empty($errorMessage)) { // Check if fetch worked and company exists
                 $errorMessage = "Error: Company not found (ID: $idToDelete).";
            } elseif (empty($errorMessage)) { // Only proceed if fetch worked
                 if ($loggedInUserRole === 'admin') {
                     $allowedToDelete = true; // Admins can delete any
                } elseif ($loggedInUserRole === 'pilote') {
                    // Pilotes can delete only if 'created_by_pilote_id' matches their ID
                    if (isset($companyDetails['created_by_pilote_id']) && $companyDetails['created_by_pilote_id'] == $loggedInUserId) {
                        $allowedToDelete = true;
                    } else {
                        $errorMessage = "Error: You do not have permission to delete this company.";
                    }
                }
            }


            if ($allowedToDelete) { // Proceed only if authorized
                $result = $companyModel->delete($idToDelete);
                 if ($result) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?delete=success"); // Redirect on success
                    exit();
                } else {
                    // Get error (could be DB error or constraint violation)
                    $errorMessage = $companyModel->getError() ?: "Error: Could not delete company.";
                }
            } elseif(empty($errorMessage)) { // If not allowed but no specific error set
                $errorMessage = "Error: Permission denied to delete this company.";
            }
        } // End ID check
    } // End delete action
} // End POST handling


// --- Fetch Data for Display (GET request or after failed POST) ---
try {
    if ($loggedInUserRole === 'admin') {
        $companies = $companyModel->readAll(); // Admin gets all
         $pageTitle = "Manage All Companies";
    } elseif ($loggedInUserRole === 'pilote') {
        $companies = $companyModel->readAll($loggedInUserId); // Pilote gets only their own
         $pageTitle = "Manage My Companies";
    }

    // Check if readAll returned false (indicating an error)
    if ($companies === false) {
        $errorMessage = $companyModel->getError() ?: "Error fetching company data."; // Get specific error if available
        error_log("Error in companyController fetching data: " . $errorMessage);
        $companies = []; // Ensure $companies is an empty array for the view
    }

} catch (Exception $e) {
     error_log("Exception fetching company data in companyController: " . $e->getMessage());
     $errorMessage = "An unexpected error occurred while retrieving the company list.";
     $companies = []; // Ensure $companies is an empty array
}

// --- Include the View ---
// Pass necessary variables to the view file
include __DIR__ . '/../View/manageCompaniesView.php';
?>
