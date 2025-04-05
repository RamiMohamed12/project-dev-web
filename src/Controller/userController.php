<?php
// Location: /home/demy/project-dev-web/src/Controller/userController.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php'; // DB Connection ($conn)
require_once __DIR__ . '/../Model/user.php';      // User Model (Ensure it has paginated methods)
require_once __DIR__ . '/../Auth/AuthSession.php'; // Session utilities

// Start session VERY FIRST to access logged-in user data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth Check (Ensure user is logged in) ---
if (!AuthSession::isUserLoggedIn()) {
    header("Location: ../View/login.php?error=" . urlencode("Authentication required for user management."));
    exit();
}

// --- Get Logged-in User Info ---
$loggedInUserRole = AuthSession::getUserData('user_role'); // 'admin', 'pilote', etc.
$loggedInUserId = AuthSession::getUserData('user_id');   // The ID of the logged-in user

// --- Authorization Check (Only Admins or Pilotes can access this controller) ---
if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
    AuthSession::destroySession(); // Log them out for safety
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
$pageTitle = "User Management"; // Default Title
$canManageAdmins = ($loggedInUserRole === 'admin');
$canManagePilotes = ($loggedInUserRole === 'admin');
$errorMessage = '';
$successMessage = '';

// --- Pagination Variables ---
$itemsPerPage = 4;          // Set items per page (consistent with AJAX endpoint)
$initialPage = 1;
$initialOffset = 0; // Offset for the first page

// Arrays to hold initial data and pagination info
$students = []; $pilotes = []; $admins = [];
$studentPagination = ['currentPage' => $initialPage, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage];
$pilotePagination = ['currentPage' => $initialPage, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage];
$adminPagination = ['currentPage' => $initialPage, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage];


// --- Populate messages from GET parameters ---
if(isset($_GET['update']) && $_GET['update'] == 'success') { $successMessage = "User updated successfully."; }
elseif (isset($_GET['delete']) && $_GET['delete'] == 'success') { $successMessage = "User deleted successfully."; }
elseif (isset($_GET['add']) && $_GET['add'] == 'success') { $successMessage = "User added successfully."; }
elseif (isset($_GET['error'])) { $errorMessage = htmlspecialchars(urldecode($_GET['error'])); }


// --- Handle POST Actions (Add User, Delete User) ---
// This logic remains the same as your original file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- ACTION: Add User ---
    if ($action == 'add') {
        $typeToAdd = $_POST['type'] ?? null; $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? ''; $password = $_POST['password'] ?? '';
        $location = $_POST['location'] ?? null; $phone = $_POST['phone'] ?? null;
        $dob = $_POST['dob'] ?? null; $year = $_POST['year'] ?? null;
        $description = $_POST['description'] ?? null; $school = $_POST['school'] ?? null;

        $creatorPiloteId = ($loggedInUserRole === 'pilote') ? $loggedInUserId : null;

        // Authorization checks
        $allowedToAdd = false;
        if ($typeToAdd === 'student' && ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote')) { $allowedToAdd = true; }
        elseif (($typeToAdd === 'pilote' || $typeToAdd === 'admin') && $loggedInUserRole === 'admin') { $allowedToAdd = true; }

        // Validation & Execution
        if (!$allowedToAdd) { $errorMessage = "Error: You do not have permission to add this type of user."; }
        elseif (empty($name) || empty($email) || empty($password) || empty($typeToAdd)) { $errorMessage = "Error: User Type, Name, Email, and Password are required."; }
        // Basic Password Strength Check (Server-side) - Mirror JS check
        elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
             $errorMessage = "Error: Password does not meet requirements (min 8 chars, 1 uppercase, 1 number).";
        }
        else {
            $result = false;
            try {
                switch ($typeToAdd) {
                    case 'student':
                        if (empty($dob) || empty($year)) { $errorMessage = "Date of Birth and Year are required for students."; break; }
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
                // Redirect cleanly after successful add
                header("Location: userController.php?add=success"); exit();
            } elseif (empty($errorMessage)) {
                $errorMessage = $userModel->getError() ?: "Error: Could not add the user.";
            }
        }
    }
    // --- ACTION: Delete User ---
    elseif ($action == 'delete') {
        $idToDelete = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $typeToDelete = $_POST['type'] ?? null;

        if ($idToDelete <= 0 || !$typeToDelete) { $errorMessage = "Error: Invalid ID or type specified for deletion."; }
        else {
            $allowedToDelete = false; $itemDetails = null;
            // Fetch details needed for authorization (e.g., student's creator)
            try {
                if ($typeToDelete === 'student') {
                    $itemDetails = $userModel->readStudent($idToDelete);
                }
                // No need to fetch details for pilote/admin deletion if only admin can delete them
            } catch (Exception $e) {
                error_log("Error fetching item details before delete: " . $e->getMessage());
                $errorMessage = "Error verifying item for deletion.";
            }

            if (empty($errorMessage)) {
                // Authorization Logic
                if ($loggedInUserRole === 'admin') {
                    // Admin cannot delete self via this list
                    if (!($typeToDelete === 'admin' && $idToDelete === $loggedInUserId)) {
                        $allowedToDelete = true;
                    } else {
                        $errorMessage = "Error: Admins cannot delete their own account via this list.";
                    }
                } elseif ($loggedInUserRole === 'pilote') {
                    // Pilote can only delete students they created
                    if ($typeToDelete === 'student') {
                        if ($itemDetails && isset($itemDetails['created_by_pilote_id']) && $itemDetails['created_by_pilote_id'] == $loggedInUserId) {
                            $allowedToDelete = true;
                        } else {
                            $errorMessage = "Error: You can only delete students you created.";
                        }
                    } else {
                        // Pilote cannot delete pilote or admin
                        $errorMessage = "Error: Pilotes do not have permission to delete this type of user.";
                    }
                }

                if (!$allowedToDelete && empty($errorMessage)) {
                    // Fallback permission error
                    $errorMessage = "Error: You do not have permission to delete this user.";
                }

                // Execute Deletion if allowed
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
                        // Redirect cleanly after successful delete
                        header("Location: userController.php?delete=success"); exit();
                    } elseif (empty($errorMessage)) {
                        $errorMessage = $userModel->getError() ?: "Error: Could not delete user.";
                         // Add more specific FK error message if needed
                         if (strpos($errorMessage, 'foreign key constraint') !== false || strpos($errorMessage, 'Integrity constraint violation') !== false) {
                             $errorMessage = "Error: Cannot delete user. They may have associated records (e.g., applications, created companies/offers). Please reassign or remove related items first.";
                         }
                    }
                }
            }
        }
    } // End if ($action == 'delete')

} // End if POST request


// --- Fetch *Initial* User Data for Display Based on Role (Page 1 Only) ---
try {
    $piloteIdFilter = null;
    if ($loggedInUserRole === 'pilote') {
        $piloteIdFilter = $loggedInUserId;
        $pageTitle = "Manage My Students";
    } elseif ($loggedInUserRole === 'admin') {
         $pageTitle = "Manage All Users";
    }

    // Fetch Students (Page 1)
    // Ensure the model methods exist: getStudentsPaginated and getTotalStudentsCount
    $students = $userModel->getStudentsPaginated($itemsPerPage, $initialOffset, $piloteIdFilter);
    $totalStudents = $userModel->getTotalStudentsCount($piloteIdFilter);
    if ($students === false || $totalStudents === false) throw new Exception($userModel->getError() ?: "Failed to fetch initial student data or count.");
    $studentPagination['totalPages'] = ($totalStudents > 0) ? ceil($totalStudents / $itemsPerPage) : 0;
    $studentPagination['totalUsers'] = $totalStudents;

    // Fetch Pilotes (Page 1 - Admin only)
    if ($loggedInUserRole === 'admin') {
        // Ensure the model methods exist: getPilotesPaginated and getTotalPilotesCount
        $pilotes = $userModel->getPilotesPaginated($itemsPerPage, $initialOffset);
        $totalPilotes = $userModel->getTotalPilotesCount();
         if ($pilotes === false || $totalPilotes === false) throw new Exception($userModel->getError() ?: "Failed to fetch initial pilote data or count.");
        $pilotePagination['totalPages'] = ($totalPilotes > 0) ? ceil($totalPilotes / $itemsPerPage) : 0;
        $pilotePagination['totalUsers'] = $totalPilotes;
    }

    // Fetch Admins (Page 1 - Admin only)
    if ($loggedInUserRole === 'admin') {
         // Ensure the model methods exist: getAdminsPaginated and getTotalAdminsCount
        $admins = $userModel->getAdminsPaginated($itemsPerPage, $initialOffset);
        $totalAdmins = $userModel->getTotalAdminsCount();
         if ($admins === false || $totalAdmins === false) throw new Exception($userModel->getError() ?: "Failed to fetch initial admin data or count.");
        $adminPagination['totalPages'] = ($totalAdmins > 0) ? ceil($totalAdmins / $itemsPerPage) : 0;
        $adminPagination['totalUsers'] = $totalAdmins;
    }

     // --- Add 'canModify', 'user_id', 'user_type' flags for initial load ---
     // This makes the initial data structure consistent with the AJAX response
     foreach ($students as $key => $student) {
         $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($student['created_by_pilote_id']) && $student['created_by_pilote_id'] == $loggedInUserId));
         $students[$key]['canModify'] = $canModify;
         $students[$key]['user_id'] = $student['id_student']; // Use the actual ID field name
         $students[$key]['user_type'] = 'student';
     }
     if ($loggedInUserRole === 'admin') { // Only process these if admin fetched them
         foreach ($pilotes as $key => $pilote) {
             $pilotes[$key]['canModify'] = true; // Admin can modify all pilotes
             $pilotes[$key]['user_id'] = $pilote['id_pilote'];
             $pilotes[$key]['user_type'] = 'pilote';
         }
         foreach ($admins as $key => $admin) {
             // Admin cannot delete self via list, but can edit
             $admins[$key]['canModify'] = true; // Can always edit
             // Optional: add a specific 'canDelete' flag if needed
             // $admins[$key]['canDelete'] = !($admin['id_admin'] == $loggedInUserId);
             $admins[$key]['user_id'] = $admin['id_admin'];
             $admins[$key]['user_type'] = 'admin';
         }
     }


} catch (Exception $e) {
     error_log("Exception fetching initial user data in userController: " . $e->getMessage());
     $errorMessage = "An error occurred while retrieving initial user lists. Please try refreshing. Details: " . $e->getMessage();
     // Ensure arrays are empty on error
     $students = []; $pilotes = []; $admins = [];
     // Reset pagination counts
     $studentPagination = ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage];
     $pilotePagination = ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage];
     $adminPagination = ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage];
}


// --- Include the View ---
// Pass initial paginated data and pagination info to the view
include __DIR__ . '/../View/manageUsersView.php';

?>