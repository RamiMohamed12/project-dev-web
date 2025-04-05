<?php
// Location: /home/demy/project-dev-web/src/Controller/internshipController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';         // DB Connection ($conn)
require_once __DIR__ . '/../Model/Internship.php';   // Internship Model
require_once __DIR__ . '/../Model/company.php';      // Company Model (needed for dropdowns & auth)
require_once __DIR__ . '/../Auth/AuthSession.php';     // Session utilities

// Start session if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth Check & Role/ID ---
if (!AuthSession::isUserLoggedIn()) { header("Location: ../View/login.php?error=" . urlencode("Authentication required.")); exit(); }
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Authorization: Only Admins, Pilotes, and Students can access internships ---
if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote' && $loggedInUserRole !== 'student') {
    AuthSession::destroySession(); // Log out unauthorized users
    header("Location: ../View/login.php?error=" . urlencode("Access Denied: Insufficient privileges."));
    exit();
}

// --- Instantiate Models ---
try {
    $internshipModel = new Internship($conn);
    $companyModel = new Company($conn); // Needed for company list & checking ownership
} catch (Exception $e) {
    error_log("Internship Controller: Failed to instantiate models: " . $e->getMessage());
    die("A critical error occurred setting up internship management (ICM).");
}

// --- Determine Action ---
$action = $_GET['action'] ?? 'list'; // Default action is to show the list
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action']; // Override with POST action if present
}

// If student is trying to access management functions, redirect to view
if ($loggedInUserRole === 'student' && !in_array($action, ['view', 'list'])) { // Allow list for potential future student views maybe? Redirecting non-view actions.
    $action = 'view';
}
// If admin/pilote requests view explicitly, let them, otherwise default to list
elseif (($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') && $action === 'view') {
   $action = 'list'; // Redirect management roles from 'view' to 'list'
}


// --- Initialize Variables for Views ---
$internships = []; $internshipDetails = null; $companiesList = [];
$pageTitle = "Manage Internships"; // Default title
$errorMessage = ''; $successMessage = '';

// Populate messages from GET parameters (after redirects)
if(isset($_GET['update']) && $_GET['update'] == 'success') { $successMessage = "Internship updated successfully."; }
if(isset($_GET['delete']) && $_GET['delete'] == 'success') { $successMessage = "Internship deleted successfully."; }
if(isset($_GET['add']) && $_GET['add'] == 'success') { $successMessage = "Internship added successfully."; }
if(isset($_GET['error'])) { $errorMessage = htmlspecialchars(urldecode($_GET['error'])); }


// --- Action Handling using switch ---
try { // Wrap main logic in try-catch

    switch ($action) {

        // --- CASE: Add New Internship (Processing POST data) ---
        case 'add':
            // Ensure only admin/pilote can add
            if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
                 header("Location: internshipController.php?action=view&error=" . urlencode("Permission Denied (add).")); exit();
            }
            if ($_SERVER["REQUEST_METHOD"] !== "POST") { $action = 'list'; break; /* Only process POST */ }

            $id_company = filter_input(INPUT_POST, 'id_company', FILTER_VALIDATE_INT);
            $title = $_POST['title'] ?? ''; $description = $_POST['description'] ?? '';
            $remuneration = $_POST['remuneration'] ?? null; $offre_date = $_POST['offre_date'] ?? '';

            if ($remuneration !== null && $remuneration !== '' && !is_numeric($remuneration)) { $errorMessage = "Remuneration invalid."; break; }
            if ($remuneration === '') $remuneration = null;
            if (!$id_company || empty($title) || empty($description) || empty($offre_date)) { $errorMessage = "Company, Title, Description, Offer Date required."; break; }

            // Authorization Check (Pilote can only add to companies they manage)
            $canAddForCompany = false;
            if ($loggedInUserRole === 'admin') { $canAddForCompany = true; }
            else { // Role is pilote
                $companyDetails = $companyModel->read($id_company);
                if ($companyDetails && isset($companyDetails['created_by_pilote_id']) && $companyDetails['created_by_pilote_id'] == $loggedInUserId) { $canAddForCompany = true; }
                else { $errorMessage = "Selected company not found or not managed by you."; }
            }

            if ($canAddForCompany) {
                $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;
                $result = $internshipModel->create($id_company, $title, $description, $remuneration, $offre_date, $creatorPiloteId);
                if ($result) { header("Location: internshipController.php?action=list&add=success"); exit(); }
                else { $errorMessage = $internshipModel->getError() ?: "Error adding internship."; }
            } elseif (empty($errorMessage)) { $errorMessage = "Permission denied for this company."; }

            $action = 'list'; // Fall through to list view to show error
            break;

        // --- CASE: Display Edit Form (GET request) ---
        case 'edit':
            // Ensure only admin/pilote can edit
            if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
                 header("Location: internshipController.php?action=view&error=" . urlencode("Permission Denied (edit form).")); exit();
            }
            if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
                header("Location: internshipController.php?action=list&error=invalid_edit_req"); exit();
            }

            $idToEdit = (int)$_GET['id'];
            // Fetch internship details, including creator IDs if possible (model needs to support this)
            $internshipDetails = $internshipModel->readInternship($idToEdit); // Assumes this method joins company and fetches needed IDs

            if (!$internshipDetails) {
                header("Location: internshipController.php?action=list&error=not_found"); exit();
            }

            // --- Authorization check ---
            $canModify = false;
            if ($loggedInUserRole === 'admin') {
                $canModify = true;
            } elseif ($loggedInUserRole === 'pilote') {
                // Check 1: Did the pilote create the COMPANY associated with the internship?
                if (isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] == $loggedInUserId) {
                    $canModify = true;
                }
                // Check 2: Did the pilote create the INTERNSHIP itself?
                // Use 'created_by_pilote_id' field from the internship table
                elseif (isset($internshipDetails['created_by_pilote_id']) && $internshipDetails['created_by_pilote_id'] == $loggedInUserId) {
                    $canModify = true;
                }
            }
            // --- End Authorization Check ---


            if (!$canModify) {
                header("Location: internshipController.php?action=list&error=" . urlencode("Permission denied (edit).")); exit();
            }

            // Fetch companies for dropdown (filtered for pilote)
             if ($loggedInUserRole === 'admin') { $companiesList = $companyModel->readAll(); } else { $companiesList = $companyModel->readAll($loggedInUserId); }
             if ($companiesList === false) { $errorMessage = $companyModel->getError() ?: "Error fetching company list."; $companiesList = []; }

            $pageTitle = "Edit Internship Offer";
            include __DIR__ . '/../View/editInternshipView.php'; // Load edit view
            exit();

        // --- CASE: Update Internship (Processing POST data) ---
        case 'update':
             // Ensure only admin/pilote can update
             if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
                  header("Location: internshipController.php?action=view&error=" . urlencode("Permission Denied (update).")); exit();
             }
             if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_internship']) || !filter_var($_POST['id_internship'], FILTER_VALIDATE_INT)) {
                 header("Location: internshipController.php?action=list&error=invalid_update_req"); exit();
             }

             $idToUpdate = (int)$_POST['id_internship'];
             $originalInternship = $internshipModel->readInternship($idToUpdate); // Re-fetch for auth check
             if (!$originalInternship) {
                 header("Location: internshipController.php?action=list&error=not_found_update"); exit();
             }

             // --- Auth check based on original record ---
             $canModify = false;
             if ($loggedInUserRole === 'admin') {
                 $canModify = true;
             } elseif ($loggedInUserRole === 'pilote') {
                 // Check 1: Did the pilote create the COMPANY associated with the internship?
                 if (isset($originalInternship['company_creator_id']) && $originalInternship['company_creator_id'] == $loggedInUserId) {
                     $canModify = true;
                 }
                 // Check 2: Did the pilote create the INTERNSHIP itself?
                 elseif (isset($originalInternship['created_by_pilote_id']) && $originalInternship['created_by_pilote_id'] == $loggedInUserId) {
                     $canModify = true;
                 }
             }
             // --- End Auth Check ---

             if (!$canModify) {
                 header("Location: internshipController.php?action=list&error=" . urlencode("Permission denied (update).")); exit();
             }

             // Get POST data
             $id_company = filter_input(INPUT_POST, 'id_company', FILTER_VALIDATE_INT);
             $title = $_POST['title'] ?? ''; $description = $_POST['description'] ?? '';
             $remuneration = $_POST['remuneration'] ?? null; $offre_date = $_POST['offre_date'] ?? '';
             if ($remuneration !== null && $remuneration !== '' && !is_numeric($remuneration)) { $errorMessage = "Remuneration invalid."; }
             if ($remuneration === '') $remuneration = null;
             if (!$id_company || empty($title) || empty($description) || empty($offre_date)) { $errorMessage = "Required fields missing."; }

             // Pilote check: Ensure NEW company (if changed) belongs to them
             if (empty($errorMessage) && $loggedInUserRole === 'pilote') {
                 $newCompanyDetails = $companyModel->read($id_company);
                 if (!$newCompanyDetails || !isset($newCompanyDetails['created_by_pilote_id']) || $newCompanyDetails['created_by_pilote_id'] != $loggedInUserId) {
                     $errorMessage = "Selected company not managed by you.";
                 }
             }

             if (empty($errorMessage)) {
                 $result = $internshipModel->updateInternship($idToUpdate, $id_company, $title, $description, $remuneration, $offre_date);
                 if ($result) { header("Location: internshipController.php?action=list&update=success"); exit(); }
                 else { $errorMessage = $internshipModel->getError() ?: "Error updating internship."; }
             }

             // If error, re-display edit form
             $internshipDetails = $originalInternship; // Show original data structure
             // Keep user's attempted changes for repopulating form
             $internshipDetails['title'] = $title;
             $internshipDetails['description'] = $description;
             $internshipDetails['remuneration'] = $remuneration;
             $internshipDetails['offre_date'] = $offre_date;
             $internshipDetails['id_company'] = $id_company; // Keep selected company

             // Re-fetch companies for dropdown
             if ($loggedInUserRole === 'admin') { $companiesList = $companyModel->readAll(); } else { $companiesList = $companyModel->readAll($loggedInUserId); }
             if ($companiesList === false) { $errorMessage .= " (Err fetch companies)"; $companiesList = []; }
             $pageTitle = "Edit Internship Offer";
             include __DIR__ . '/../View/editInternshipView.php';
             exit();


        // --- CASE: Delete Internship (Processing POST data) ---
        case 'delete':
             // Ensure only admin/pilote can delete
             if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
                  header("Location: internshipController.php?action=view&error=" . urlencode("Permission Denied (delete).")); exit();
             }
             if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
                 header("Location: internshipController.php?action=list&error=invalid_delete_req"); exit();
             }

             $idToDelete = (int)$_POST['id'];
             $internshipToDelete = $internshipModel->readInternship($idToDelete); // Fetch to check ownership
             if (!$internshipToDelete) {
                 header("Location: internshipController.php?action=list&error=not_found_delete"); exit();
             }

             // --- Auth check ---
             $canDelete = false;
             if ($loggedInUserRole === 'admin') {
                 $canDelete = true;
             } elseif ($loggedInUserRole === 'pilote') {
                 // Check 1: Did pilote create the COMPANY?
                 if (isset($internshipToDelete['company_creator_id']) && $internshipToDelete['company_creator_id'] == $loggedInUserId) {
                     $canDelete = true;
                 }
                 // Check 2: Did pilote create the INTERNSHIP?
                 elseif (isset($internshipToDelete['created_by_pilote_id']) && $internshipToDelete['created_by_pilote_id'] == $loggedInUserId) {
                     $canDelete = true;
                 }
             }
            // --- End Auth Check ---

             if (!$canDelete) {
                 header("Location: internshipController.php?action=list&error=" . urlencode("Permission denied (delete).")); exit();
             }

             // Delete
             $result = $internshipModel->delete($idToDelete);
             if ($result) { header("Location: internshipController.php?action=list&delete=success"); exit(); }
             else { $errorMsg = $internshipModel->getError() ?: "Could not delete internship."; header("Location: internshipController.php?action=list&error=" . urlencode($errorMsg)); exit(); }
             break;


        // --- CASE: View Internships (for Students) ---
        case 'view':
             // This case is primarily for students, but accessible to all logged-in users
            $pageTitle = "Available Internship Offers";

            // Fetch all active internship offers for students
            // Consider if students should see ALL or just "active" ones based on a status field if you add one
            $internships = $internshipModel->readAll(); // Fetch all internships with company details
            if ($internships === false) {
                $errorMessage = $internshipModel->getError() ?: "Error fetching internship offers.";
                $internships = [];
            }

            // Include the student view for internships
            include __DIR__ . '/../View/viewOffersView.php';
            exit();

        // --- CASE: Default/List View (GET for Admin/Pilote) ---
        case 'list':
        default:
             // This case is for Admin/Pilote management
             if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
                 // If a student somehow requests action=list, redirect them to view
                 header("Location: internshipController.php?action=view");
                 exit();
             }

             $pageTitle = ($loggedInUserRole === 'admin') ? "Manage All Internships" : "Manage My Internships";

             // Fetch companies for add form dropdown (filtered for pilote)
             if ($loggedInUserRole === 'admin') { $companiesList = $companyModel->readAll(); }
             else { $companiesList = $companyModel->readAll($loggedInUserId); }
             if ($companiesList === false) { $errorMessage .= ($errorMessage ? ' ' : '') . ($companyModel->getError() ?: "Error fetch companies."); $companiesList = []; }

             // Fetch internships: Admin sees all, Pilote sees internships linked to companies they manage
             // OR internships they created directly.
             // The readAll model method might need adjustment if Pilote should see internships they created
             // even if the company is managed by someone else (or admin).
             // Current logic fetches based on COMPANY ownership only for pilotes.
             $allowedCompanyIds = null;
             $creatorPiloteIdForFilter = null; // We might need this later

             if ($loggedInUserRole === 'pilote') {
                 // Option 1: Filter strictly by companies the pilote manages
                 // if ($companiesList === false || empty($companiesList)) {
                 //     $allowedCompanyIds = [-1]; // No companies owned, fetch none via company ID
                 // } else {
                 //     $allowedCompanyIds = array_column($companiesList, 'id_company');
                 // }
                 // $internships = $internshipModel->readAll($allowedCompanyIds); // Pass company IDs

                 // Option 2: Fetch ALL internships and filter in PHP (less efficient for large datasets)
                 // $allInternships = $internshipModel->readAll();
                 // $internships = [];
                 // if (is_array($allInternships)) {
                 //     foreach ($allInternships as $internship) {
                 //         if ((isset($internship['company_creator_id']) && $internship['company_creator_id'] == $loggedInUserId) ||
                 //             (isset($internship['created_by_pilote_id']) && $internship['created_by_pilote_id'] == $loggedInUserId)) {
                 //             $internships[] = $internship;
                 //         }
                 //     }
                 // }

                 // Option 3 (Recommended): Modify Internship::readAll to accept pilote ID
                 // Fetch internships where company is managed OR internship was created by this pilote
                 $internships = $internshipModel->readAllForPilote($loggedInUserId); // Assumes method exists or is added to Model

             } else { // Admin sees all
                 $internships = $internshipModel->readAll(); // Admin uses the regular readAll
             }


             if ($internships === false) { $errorMessage .= ($errorMessage ? ' ' : '') . ($internshipModel->getError() ?: "Error fetching internships."); $internships = []; }

             // Include the Admin/Pilote list view
             include __DIR__ . '/../View/manageInternshipsView.php';
             exit();

    } // End switch ($action)

} catch (Exception $e) {
     error_log("Unhandled Exception in internshipController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
     $errorMessage = "An unexpected system error occurred. Please report this issue.";
     // Determine which view to show based on role
     if ($loggedInUserRole === 'student') {
         include __DIR__ . '/../View/viewOffersView.php';
     } else {
         include __DIR__ . '/../View/manageInternshipsView.php';
     }
     exit();
}
?>