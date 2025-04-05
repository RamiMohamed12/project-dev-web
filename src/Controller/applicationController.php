<?php
// Location: src/Controller/applicationController.php

// Required includes
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/Application.php';
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

// Authorization: Only Students can apply for internships
if ($loggedInUserRole !== 'student') {
    header("Location: ../View/login.php?error=" . urlencode("Access Denied: Only students can apply for internships."));
    exit();
}

// Instantiate Models
$applicationModel = new Application($conn);
$internshipModel = new Internship($conn);

// Default values
$errorMessage = '';
$successMessage = '';
$pageTitle = 'Apply for Internship';

// Get action from GET or POST
$action = $_GET['action'] ?? ($_POST['action'] ?? 'apply');

try {
    switch ($action) {
        // View application form
        case 'apply':
            if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
                $errorMessage = "Invalid internship ID.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            $internshipId = (int)$_GET['id'];
            
            // Check if already applied
            if ($applicationModel->hasApplied($loggedInUserId, $internshipId)) {
                $errorMessage = "You have already applied for this internship.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            // Get internship details
            $internshipDetails = $internshipModel->readInternship($internshipId);
            if (!$internshipDetails) {
                $errorMessage = "Internship not found.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            // Show application form
            include __DIR__ . '/../View/applicationFormView.php';
            break;
            
        // Process application submission
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: offerController.php?action=view");
                exit();
            }
            
            // Validate inputs
            $internshipId = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
            $motivationLetter = trim($_POST['motivation_letter'] ?? '');
            
            if (!$internshipId || empty($motivationLetter)) {
                $errorMessage = "All fields are required.";
                
                // Get internship details again to redisplay the form
                $internshipDetails = $internshipModel->readInternship($internshipId);
                include __DIR__ . '/../View/applicationFormView.php';
                exit();
            }
            
            // Check if already applied
            if ($applicationModel->hasApplied($loggedInUserId, $internshipId)) {
                $errorMessage = "You have already applied for this internship.";
                header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
                exit();
            }
            
            // Handle CV upload if provided
            $cvFilePath = null;
            $cvFileName = null;
            
            if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/cv/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $cvFileName = basename($_FILES['cv_file']['name']);
                $cvFileExt = strtolower(pathinfo($cvFileName, PATHINFO_EXTENSION));
                
                // Validate file extension
                $allowedExts = ['pdf', 'doc', 'docx'];
                if (!in_array($cvFileExt, $allowedExts)) {
                    $errorMessage = "Only PDF, DOC, and DOCX files are allowed for CV.";
                    $internshipDetails = $internshipModel->readInternship($internshipId);
                    include __DIR__ . '/../View/applicationFormView.php';
                    exit();
                }
                
                // Generate unique filename
                $uniqueFileName = uniqid('cv_') . '_' . $cvFileName;
                $cvFilePath = $uploadDir . $uniqueFileName;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $cvFilePath)) {
                    $errorMessage = "Failed to upload CV file.";
                    $internshipDetails = $internshipModel->readInternship($internshipId);
                    include __DIR__ . '/../View/applicationFormView.php';
                    exit();
                }
                
                // Store relative path for database
                $cvFilePath = 'uploads/cv/' . $uniqueFileName;
            }
            
            // Submit application
            $result = $applicationModel->createApplication(
                $loggedInUserId,
                $internshipId,
                $motivationLetter,
                $cvFilePath,
                $cvFileName
            );
            
            if ($result) {
                $successMessage = "Your application has been submitted successfully!";
                header("Location: applicationController.php?action=myapps&success=" . urlencode($successMessage));
                exit();
            } else {
                $errorMessage = $applicationModel->getError() ?: "Failed to submit application.";
                $internshipDetails = $internshipModel->readInternship($internshipId);
                include __DIR__ . '/../View/applicationFormView.php';
                exit();
            }
            break;
            
        // View student's applications
        // In the 'myapps' case section:
        case 'myapps':
            $pageTitle = "My Applications";
            $applications = $applicationModel->getStudentApplications($loggedInUserId);
            
            // Debug: Log the applications data
            error_log("Applications in controller: " . (is_array($applications) ? count($applications) : 'not an array'));
            if (is_array($applications)) {
                error_log("First application: " . print_r($applications[0] ?? 'none', true));
            }
            
            include __DIR__ . '/../View/myApplicationsView.php';
            break;
            
        default:
            header("Location: offerController.php?action=view");
            exit();
    }
} catch (Exception $e) {
    error_log("Unhandled Exception in applicationController: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $errorMessage = "An unexpected system error occurred. Please report this issue.";
    header("Location: offerController.php?action=view&error=" . urlencode($errorMessage));
    exit();
}
?>
