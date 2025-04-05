<?php
// Location: /home/demy/project-dev-web/src/Controller/ajax_get_users.php

// --- Required Includes & Session Start ---
require_once __DIR__ . '/../../config/config.php'; // DB Connection ($conn)
require_once __DIR__ . '/../Model/user.php';      // User Model
require_once __DIR__ . '/../Auth/AuthSession.php'; // Session utilities

header('Content-Type: application/json'); // Set header for JSON response

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Basic Auth & Role Check ---
if (!AuthSession::isUserLoggedIn()) {
    echo json_encode(['error' => 'Authentication required.']);
    exit();
}
$loggedInUserRole = AuthSession::getUserData('user_role');
$loggedInUserId = AuthSession::getUserData('user_id');

if ($loggedInUserRole !== 'admin' && $loggedInUserRole !== 'pilote') {
    echo json_encode(['error' => 'Access Denied: Insufficient privileges.']);
    exit();
}

// --- Get Parameters ---
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : 1;
$userType = $_GET['type'] ?? null; // 'students', 'pilotes', 'admins'
$itemsPerPage = 4; // Define items per page

if (!$page) $page = 1; // Default to page 1 if validation fails
$offset = ($page - 1) * $itemsPerPage;

$allowedTypes = ['students', 'pilotes', 'admins'];
if (!$userType || !in_array($userType, $allowedTypes)) {
    echo json_encode(['error' => 'Invalid user type requested.']);
    exit();
}

// --- Instantiate User Model ---
try {
    $userModel = new User($conn);
} catch (Exception $e) {
    error_log("AJAX Get Users: Failed to instantiate User model: " . $e->getMessage());
    echo json_encode(['error' => 'Server error during model setup.']);
    exit();
}

// --- Fetch Data Based on Type and Role ---
$users = [];
$totalUsers = 0;
$totalPages = 0;
$piloteIdFilter = null;

// Apply filter only if a pilote is requesting student data
if ($loggedInUserRole === 'pilote' && $userType === 'students') {
    $piloteIdFilter = $loggedInUserId;
}
// Pilotes cannot view pilotes or admins list via this AJAX
if ($loggedInUserRole === 'pilote' && ($userType === 'pilotes' || $userType === 'admins')) {
     echo json_encode(['error' => 'Access Denied: Pilotes cannot view this list.']);
     exit();
}


try {
    switch ($userType) {
        case 'students':
            $users = $userModel->getStudentsPaginated($itemsPerPage, $offset, $piloteIdFilter);
            $totalUsers = $userModel->getTotalStudentsCount($piloteIdFilter);
            break;
        case 'pilotes':
            // Only admin can reach here for pilotes
            $users = $userModel->getPilotesPaginated($itemsPerPage, $offset);
            $totalUsers = $userModel->getTotalPilotesCount();
            break;
        case 'admins':
            // Only admin can reach here for admins
            $users = $userModel->getAdminsPaginated($itemsPerPage, $offset);
            $totalUsers = $userModel->getTotalAdminsCount();
            break;
    }

    if ($users === false || $totalUsers === false) {
        throw new Exception($userModel->getError() ?: 'Failed to fetch user data or count.');
    }

    $totalPages = ($totalUsers > 0) ? ceil($totalUsers / $itemsPerPage) : 0;

    // *** Add 'canModify' flag based on server-side logic ***
    foreach ($users as $key => $user) {
        $canModify = false;
        if ($loggedInUserRole === 'admin') {
            // Admin cannot delete self via list
            if (!($userType === 'admins' && isset($user['id_admin']) && $user['id_admin'] == $loggedInUserId)) {
                 $canModify = true;
            }
        } elseif ($loggedInUserRole === 'pilote') {
            // Pilote can modify students they created
            if ($userType === 'students' && isset($user['created_by_pilote_id']) && $user['created_by_pilote_id'] == $loggedInUserId) {
                $canModify = true;
            }
        }
        $users[$key]['canModify'] = $canModify; // Add the flag to each user object/array

        // Add user ID consistently for JS, regardless of type-specific ID name
        if ($userType === 'students' && isset($user['id_student'])) $users[$key]['user_id'] = $user['id_student'];
        if ($userType === 'pilotes' && isset($user['id_pilote'])) $users[$key]['user_id'] = $user['id_pilote'];
        if ($userType === 'admins' && isset($user['id_admin'])) $users[$key]['user_id'] = $user['id_admin'];

        // Add user type consistently
        $users[$key]['user_type'] = substr($userType, 0, -1); // 'student', 'pilote', 'admin'
    }


    // --- Prepare JSON Response ---
    $response = [
        'success' => true,
        'users' => $users,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'itemsPerPage' => $itemsPerPage
        ]
    ];
    echo json_encode($response);

} catch (Exception $e) {
    error_log("AJAX Get Users Error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching users.', 'details' => $e->getMessage()]);
}

?>