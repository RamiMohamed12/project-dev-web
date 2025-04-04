<?php
// Location: src/Controller/internshipController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/internship.php'; // Use Internship model
require_once __DIR__ . '/../Model/company.php'; // For company selection
require_once __DIR__ . '/../Auth/AuthSession.php';

if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

// --- Basic Auth Check & Role/ID ---
if (!AuthSession::isUserLoggedIn()) { 
    header("Location: ../View/login.php?error=" . urlencode("Authentication required.")); 
    exit(); 
}

$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') { 
    AuthSession::destroySession(); 
    header("Location: ../View/login.php?error=" . urlencode("Access Denied: Insufficient privileges.")); 
    exit(); 
}

// --- Instantiate Models ---
try { 
    $internshipModel = new Internship($conn); 
    $companyModel = new Company($conn); // For company dropdown
} catch (Exception $e) { 
    error_log("Internship controller model error: " . $e->getMessage()); 
    die("Critical error: Could not initialize data models."); 
}

// --- Init View Vars ---
$internships = [];
$companies = []; // For form dropdown
$pageTitle = "Internship Offer Management";
$errorMessage = '';
$successMessage = '';

// Check for messages passed via GET params from redirects
if(isset($_GET['update']) && $_GET['update'] == 'success') { 
    $successMessage = "Internship offer updated successfully."; 
}
if(isset($_GET['delete']) && $_GET['delete'] == 'success') { 
    $successMessage = "Internship offer deleted successfully."; 
}
if(isset($_GET['add']) && $_GET['add'] == 'success') { 
    $successMessage = "Internship offer added successfully."; 
}
if(isset($_GET['error'])) { 
    $errorMessage = htmlspecialchars(urldecode($_GET['error'])); 
}

// --- Handle POST Actions (Add, Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACTION: Add Internship Offer ---
    if ($action == 'add') {
        // Retrieve all potential fields
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $companyId = $_POST['company_id'] ?? '';
        $remuneration = $_POST['remuneration'] ?? null;
        $offreDate = $_POST['offre_date'] ?? date('Y-m-d');

        $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;

        // Basic required field validation
        if (empty($title) || empty($description) || empty($companyId)) {
            $errorMessage = "Error: Title, description, and company are required.";
        } else {
            // Attempt to create the internship offer
            $result = $internshipModel->create(
                $title, 
                $description, 
                $companyId, 
                $remuneration,
                $offreDate,
                $creatorPiloteId
            );

            if ($result) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?add=success"); // Redirect on success
                exit();
            } else {
                // If create failed, get error from model
                $errorMessage = $internshipModel->getError() ?: "Error: Could not add internship offer. Please check details.";
            }
        }
    }
    // --- ACTION: Delete Internship Offer ---
    elseif ($action == 'delete') {
        $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($idToDelete <= 0) {
            $errorMessage = "Error: Invalid ID provided for deletion.";
        } else {
            // Since we can't track who created which offer, allow all admins and pilotes to delete any
            $allowedToDelete = ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote');
            $internshipDetails = null;
            
            try {
                $internshipDetails = $internshipModel->read($idToDelete); // Fetch details to check if exists
            } catch (Exception $e) {
                error_log("Error fetching internship for delete check (ID: $idToDelete): " . $e->getMessage());
                $errorMessage = "Error verifying internship before deletion.";
            }

            if (!$internshipDetails && empty($errorMessage)) {
                $errorMessage = "Error: Internship not found (ID: $idToDelete).";
            }

            if ($allowedToDelete && empty($errorMessage)) {
                $result = $internshipModel->delete($idToDelete);
                if ($result) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?delete=success"); // Redirect on success
                    exit();
                } else {
                    // Get error
                    $errorMessage = $internshipModel->getError() ?: "Error: Could not delete internship offer.";
                }
            } elseif(empty($errorMessage)) {
                $errorMessage = "Error: Permission denied to delete this internship offer.";
            }
        }
    }
}

// --- Fetch Data for Display (GET request or after failed POST) ---
try {
    // Get companies for the dropdown
    if ($loggedInUserRole === 'admin') {
        $companies = $companyModel->readAll();
    } elseif ($loggedInUserRole === 'pilote') {
        $companies = $companyModel->readAll($loggedInUserId);
    }

    if ($companies === false) {
        $errorMessage = $companyModel->getError() ?: "Error fetching company data for the form.";
        $companies = [];
    }

    // Get internship offers - Since we can't filter by creator, all users see all offers
    $internships = $internshipModel->readAll();
    
    if ($loggedInUserRole === 'admin') {
        $pageTitle = "Manage All Internship Offers";
    } elseif ($loggedInUserRole === 'pilote') {
        $pageTitle = "Manage Internship Offers";
    }

    if ($internships === false) {
        $errorMessage = $internshipModel->getError() ?: "Error fetching internship data.";
        $internships = [];
    }

} catch (Exception $e) {
    error_log("Exception in internshipController: " . $e->getMessage());
    $errorMessage = "An unexpected error occurred while retrieving data.";
    $internships = [];
    $companies = [];
}

// --- Include the View ---
include __DIR__ . '/../View/manageInternshipsView.php';
?> 