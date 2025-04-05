<?php
// Location: src/Controller/offerController.php

// Required includes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/company.php'; // Already included
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
$loggedInUserId = AuthSession::getUserData('user_id'); // Ensure this contains the student ID

// Instantiate Models
$internshipModel = new Internship($conn);
$companyModel = new Company($conn); // Already instantiated

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

// Capture the current request URI for redirects after rating
$requestUri = $_SERVER['REQUEST_URI'];
// Store it in session to use after rating submission redirect
$_SESSION['rating_return_url'] = $requestUri;


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

            // --- START NEW CODE: Fetch Ratings ---
            if (!empty($internships) && $loggedInUserId) {
                foreach ($internships as $key => $internship) {
                    // Ensure company ID exists
                    if (isset($internship['id_company'])) {
                        $companyId = $internship['id_company'];

                        // Get average rating and count
                        $ratingInfo = $companyModel->getCompanyAverageRating($companyId);
                        $internships[$key]['rating_info'] = $ratingInfo; // Add to internship array

                        // Check if the current student has already rated this company
                        $hasRated = $companyModel->hasStudentRatedCompany($loggedInUserId, $companyId);
                        $internships[$key]['student_has_rated'] = $hasRated; // Add to internship array
                    } else {
                        // Handle case where company ID might be missing (shouldn't happen with JOIN)
                        $internships[$key]['rating_info'] = ['average' => null, 'count' => 0];
                        $internships[$key]['student_has_rated'] = false; // Assume not rated if no company ID
                    }
                }
            }
            // --- END NEW CODE ---

            include __DIR__ . '/../View/viewOffersView.php';
            break;

        // Default action for admin/pilote is to redirect to internshipController
        default:
            if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') {
                header("Location: internshipController.php");
            } else {
                // Redirect students to view action if no valid action specified
                header("Location: offerController.php?action=view");
            }
            exit();
    }
} catch (Exception $e) {
    error_log("Unhandled Exception in offerController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    // Avoid including the view again if headers already sent or if it's not appropriate
    // Maybe redirect to an error page or show a generic error
    if (!headers_sent()) {
         // Redirect to a safe page or show error differently
         // For now, keep original behavior but be aware it might redisplay the view in a broken state
         $errorMessage = "An unexpected system error occurred. Please report this issue.";
         // Decide where to show the error, including viewOffersView might not be ideal here
         // Depending on where the exception occurred. Let's assume viewOffersView is okay for now.
         if ($action === 'view' && $loggedInUserRole === 'student') {
             // Ensure $internships is initialized if the exception happened before fetching
             $internships = $internships ?? [];
             include __DIR__ . '/../View/viewOffersView.php';
         } else {
             // Handle error for other roles/actions appropriately
             echo "An error occurred."; // Simple fallback
         }
    } else {
        echo "An critical error occurred. Please contact support."; // Fallback if headers sent
    }
    exit();
}
?>
