<?php
// Location: /home/demy/project-dev-web/src/Controller/editCompany.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php'; // Use Company model
require_once __DIR__ . '/../Auth/AuthSession.php';
// require_once __DIR__ . '/../Auth/AuthCheck.php'; // Specific check done below

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Basic Auth Check & Role/ID ---
if (!AuthSession::isUserLoggedIn()) { header("Location: ../View/login.php?error=" . urlencode("Auth required.")); exit(); }
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Instantiate Model ---
try { $companyModel = new Company($conn); }
catch (Exception $e) { error_log("EditCompany model error: " . $e->getMessage()); die("Critical error (ECM)."); }

// --- Get Target Company ID ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) { header("Location: companyController.php?error=missing_id"); exit(); }
$targetCompanyId = (int) $_GET['id'];

// --- Fetch Target Company Details ---
$companyDetails = null;
try { $companyDetails = $companyModel->read($targetCompanyId); }
catch (Exception $e) { error_log("Error fetching company ID {$targetCompanyId}: " . $e->getMessage()); header("Location: companyController.php?error=fetch_failed"); exit(); }
if (!$companyDetails) { header("Location: companyController.php?error=not_found"); exit(); }

// --- Authorization Check ---
$canEdit = false;
if ($loggedInUserRole === 'admin') { $canEdit = true; }
elseif ($loggedInUserRole === 'pilote' && isset($companyDetails['created_by_pilote_id']) && $companyDetails['created_by_pilote_id'] == $loggedInUserId) { $canEdit = true; }
if (!$canEdit) { header("Location: companyController.php?error=" . urlencode("Permission denied to edit this company.")); exit(); }


// --- Handle Form Submission (POST) ---
$errorMessage = ''; $successMessage = ''; $result = null; // Init vars
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {

    if (!$canEdit) { $errorMessage = "Authorization failed during update."; }
    else {
        // --- Init picture vars ---
        $pictureData = null; $pictureMime = null;
        $removePicture = isset($_POST['remove_company_pic']) && $_POST['remove_company_pic'] == '1';

        // --- Process File Upload ---
        if (!$removePicture && isset($_FILES['company_picture']) && $_FILES['company_picture']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['company_picture']['tmp_name'];
            $fileSize = $_FILES['company_picture']['size'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if ($fileSize == 0) { $errorMessage = "Error: Uploaded company picture file is empty."; }
            elseif ($fileSize > $maxFileSize) { $errorMessage = "Error: Company picture file is too large (Max 2MB)."; }
            else {
                $fileMimeType = mime_content_type($fileTmpPath);
                if ($fileMimeType && in_array($fileMimeType, $allowedMimeTypes)) {
                    $pictureData = file_get_contents($fileTmpPath);
                    if ($pictureData === false) { $errorMessage = "Error: Could not read uploaded picture file."; $pictureData = null; }
                    else { $pictureMime = $fileMimeType; error_log("Company picture uploaded for ID {$targetCompanyId}");}
                } else { $errorMessage = "Error: Invalid file type for company picture (JPG, PNG, GIF, WebP allowed). Detected: " . ($fileMimeType ? htmlspecialchars($fileMimeType) : 'Unknown'); }
            }
        } elseif (isset($_FILES['company_picture']) && $_FILES['company_picture']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['company_picture']['error'] != UPLOAD_ERR_OK) {
             $errorMessage = "Error uploading company picture. Code: " . $_FILES['company_picture']['error'];
             error_log("Company picture upload error for ID {$targetCompanyId}: " . $_FILES['company_picture']['error']);
        }
        // --- End File Upload ---


        // --- Process other form fields if no critical upload error ---
        if (empty($errorMessage)) {
            $name = $_POST['name'] ?? ''; $location = $_POST['location'] ?? '';
            $description = $_POST['description'] ?? ''; $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? ''; $url = $_POST['url'] ?? null; // Get URL

            // Validation
            if (empty($name) || empty($location) || empty($email) || empty($phone)) { $errorMessage = "Error: Name, Location, Email, and Phone are required."; }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errorMessage = "Error: Invalid email format."; }
            elseif (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) { $errorMessage = "Error: Invalid URL format."; }
            elseif (!preg_match('/^\+?[0-9\s\-()]+$/', $phone)) { $errorMessage = "Error: Invalid phone number format."; }
            else {
                // Call Update Model
                try {
                    // Pass URL, picture data, mime type, and remove flag to model
                    $result = $companyModel->update(
                        $targetCompanyId, $name, $location, $description, $email, $phone,
                        $url, $pictureData, $pictureMime, $removePicture
                    );
                } catch (Exception $e) {
                     error_log("Exception during company update (ID: {$targetCompanyId}): " . $e->getMessage());
                     $errorMessage = $companyModel->getError() ?: "An unexpected error occurred during the update.";
                     $result = false;
                }

                // Handle Result
                if ($result) {
                    header("Location: companyController.php?update=success"); // Redirect back to list
                    exit();
                } elseif (empty($errorMessage)) { // If update failed but no specific error
                    $errorMessage = $companyModel->getError() ?: "Error: Failed to update company details.";
                }
            } // End validation
        } // End if no upload error
    } // End auth check for POST
} // End POST request

// --- Prepare Data for View Display ---
// Re-fetch if update failed to show potentially correct preview (esp. for picture)
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($result === false || $result === null) && !empty($errorMessage)) {
    error_log("Update failed for company {$targetCompanyId}. Re-fetching details.");
    try {
        $freshDetails = $companyModel->read($targetCompanyId);
        if ($freshDetails) {
            $companyDetails = $freshDetails; // Overwrite with fresh data
        } else {
             error_log("Failed to re-fetch company details after failed update for ID {$targetCompanyId}.");
             // Keep existing $companyDetails, but it might be slightly stale
        }
    } catch (Exception $e) {
        error_log("Exception re-fetching company details after failed update: " . $e->getMessage());
    }
}

$pageTitle = "Edit Company: " . htmlspecialchars($companyDetails['name_company'] ?? 'Unknown');

// --- Include the View ---
include __DIR__ . '/../View/editCompanyView.php';
?>
