<?php
// Location: src/Controller/offerController.php

// Required includes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/company.php';
require_once __DIR__ . '/../Model/Application.php'; // *** ADDED: Need Application model for counts ***
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
try {
    $internshipModel = new Internship($conn);
    $companyModel = new Company($conn);
    $applicationModel = new Application($conn); // *** ADDED: Instantiate Application model ***
} catch (Exception $e) {
     error_log("FATAL offerController: Model instantiation error: " . $e->getMessage());
     // Display a generic error, don't show specific model errors to users
     die("A critical error occurred setting up the offers page. Please try again later.");
}


// For student view, we need the wishlist model (Keep this if used in viewOffersView.php)
$wishlistModel = null; // Initialize
if ($loggedInUserRole === 'student' && $loggedInUserId) {
    require_once __DIR__ . '/../Model/Wishlist.php';
    try {
        $wishlistModel = new Wishlist($conn);
    } catch (Exception $e) {
        error_log("OfferController: Failed to instantiate Wishlist model: " . $e->getMessage());
        // Non-fatal, proceed without wishlist functionality if needed
    }
}

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'Internship Offers';
$internships = []; // Initialize internships array

// Get action from GET or POST
$action = $_GET['action'] ?? ($_POST['action'] ?? 'view'); // Default to 'view'

// Handle error/success messages from redirects
if (isset($_GET['error'])) {
    // Sanitize error message coming from URL
    $errorMessage = htmlspecialchars(urldecode($_GET['error']));
}
if (isset($_GET['success'])) {
     // Sanitize success message coming from URL
    $successMessage = htmlspecialchars(urldecode($_GET['success']));
}

try {
    switch ($action) {
        // View offers (primarily for students, but might be viewable by others?)
        case 'view':
            // This action might be accessible to others, but apply button only shows for students in the view
            $pageTitle = 'Available Internship Offers';

            // Handle search and sorting
            $search = trim($_GET['search'] ?? ''); // Trim whitespace
            $sort = $_GET['sort'] ?? 'newest'; // Default sort order

            // Get all internships with company details
            // This method should ideally join company data
            $internshipsData = $internshipModel->getAllInternshipsWithCompanyDetails($search, $sort);

            if ($internshipsData === false) {
                $errorMessage = $internshipModel->getError() ?: "Error fetching internship offers.";
                $internships = []; // Ensure it's an empty array on error
            } else {
                $internships = $internshipsData; // Assign fetched data

                // *** NEW: Loop through internships to add counts and ratings ***
                foreach ($internships as $key => &$internship) { // Use reference '&' to modify directly

                    // 1. Get Application Count for this internship
                    if (isset($internship['id_internship'])) {
                        $appCountResult = $applicationModel->countApplicationsForInternship($internship['id_internship']);
                        // Assign count, default to 0 if fetch fails (false)
                        $internship['application_count'] = ($appCountResult !== false) ? (int)$appCountResult : 0;
                    } else {
                        $internship['application_count'] = 0; // Default if no ID
                    }

                    // 2. Get Company Rating (if company ID exists in the fetched data)
                    if (isset($internship['id_company'])) {
                        $ratingResult = $companyModel->getCompanyAverageRating($internship['id_company']);
                        // Add rating details to the internship array
                        $internship['company_average_rating'] = $ratingResult['average']; // null if no ratings
                        $internship['company_rating_count'] = $ratingResult['count'];   // 0 if no ratings
                    } else {
                         // Set defaults if no company ID found for this internship
                         $internship['company_average_rating'] = null;
                         $internship['company_rating_count'] = 0;
                    }

                    // 3. (Optional) Check if current student has added to wishlist
                    if ($loggedInUserRole === 'student' && $wishlistModel && $loggedInUserId && isset($internship['id_internship'])) {
                         $internship['is_in_wishlist'] = $wishlistModel->isInWishlist($loggedInUserId, $internship['id_internship']);
                    } else {
                         $internship['is_in_wishlist'] = false;
                    }
                }
                unset($internship); // *** IMPORTANT: Unset the reference after the loop ***
            }

            // Include the view - it will now have access to the modified $internships array
            include __DIR__ . '/../View/viewOffersView.php';
            break;

        // *** ADD action 'detail' if you want a separate detail page ***
        // case 'detail':
            // if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) { /* error */ }
            // $internshipId = (int)$_GET['id'];
            // $internshipDetails = $internshipModel->readInternship($internshipId); // Fetch single internship
            // if (!$internshipDetails) { /* error */ }
            // // Fetch rating/counts for this specific internship's company
            // $appCount = $applicationModel->countApplicationsForInternship($internshipId);
            // $ratingInfo = $companyModel->getCompanyAverageRating($internshipDetails['id_company']);
            // $pageTitle = "Internship Details: " . htmlspecialchars($internshipDetails['title'] ?? '');
            // include __DIR__ . '/../View/offerDetailView.php'; // Create this view
            // break;


        // Default action for non-student roles (e.g., admin/pilote) - redirect to their management view
        default:
            if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') {
                // Redirect admin/pilote to the internship management controller
                header("Location: internshipController.php");
            } else {
                // Redirect any other unexpected roles (or logged-out users caught late) back to the offers view
                header("Location: offerController.php?action=view");
            }
            exit();
    }
} catch (Exception $e) {
    error_log("Unhandled Exception in offerController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $errorMessage = "An unexpected system error occurred. Please report this issue.";
    // Attempt to show the view with an error message, ensure $internships is an empty array
    $internships = [];
    $pageTitle = 'Error'; // Update title
    include __DIR__ . '/../View/viewOffersView.php'; // Render view even on error
    exit();
}
?>