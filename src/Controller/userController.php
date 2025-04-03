<?php
// Location: /home/demy/project-dev-web/src/Controller/userController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php';
require_once __DIR__ . '/../Auth/AuthSession.php'; // Need for getting user role/id
require_once __DIR__ . '/../Auth/AuthCheck.php';   // Use for basic access check

// Start session VERY FIRST
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth Check (Ensure user is logged in) ---
// Redirect if not logged in at all
if (!AuthSession::isUserLoggedIn()) {
    header("Location: ../View/login.php?error=" . urlencode("Authentication required."));
    exit();
}

// --- Get Logged-in User Info ---
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

// --- Authorization Check (Admins or Pilotes can access, but with different views) ---
if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
    // Students or others cannot manage users
     AuthSession::destroySession(); // Log them out just in case
     header("Location: ../View/login.php?error=" . urlencode("Access Denied: Insufficient privileges for user management."));
     exit();
}


// --- Instantiate Model ---
try {
    $userModel = new User($conn); // Pass PDO connection
} catch (Exception $e) {
    error_log("Failed to instantiate User model: " . $e->getMessage());
    die("A critical error occurred setting up user management."); // Or redirect to an error page
}


// --- Variables for the View ---
$students = [];
$pilotes = [];
$admins = [];
$pageTitle = "User Management";
$canManageAdmins = ($loggedInUserRole === 'admin'); // Flag for view logic
$canManagePilotes = ($loggedInUserRole === 'admin');
$errorMessage = $userModel->getError(); // Get potential errors from model instantiation or previous ops
$successMessage = '';
if(isset($_GET['update']) && $_GET['update'] == 'success') {
    $successMessage = "User updated successfully.";
}
if(isset($_GET['delete']) && $_GET['delete'] == 'success') {
     $successMessage = "User deleted successfully.";
}
 if(isset($_GET['add']) && $_GET['add'] == 'success') {
     $successMessage = "User added successfully.";
}


// --- Handle POST Actions (Add, Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACTION: Add User ---
    if ($action == 'add') {
        $typeToAdd = $_POST['type'] ?? null;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? ''; // Raw password from form
        $location = $_POST['location'] ?? null;
        $phone = $_POST['phone'] ?? null;
        $dob = $_POST['dob'] ?? null;
        $year = $_POST['year'] ?? null;
        $description = $_POST['description'] ?? null;

        $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null; // Set creator ID if pilote

        // Authorization checks for adding
        $allowedToAdd = false;
        if ($typeToAdd === 'student' && ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote')) {
            $allowedToAdd = true;
        } elseif (($typeToAdd === 'pilote' || $typeToAdd === 'admin') && $loggedInUserRole === 'admin') {
            // Only admins can add other pilotes or admins
            $allowedToAdd = true;
        }

        if (!$allowedToAdd) {
            $errorMessage = "Error: You do not have permission to add this type of user.";
        } elseif (empty($name) || empty($email) || empty($password) || empty($typeToAdd)) {
            $errorMessage = "Error: Type, Name, Email, and Password are required to add a user.";
        } else {
            // Call appropriate create method based on type
            $result = false;
            switch ($typeToAdd) {
                case 'student':
                    if (empty($dob) || empty($year)) { $errorMessage = "DOB and Year required for students."; break; }
                    $result = $userModel->createStudent($name, $email, $password, $location, $phone, $dob, $year, $description, $creatorPiloteId);
                    break;
                case 'pilote':
                    // No extra fields needed beyond common ones
                    $result = $userModel->createPilote($name, $email, $password, $location, $phone);
                    break;
                case 'admin':
                    // Only name, email, password needed
                    $result = $userModel->createAdmin($name, $email, $password);
                    break;
                default:
                    $errorMessage = "Error: Invalid user type specified for addition.";
                    break;
            }

            if ($result) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?add=success"); // Redirect on success
                exit();
            } else {
                // Get specific error from model if available
                $errorMessage = $userModel->getError() ?: "Error: Could not add user.";
            }
        }
    }
    // --- ACTION: Delete User ---
    elseif ($action == 'delete') {
        $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $typeToDelete = $_POST['type'] ?? null;

        if ($idToDelete <= 0 || !$typeToDelete) {
             $errorMessage = "Error: Invalid ID or type for deletion.";
        } else {
            // Authorization check for deletion
            $allowedToDelete = false;
            if ($loggedInUserRole === 'admin') {
                // Admins can delete anyone EXCEPT themselves (usually bad idea)
                if (!($typeToDelete === 'admin' && $idToDelete === $loggedInUserId)) {
                     $allowedToDelete = true;
                } else {
                    $errorMessage = "Error: Admins cannot delete their own account through this interface.";
                }
            } elseif ($loggedInUserRole === 'pilote') {
                // Pilotes can only delete students THEY created
                if ($typeToDelete === 'student') {
                    $studentDetails = $userModel->readStudent($idToDelete);
                    if ($studentDetails && isset($studentDetails['created_by_pilote_id']) && $studentDetails['created_by_pilote_id'] == $loggedInUserId) {
                        $allowedToDelete = true;
                    } else {
                         $errorMessage = "Error: You can only delete students you created.";
                    }
                } else {
                    $errorMessage = "Error: Pilotes cannot delete admins or other pilotes.";
                }
            }

            if (!$allowedToDelete && empty($errorMessage)) { // If not already set
                 $errorMessage = "Error: You do not have permission to delete this user.";
            }

            if ($allowedToDelete) {
                $result = false;
                switch ($typeToDelete) {
                    case 'student': $result = $userModel->deleteStudent($idToDelete); break;
                    case 'pilote':  $result = $userModel->deletePilote($idToDelete); break;
                    case 'admin':   $result = $userModel->deleteAdmin($idToDelete); break;
                     default: $errorMessage = "Error: Invalid user type for deletion."; break;
                }

                 if ($result) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?delete=success"); // Redirect on success
                    exit();
                } else {
                    $errorMessage = $userModel->getError() ?: "Error: Could not delete user.";
                }
            }
        }
    }
}


// --- Fetch Data for Display Based on Role ---
try {
    if ($loggedInUserRole === 'admin') {
        // Admin sees everyone
        $students = $userModel->getAllStudents();
        $pilotes = $userModel->getAllPilotes();
        $admins = $userModel->getAllAdmins();
        $pageTitle = "Manage All Users";
    } elseif ($loggedInUserRole === 'pilote') {
        // Pilote sees ONLY students they created
        $students = $userModel->getAllStudents($loggedInUserId); // Pass pilote's ID
        // Pilotes don't see lists of other pilotes or admins
        $pilotes = [];
        $admins = [];
         $pageTitle = "Manage My Students";
    }

    // Check if fetching failed
    if ($students === false || $pilotes === false || $admins === false) {
        // Prioritize model error if available
        $errorMessage = $userModel->getError() ?: "Error fetching user data.";
         error_log("Error in userController fetching data: " . $errorMessage);
         // Reset arrays on error to prevent warnings in the view
         $students = []; $pilotes = []; $admins = [];
    }

} catch (Exception $e) {
     error_log("Exception fetching user data in userController: " . $e->getMessage());
     $errorMessage = "An unexpected error occurred while retrieving user lists.";
     $students = []; $pilotes = []; $admins = [];
}


// --- Include the View ---
// The view will use the $students, $pilotes, $admins arrays
// and the $loggedInUserRole, $loggedInUserId, $canManageAdmins flags for conditional display.
include __DIR__ . '/../View/manageUsersView.php'; // Assuming a separate view file

?>
