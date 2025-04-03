<?php
// Location: /home/demy/project-dev-web/src/Controller/editCompany.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php'; // Use Company model
require_once __DIR__ . '/../Auth/AuthSession.php';
require_once __DIR__ . '/../Auth/AuthCheck.php'; // Basic check if needed

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
    $companyModel = new Company($conn);
} catch (Exception $e) {
    error_log("EditCompany: Failed to instantiate Company model: " . $e->getMessage());
    die("A critical error occurred during company edit setup.");
}

// --- Check if target company ID is provided ---
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: companyController.php?error=missing_id"); // Redirect back to list
    exit();
}

$targetCompanyId = (int) $_GET['id'];

// --- Fetch Target Company Details (Including Creator ID) ---
$companyDetails = $companyModel->read($targetCompanyId);

if (!$companyDetails) {
    header("Location: companyController.php?error=not_found"); exit();
}

// --- AUTHORIZATION CHECK ---
$canEdit = false;
if ($loggedInUserRole === 'admin') {
    // Admins can edit any company
    $canEdit = true;
} elseif ($loggedInUserRole === 'pilote') {
    // Pilotes can edit companies they created
    if (isset($companyDetails['created_by_pilote_id']) && $companyDetails['created_by_pilote_id'] == $loggedInUserId) {
        $canEdit = true;
    }
}

// If not authorized, redirect with error
if (!$canEdit) {
     header("Location: companyController.php?error=auth_edit_failed"); // Redirect back to list
     exit();
}

// --- Handle Form Submission (POST) ---
$errorMessage = '';
$successMessage = ''; // Initialize
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {

    // Re-check authorization
    if (!$canEdit) {
         $errorMessage = "Authorization failed during update process.";
    } else {
        // Collect data
        $name = $_POST['name'] ?? '';
        $location = $_POST['location'] ?? '';
        $description = $_POST['description'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';

         // Basic Validation
        if (empty($name) || empty($location) || empty($email) || empty($phone)) {
             $errorMessage = "Error: Name, Location, Email, and Phone are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $errorMessage = "Error: Invalid email format.";
        } elseif (!preg_match('/^\+?[0-9\s\-]+$/', $phone)) {
             $errorMessage = "Error: Invalid phone number format.";
        } else {
            // Call update method
            $result = $companyModel->update($targetCompanyId, $name, $location, $description, $email, $phone);

            if ($result) {
                header("Location: companyController.php?update=success");
                exit();
            } else {
                $errorMessage = $companyModel->error ?: "Error updating company.";
            }
        }
    }
}

// --- Prepare for View ---
$pageTitle = "Edit Company";

// --- Include the View ---
include __DIR__ . '/../View/editCompanyView.php'; // Assuming a separate view file

?>
