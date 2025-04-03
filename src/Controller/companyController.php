<?php
// Location: /home/demy/project-dev-web/src/Controller/companyController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php'; // Use Company model
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

// --- Authorization Check (Admins or Pilotes only) ---
if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
     AuthSession::destroySession();
     header("Location: ../View/login.php?error=" . urlencode("Access Denied: Insufficient privileges for company management."));
     exit();
}

// --- Instantiate Model ---
try {
    $companyModel = new Company($conn);
} catch (Exception $e) {
    error_log("Failed to instantiate Company model: " . $e->getMessage());
    die("A critical error occurred setting up company management.");
}

// --- Variables for the View ---
$companies = [];
$pageTitle = "Company Management";
$errorMessage = $companyModel->error; // Get potential errors
$successMessage = '';
// Add success message handling similar to userController if needed


// --- Handle POST Actions (Add, Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACTION: Add Company ---
    if ($action == 'add') {
        // Permissions: Admin & Pilote can add
        $name = $_POST['name'] ?? '';
        $location = $_POST['location'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

        $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;

        if (empty($name) || empty($location) || empty($email) || empty($phone)) {
             $errorMessage = "Error: Name, Location, Email, and Phone are required.";
        } else {
            $result = $companyModel->create($name, $location, $description, $email, $phone, $creatorPiloteId);
            if ($result) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?add=success"); // Redirect
                exit();
            } else {
                $errorMessage = $companyModel->error ?: "Error: Could not add company.";
            }
        }
    }
    // --- ACTION: Delete Company ---
    elseif ($action == 'delete') {
        $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idToDelete <= 0) {
             $errorMessage = "Error: Invalid ID for deletion.";
        } else {
            // Authorization check
            $allowedToDelete = false;
            $companyDetails = $companyModel->read($idToDelete); // Fetch company details including creator

            if (!$companyDetails) {
                 $errorMessage = "Error: Company not found for deletion.";
            } elseif ($loggedInUserRole === 'admin') {
                 $allowedToDelete = true; // Admins can delete any company
            } elseif ($loggedInUserRole === 'pilote') {
                // Pilotes can only delete companies they created
                if (isset($companyDetails['created_by_pilote_id']) && $companyDetails['created_by_pilote_id'] == $loggedInUserId) {
                    $allowedToDelete = true;
                } else {
                    $errorMessage = "Error: You can only delete companies you created.";
                }
            }

            if (!$allowedToDelete && empty($errorMessage)) {
                 $errorMessage = "Error: You do not have permission to delete this company.";
            }

            if ($allowedToDelete) {
                $result = $companyModel->delete($idToDelete);
                 if ($result) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?delete=success"); // Redirect
                    exit();
                } else {
                    $errorMessage = $companyModel->error ?: "Error: Could not delete company.";
                     // Consider if foreign key constraints (internships) might prevent deletion
                     if (strpos($errorMessage, 'foreign key constraint') !== false) {
                         $errorMessage = "Error: Cannot delete company because it has associated internships.";
                     }
                }
            }
        }
    }
}


// --- Fetch Data for Display Based on Role ---
try {
    if ($loggedInUserRole === 'admin') {
        // Admin sees all companies
        $companies = $companyModel->readAll();
         $pageTitle = "Manage All Companies";
    } elseif ($loggedInUserRole === 'pilote') {
        // Pilote sees ONLY companies they created
        $companies = $companyModel->readAll($loggedInUserId); // Pass pilote's ID
         $pageTitle = "Manage My Companies";
    }

    if ($companies === false) {
        $errorMessage = $companyModel->error ?: "Error fetching company data.";
        error_log("Error in companyController fetching data: " . $errorMessage);
        $companies = []; // Prevent view errors
    }

} catch (Exception $e) {
     error_log("Exception fetching company data in companyController: " . $e->getMessage());
     $errorMessage = "An unexpected error occurred while retrieving the company list.";
     $companies = [];
}

// --- Include the View ---
// The view needs to be updated to conditionally show edit/delete links
include __DIR__ . '/../View/manageCompaniesView.php'; // Assuming a separate view file

?>
