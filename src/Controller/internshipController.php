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
if ($loggedInUserRole === 'student' && $action !== 'view') {
    $action = 'view';
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
            if ($_SERVER["REQUEST_METHOD"] !== "POST") { $action = 'list'; break; /* Only process POST */ }

            $id_company = filter_input(INPUT_POST, 'id_company', FILTER_VALIDATE_INT);
            $title = $_POST['title'] ?? ''; $description = $_POST['description'] ?? '';
            $remuneration = $_POST['remuneration'] ?? null; $offre_date = $_POST['offre_date'] ?? '';

            if ($remuneration !== null && $remuneration !== '' && !is_numeric($remuneration)) { $errorMessage = "Remuneration invalid."; break; }
            if ($remuneration === '') $remuneration = null;
            if (!$id_company || empty($title) || empty($description) || empty($offre_date)) { $errorMessage = "Company, Title, Description, Offer Date required."; break; }

            // Authorization Check
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
            if ($_SERVER["REQUEST_METHOD"] !== "GET" || !isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) { header("Location: internshipController.php?action=list&error=invalid_edit_req"); exit(); }

            $idToEdit = (int)$_GET['id'];
            $internshipDetails = $internshipModel->readInternship($idToEdit); // Fetches joined company data too

            if (!$internshipDetails) { header("Location: internshipController.php?action=list&error=not_found"); exit(); }

            // Authorization check
            $canModify = false;
            if ($loggedInUserRole === 'admin') { $canModify = true; }
            elseif ($loggedInUserRole === 'pilote' && isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] == $loggedInUserId) { $canModify = true; }

            if (!$canModify) { header("Location: internshipController.php?action=list&error=" . urlencode("Permission denied (edit).")); exit(); }

            // Fetch companies for dropdown
             if ($loggedInUserRole === 'admin') { $companiesList = $companyModel->readAll(); } else { $companiesList = $companyModel->readAll($loggedInUserId); } // Pilote sees own companies
             if ($companiesList === false) { $errorMessage = $companyModel->getError() ?: "Error fetching company list."; $companiesList = []; }

            $pageTitle = "Edit Internship Offer";
            include __DIR__ . '/../View/editInternshipView.php'; // Load edit view
            exit();

        // --- CASE: Update Internship (Processing POST data) ---
        case 'update':
             if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_internship']) || !filter_var($_POST['id_internship'], FILTER_VALIDATE_INT)) { header("Location: internshipController.php?action=list&error=invalid_update_req"); exit(); }

             $idToUpdate = (int)$_POST['id_internship'];
             $originalInternship = $internshipModel->readInternship($idToUpdate); // Re-fetch for auth check
             if (!$originalInternship) { header("Location: internshipController.php?action=list&error=not_found_update"); exit(); }

             // Auth check based on original record
             $canModify = false;
             if ($loggedInUserRole === 'admin') { $canModify = true; }
             elseif ($loggedInUserRole === 'pilote' && isset($originalInternship['company_creator_id']) && $originalInternship['company_creator_id'] == $loggedInUserId) { $canModify = true; }
             // Add check for internship creator
             elseif ($loggedInUserRole === 'pilote' && isset($originalInternship['created_by_pilote_id']) && $originalInternship['created_by_pilote_id'] == $loggedInUserId) { $canModify = true; }
             
             if (!$canModify) { header("Location: internshipController.php?action=list&error=" . urlencode("Permission denied (update).")); exit(); }

             // Get POST data
             $id_company = filter_input(INPUT_POST, 'id_company', FILTER_VALIDATE_INT);
             $title = $_POST['title'] ?? ''; $description = $_POST['description'] ?? '';
             $remuneration = $_POST['remuneration'] ?? null; $offre_date = $_POST['offre_date'] ?? '';
             if ($remuneration !== null && $remuneration !== '' && !is_numeric($remuneration)) { $errorMessage = "Remuneration invalid."; }
             if ($remuneration === '') $remuneration = null;
             if (!$id_company || empty($title) || empty($description) || empty($offre_date)) { $errorMessage = "Required fields missing."; }

             // Pilote check: Ensure NEW company belongs to them
             if (empty($errorMessage) && $loggedInUserRole === 'pilote') {
                 $newCompanyDetails = $companyModel->read($id_company);
                 if (!$newCompanyDetails || !isset($newCompanyDetails['created_by_pilote_id']) || $newCompanyDetails['created_by_pilote_id'] != $loggedInUserId) { $errorMessage = "Selected company not managed by you."; }
             }

             if (empty($errorMessage)) {
                 $result = $internshipModel->updateInternship($idToUpdate, $id_company, $title, $description, $remuneration, $offre_date);
                 if ($result) { header("Location: internshipController.php?action=list&update=success"); exit(); }
                 else { $errorMessage = $internshipModel->getError() ?: "Error updating internship."; }
             }

             // If error, re-display edit form
             $internshipDetails = $originalInternship; // Show original data on error form
             $internshipDetails['title'] = $title; // Keep user's attempted changes
             $internshipDetails['description'] = $description;
             $internshipDetails['remuneration'] = $remuneration;
             $internshipDetails['offre_date'] = $offre_date;
             $internshipDetails['id_company'] = $id_company; // Keep selected company

             if ($loggedInUserRole === 'admin') { $companiesList = $companyModel->readAll(); } else { $companiesList = $companyModel->readAll($loggedInUserId); }
             if ($companiesList === false) { $errorMessage .= " (Err fetch companies)"; $companiesList = []; }
             $pageTitle = "Edit Internship Offer";
             include __DIR__ . '/../View/editInternshipView.php';
             exit();


        // --- CASE: Delete Internship (Processing POST data) ---
        case 'delete':
             if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') { /* Auth fail handled above */ }
             if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) { header("Location: internshipController.php?action=list&error=invalid_delete_req"); exit(); }

             $idToDelete = (int)$_POST['id'];
             $internshipToDelete = $internshipModel->readInternship($idToDelete); // Fetch to check ownership
             if (!$internshipToDelete) { header("Location: internshipController.php?action=list&error=not_found_delete"); exit(); }

             // Auth check
             $canDelete = false;
             if ($loggedInUserRole === 'admin') { $canDelete = true; }
             elseif ($loggedInUserRole === 'pilote' && isset($internshipToDelete['company_creator_id']) && $internshipToDelete['company_creator_id'] == $loggedInUserId) { $canDelete = true; }
             // Add check for internship creator
             elseif ($loggedInUserRole === 'pilote' && isset($internshipToDelete['created_by_pilote_id']) && $internshipToDelete['created_by_pilote_id'] == $loggedInUserId) { $canDelete = true; }
             
             if (!$canDelete) { header("Location: internshipController.php?action=list&error=" . urlencode("Permission denied (delete).")); exit(); }

             // Delete
             $result = $internshipModel->delete($idToDelete);
             if ($result) { header("Location: internshipController.php?action=list&delete=success"); exit(); }
             else { $errorMsg = $internshipModel->getError() ?: "Could not delete internship."; header("Location: internshipController.php?action=list&error=" . urlencode($errorMsg)); exit(); }
             break;


        // --- CASE: View Internships (for Students) ---
        case 'view':
            $pageTitle = "Available Internship Offers";
            
            // Fetch all active internship offers for students
            $internships = $internshipModel->readAll(); // Using readAll instead of readAllActive
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
             if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
                 // If a student somehow requests action=list, redirect them
                 header("Location: internshipController.php?action=view");
                 exit();
             }

             $pageTitle = ($loggedInUserRole === 'admin') ? "Manage All Internships" : "Manage My Internships";

             // Fetch companies for add form dropdown
             if ($loggedInUserRole === 'admin') { $companiesList = $companyModel->readAll(); }
             else { $companiesList = $companyModel->readAll($loggedInUserId); }
             if ($companiesList === false) { $errorMessage .= ($errorMessage ? ' ' : '') . ($companyModel->getError() ?: "Error fetch companies."); $companiesList = []; }

             // Fetch internships based on company ownership for pilotes
             $allowedCompanyIds = null;
             if ($loggedInUserRole === 'pilote') {
                 if ($companiesList === false || empty($companiesList)) { $allowedCompanyIds = [-1]; } // No companies owned, fetch none
                 else { $allowedCompanyIds = array_column($companiesList, 'id_company'); }
             } // Admin ($allowedCompanyIds = null) sees all

             $internships = $internshipModel->readAll($allowedCompanyIds);
             if ($internships === false) { $errorMessage = $internshipModel->getError() ?: "Error fetching internships."; $internships = []; }

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
