<?php
// Location: src/Controller/offerController.php

// Required includes
require_once __DIR__ . '/../../config/config.php'; // Make sure this sets up $conn
require_once __DIR__ . '/../Model/Internship.php';
require_once __DIR__ . '/../Model/company.php';     // Contains Company details AND rating methods now
require_once __DIR__ . '/../Model/Application.php'; // For applicant count
require_once __DIR__ . '/../Model/Wishlist.php';    // For wishlist status
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

// Check if $conn is set from config.php
if (!isset($conn) || !$conn) {
    // Consider a more user-friendly error page in production
    die("Database connection not established. Check config/config.php");
}

// Instantiate Models
$internshipModel = new Internship($conn);
$companyModel = new Company($conn); // Instantiated - Now includes rating methods
$applicationModel = new Application($conn);
$wishlistModel = null; // Initialize

// Instantiate models needed specifically for students
if ($loggedInUserRole === 'student' && $loggedInUserId) {
    $wishlistModel = new Wishlist($conn); // Instantiate only if student
    // No separate rating model needed anymore
}

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'Internship Offers';

// Get action from GET or POST
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

// Handle error/success messages from redirects or session
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
} elseif (isset($_SESSION['error_message'])) {
    $errorMessage = htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}

if (isset($_GET['success'])) {
    $successMessage = htmlspecialchars($_GET['success']);
} elseif (isset($_SESSION['success_message'])) {
    $successMessage = htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
}

// Capture the current request URI for redirects (e.g., after rating)
$requestUri = $_SERVER['REQUEST_URI'];
$_SESSION['rating_return_url'] = $requestUri; // Store for potential use after rating submission


try {
    switch ($action) {
        // View offers (for students)
        case 'view':
            if ($loggedInUserRole !== 'student') {
                // Redirect non-students
                header("Location: internshipController.php"); // Example redirect
                exit();
            }

            // Ensure loggedInUserId is valid for student actions
            if (!$loggedInUserId) {
                // Redirect or throw error if student ID is missing
                 header("Location: ../View/login.php?error=" . urlencode("Session expired or invalid. Please login again."));
                 exit();
            }

            $pageTitle = 'Available Internship Offers';

            // Handle search and sorting
            $search = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'])) : ''; // Sanitize search
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest'; // Default sort

            // Get all internships with company details
            $internships = $internshipModel->getAllInternshipsWithCompanyDetails($search, $sort);

            // --- Fetch Ratings, Applicant Count, and Wishlist Status ---
            if (!empty($internships)) {
                foreach ($internships as $key => $internship) {
                    $companyId = $internship['id_company'] ?? null;
                    $internshipId = $internship['id_internship'] ?? null;

                    // 1. Fetch Company Rating Info using Company Model
                    if ($companyId) {
                        // Use methods from Company model instance ($companyModel)
                        $ratingInfo = $companyModel->getCompanyAverageRating($companyId);
                        $hasRated = $companyModel->hasStudentRatedCompany($loggedInUserId, $companyId); // Pass student ID first as per your Company model method
                        $internships[$key]['rating_info'] = $ratingInfo;
                        $internships[$key]['student_has_rated'] = $hasRated;
                    } else {
                        // Default if company ID is missing
                        $internships[$key]['rating_info'] = ['average' => null, 'count' => 0];
                        $internships[$key]['student_has_rated'] = false;
                    }

                    // 2. Fetch Applicant Count using Application Model
                    if ($internshipId) {
                        $applicantCount = $applicationModel->countApplicationsForInternship((int)$internshipId);
                        $internships[$key]['applicant_count'] = $applicantCount; // Add count
                    } else {
                         $internships[$key]['applicant_count'] = 0; // Default if no internship ID
                    }

                    // 3. Wishlist Status Check (Handled in View using $wishlistModel)
                    // No action needed here, just ensure $wishlistModel is passed to view.
                }
            }
            // --- End Fetch Ratings, Applicant Count, and Wishlist Status ---

            // Pass necessary variables to the view
            // $conn is needed by the view for profile pic logic
            // $loggedInUserId is needed
            // $wishlistModel is needed for add/remove buttons
            // $companyModel might be needed if the view calls its methods directly (unlikely now)
            include __DIR__ . '/../View/viewOffersView.php';
            break;

        // Default action
        default:
            // Redirect based on role
            if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') {
                header("Location: internshipController.php"); // Example redirect for admin/pilote
            } elseif ($loggedInUserRole === 'student') {
                header("Location: offerController.php?action=view"); // Redirect students to view
            } else {
                 // Fallback for unknown roles or if role is not set
                 header("Location: ../View/login.php?error=" . urlencode("Invalid access level or unknown role."));
            }
            exit();
    }
} catch (Exception $e) {
    error_log("Unhandled Exception in offerController (Action: $action, UserID: $loggedInUserId, Role: $loggedInUserRole): " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Set a generic error message for the user
    $errorMessage = "An unexpected system error occurred. Please try again later or contact support if the problem persists.";

    // Attempt to show the error within the appropriate view if possible, otherwise show a generic error page/message.
    if ($action === 'view' && $loggedInUserRole === 'student' && !headers_sent()) {
        // Try to render the viewOffersView with the error message
        $internships = $internships ?? []; // Use previously fetched or empty array
        $pageTitle = $pageTitle ?? 'Error'; // Use existing or default title
        // Ensure required view variables are initialized
        $wishlistModel = $wishlistModel ?? null; // Ensure it's at least null
        include __DIR__ . '/../View/viewOffersView.php'; // Render view with error message
    } elseif (!headers_sent()) {
        // For other actions or roles, or if rendering the specific view fails, show a simpler error
        echo "<h1>Application Error</h1><p>" . htmlspecialchars($errorMessage) . "</p><p><a href='../View/student.php'>Go to Dashboard</a></p>"; // Provide a way back
    } else {
        // If headers are already sent, output a simple error message directly.
        echo "<br><strong>Critical Error:</strong> " . htmlspecialchars($errorMessage);
    }
    exit(); // Stop execution after handling the error
}
?>
