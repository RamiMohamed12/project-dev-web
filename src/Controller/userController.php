<?php
// Location: /home/demy/project-dev-web/src/Controller/userController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php'; // DB Connection ($conn)
require_once __DIR__ . '/../Model/user.php';      // User Model
require_once __DIR__ . '/../Auth/AuthSession.php'; // Session utilities
// AuthCheck might not be strictly needed here as we do more specific checks, but include for consistency if desired
// require_once __DIR__ . '/../Auth/AuthCheck.php';

// Start session VERY FIRST to access logged-in user data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth Check (Ensure user is logged in) ---
if (!AuthSession::isUserLoggedIn()) {
    // Redirect to login if no valid session exists
    // Path is correct: Up one level from Controller, down into View
    header("Location: ../View/login.php?error=" . urlencode("Authentication required for user management."));
    exit();
}

// --- Get Logged-in User Info ---
$loggedInUserRole = AuthSession::getUserData('user_role'); // 'admin', 'pilote', etc.
$loggedInUserId = AuthSession::getUserData('user_id');   // The ID of the logged-in user

// --- Authorization Check (Only Admins or Pilotes can access this controller) ---
if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
    // Students or other roles should not be here
     AuthSession::destroySession(); // Log them out for safety
     // Correct path to the login view
     header("Location: ../View/login.php?error=" . urlencode("Access Denied: Insufficient privileges for user management."));
     exit();
}


// --- Instantiate User Model ---
try {
    $userModel = new User($conn); // Pass the database connection
} catch (InvalidArgumentException $e) {
    error_log("FATAL in userController: Invalid DB connection passed to User model: " . $e->getMessage());
    die("Database configuration error. Cannot manage users.");
} catch (Exception $e) {
    error_log("FATAL in userController: Failed to instantiate User model: " . $e->getMessage());
    die("A critical error occurred setting up user management.");
}


// --- Initialize Variables for the View ---
$students = []; $pilotes = []; $admins = [];
$pageTitle = "User Management";
$canManageAdmins = ($loggedInUserRole === 'admin');
$canManagePilotes = ($loggedInUserRole === 'admin');
$errorMessage = '';
$successMessage = '';

// Populate messages from GET parameters
if(isset($_GET['update']) && $_GET['update'] == 'success') { $successMessage = "User updated successfully."; }
elseif (isset($_GET['delete']) && $_GET['delete'] == 'success') { $successMessage = "User deleted successfully."; }
elseif (isset($_GET['add']) && $_GET['add'] == 'success') { $successMessage = "User added successfully."; }
elseif (isset($_GET['error'])) { $errorMessage = htmlspecialchars(urldecode($_GET['error'])); }


// --- Handle POST Actions (Add User, Delete User) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // =========================================
    // --- ACTION: Add User ---
    // =========================================
    if ($action == 'add') {
        $typeToAdd = $_POST['type'] ?? null; $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? ''; $password = $_POST['password'] ?? '';
        $location = $_POST['location'] ?? null; $phone = $_POST['phone'] ?? null;
        $dob = $_POST['dob'] ?? null; $year = $_POST['year'] ?? null;
        $description = $_POST['description'] ?? null; $school = $_POST['school'] ?? null; // Get school

        $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;

        // Authorization checks
        $allowedToAdd = false;
        if ($typeToAdd === 'student' && ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote')) { $allowedToAdd = true; }
        elseif (($typeToAdd === 'pilote' || $typeToAdd === 'admin') && $loggedInUserRole === 'admin') { $allowedToAdd = true; }

        // Validation & Execution
        if (!$allowedToAdd) { $errorMessage = "Error: You do not have permission to add this type of user."; }
        elseif (empty($name) || empty($email) || empty($password) || empty($typeToAdd)) { $errorMessage = "Error: User Type, Name, Email, and Password are required."; }
        else {
            $result = false;
            try { // Wrap model calls in try-catch
                switch ($typeToAdd) {
                    case 'student':
                        if (empty($dob) || empty($year)) { $errorMessage = "Date of Birth and Year are required for students."; break; }
                        // Add school validation if required when creating
                        // if (empty($school)) { $errorMessage = "School is required for students."; break; }
                        $result = $userModel->createStudent( $name, $email, $password, $location, $phone, $dob, $year, $description, $school, $creatorPiloteId );
                        break;
                    case 'pilote':
                        $result = $userModel->createPilote( $name, $email, $password, $location, $phone );
                        break;
                    case 'admin':
                        $result = $userModel->createAdmin( $name, $email, $password );
                        break;
                    default:
                        $errorMessage = "Error: Invalid user type specified for addition."; break;
                }
            } catch (Exception $e) {
                error_log("Exception during user add operation: " . $e->getMessage());
                $errorMessage = $userModel->getError() ?: "An unexpected error occurred while adding the user.";
            }

            if ($result) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?add=success"); exit();
            } elseif (empty($errorMessage)) {
                $errorMessage = $userModel->getError() ?: "Error: Could not add the user.";
            }
        }
    }
    // =========================================
    // --- ACTION: Delete User ---
    // =========================================
    elseif ($action == 'delete') {
        $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $typeToDelete = $_POST['type'] ?? null;

        if ($idToDelete <= 0 || !$typeToDelete) { $errorMessage = "Error: Invalid ID or type specified for deletion."; }
        else {
            $allowedToDelete = false; $itemDetails = null;
            try { if ($typeToDelete === 'student') $itemDetails = $userModel->readStudent($idToDelete); }
            catch (Exception $e) { error_log("Error fetching item details before delete: " . $e->getMessage()); $errorMessage = "Error verifying item for deletion."; }

            if (empty($errorMessage)) {
                if ($loggedInUserRole === 'admin') {
                    if (!($typeToDelete === 'admin' && $idToDelete === $loggedInUserId)) { $allowedToDelete = true; }
                    else { $errorMessage = "Error: Admins cannot delete their own account via this list."; }
                } elseif ($loggedInUserRole === 'pilote') {
                    if ($typeToDelete === 'student') {
                        if ($itemDetails && isset($itemDetails['created_by_pilote_id']) && $itemDetails['created_by_pilote_id'] == $loggedInUserId) { $allowedToDelete = true; }
                        else { $errorMessage = "Error: You can only delete students you created."; }
                    } else { $errorMessage = "Error: Pilotes do not have permission to delete this type of user."; }
                }

                if (!$allowedToDelete && empty($errorMessage)) { $errorMessage = "Error: You do not have permission to delete this user."; }

                if ($allowedToDelete) {
                    $result = false;
                    try {
                        switch ($typeToDelete) {
                            case 'student': $result = $userModel->deleteStudent($idToDelete); break;
                            case 'pilote':  $result = $userModel->deletePilote($idToDelete); break;
                            case 'admin':   $result = $userModel->deleteAdmin($idToDelete); break;
                            default: $errorMessage = "Error: Invalid user type specified for deletion."; break;
                        }
                    } catch (Exception $e) {
                         error_log("Exception during user delete operation: " . $e->getMessage());
                         $errorMessage = $userModel->getError() ?: "An unexpected error occurred during deletion.";
                    }

                    if ($result) {
                        header("Location: " . $_SERVER['PHP_SELF'] . "?delete=success"); exit();
                    } elseif (empty($errorMessage)) {
                        $errorMessage = $userModel->getError() ?: "Error: Could not delete user.";
                         if (strpos($errorMessage, 'foreign key constraint') !== false) {
                             $errorMessage = "Error: Cannot delete user due to related records (e.g., applications).";
                         }
                    }
                }
            }
        }
    } // End if ($action == 'delete')

} // End if POST request


// --- Fetch User Data for Display Based on Role ---
try {
    if ($loggedInUserRole === 'admin') {
        $students = $userModel->getAllStudents();
        $pilotes = $userModel->getAllPilotes();
        $admins = $userModel->getAllAdmins();
        $pageTitle = "Manage All Users";
    } elseif ($loggedInUserRole === 'pilote') {
        $students = $userModel->getAllStudents($loggedInUserId); // Filter by creator
        $pilotes = []; // Pilotes don't see lists of other pilotes
        $admins = [];  // Pilotes don't see lists of admins
        $pageTitle = "Manage My Students";
    }

    // Check for fetch errors (model methods return false)
    if ($students === false || ($loggedInUserRole === 'admin' && ($pilotes === false || $admins === false))) {
        $errorMsgFromModel = $userModel->getError();
        $errorMessage = $errorMsgFromModel ?: "Error fetching user data for display.";
        error_log("Error in userController fetching data: " . $errorMessage . ($errorMsgFromModel ? '' : ' (Generic)'));
        $students = []; $pilotes = []; $admins = []; // Ensure arrays are empty on error
    }

} catch (Exception $e) {
     error_log("Exception fetching user data in userController: " . $e->getMessage());
     $errorMessage = "An unexpected error occurred while retrieving user lists.";
     $students = []; $pilotes = []; $admins = []; // Ensure arrays are empty
}


// --- Include the View ---
// Pass all necessary variables ($students, $pilotes, $admins, flags, messages) to the view file.
include __DIR__ . '/../View/manageUsersView.php';

?>
