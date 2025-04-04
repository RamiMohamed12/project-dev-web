<?php
// Location: src/Controller/offerController.php

// Required includes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/company.php';
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

// Instantiate Models
$internshipModel = new Internship($conn);
$companyModel = new Company($conn);

// For student view, we need the wishlist model
if ($loggedInUserRole === 'student') {
    require_once __DIR__ . '/../Model/Wishlist.php';
    $wishlistModel = new Wishlist($conn);
}

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'Internship Offers';

// Get action from GET or POST
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// Handle error/success messages from redirects
if (isset($_GET['error'])) {
    $errorMessage = $_GET['error'];
}
if (isset($_GET['success'])) {
    $successMessage = $_GET['success'];
}

try {
    switch ($action) {
        // View offers (for students)
        case 'view':
            if ($loggedInUserRole !== 'student') {
                header("Location: internshipController.php");
                exit();
            }
            
            $pageTitle = 'Available Internship Offers';
            
            // Handle search and sorting
            $search = $_GET['search'] ?? '';
            $sort = $_GET['sort'] ?? 'newest';
            
            // Get all internships with company details
            $internships = $internshipModel->getAllInternshipsWithCompanyDetails($search, $sort);
            
            include __DIR__ . '/../View/viewOffersView.php';
            break;
            
        // Default action for admin/pilote is to redirect to internshipController
        default:
            if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') {
                header("Location: internshipController.php");
            } else {
                header("Location: offerController.php?action=view");
            }
            exit();
    }
} catch (Exception $e) {
    error_log("Unhandled Exception in offerController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $errorMessage = "An unexpected system error occurred. Please report this issue.";
    include __DIR__ . '/../View/viewOffersView.php';
    exit();
}
?>