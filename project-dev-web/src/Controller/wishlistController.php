<?php
// Location: src/Controller/wishlistController.php

// Required includes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Wishlist.php';
require_once __DIR__ . '/../Model/Internship.php';
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

// Authorization: Only Students can access wishlist
if ($loggedInUserRole !== 'student') {
    header("Location: ../View/login.php?error=" . urlencode("Access Denied: Only students can access wishlist."));
    exit();
}

// Instantiate Models
$wishlistModel = new Wishlist($conn);
$internshipModel = new Internship($conn);

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'My Wishlist';

// Get action from GET or POST
$action = $_GET['action'] ?? ($_POST['action'] ?? 'view');

try {
    switch ($action) {
        // View wishlist
        case 'view':
            $wishlistItems = $wishlistModel->getStudentWishlist($loggedInUserId);
            include __DIR__ . '/../View/wishlistView.php';
            break;

        // Add to wishlist
        case 'add':
            if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
                $errorMessage = "Invalid internship ID.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            $internshipId = (int)$_GET['id'];
            
            // Verify the internship exists
            $internshipExists = $internshipModel->readInternship($internshipId);
            if (!$internshipExists) {
                $errorMessage = "Internship not found.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            // Add to wishlist
            if ($wishlistModel->addToWishlist($loggedInUserId, $internshipId)) {
                $successMessage = "Internship added to your wishlist.";
                header("Location: offerController.php?action=view&success=" . urlencode($successMessage));
            } else {
                $errorMessage = $wishlistModel->getError() ?: "Failed to add to wishlist.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
            }
            exit();
            break;

        // Remove from wishlist
        case 'remove':
            if (!isset($_GET['id']) || empty($_GET['id'])) {
                $errorMessage = "Missing internship ID.";
                header("Location: wishlistController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            $internshipId = (int)$_GET['id'];
            
            // Remove from wishlist
            if ($wishlistModel->removeFromWishlist($loggedInUserId, $internshipId)) {
                $successMessage = "Internship removed from your wishlist.";
                header("Location: wishlistController.php?action=view&success=" . urlencode($successMessage));
            } else {
                $errorMessage = $wishlistModel->getError() ?: "Failed to remove from wishlist.";
                header("Location: wishlistController.php?action=view&error=" . urlencode($errorMessage));
            }
            exit();
            break;

        default:
            $errorMessage = "Invalid action.";
            header("Location: ../View/student.php?error=" . urlencode($errorMessage));
            exit();
    }
} catch (Exception $e) {
    error_log("Unhandled Exception in wishlistController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $errorMessage = "An unexpected system error occurred. Please report this issue.";
    include __DIR__ . '/../View/wishlistView.php';
    exit();
}
?>