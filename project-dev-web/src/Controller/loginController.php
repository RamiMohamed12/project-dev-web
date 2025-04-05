<?php
// Location: /home/demy/project-dev-web/src/Controller/loginController.php

// Session MUST be started before any output or session access
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Essential Includes ---
require_once __DIR__ . '/../../config/config.php';      // For $conn (Database connection)
require_once __DIR__ . '/../Auth/Authenticator.php';   // **** USE THE NEW AUTHENTICATOR ****
require_once __DIR__ . '/../Auth/AuthSession.php';     // Session management class

// Define the path to the login view (relative from this controller's directory)
$loginViewPath = '../View/login.php';

// --- Pre-computation and Checks ---
$error_message = ''; // Initialize error message variable

// Check DB connection from config.php
if (!isset($conn) || !$conn instanceof PDO) {
    error_log("FATAL ERROR: Database connection is not available or invalid in loginController.php.");
    // Using die() here because redirect might fail if headers already sent or buffer active
    die("A critical system error occurred. Please contact the administrator. (Error Code: LCDB)");
}


// --- Request Handling ---

// A) Handle POST requests (Login Attempt)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if required fields are present
    if (!isset($_POST['email'], $_POST['password'], $_POST['user_type'])) {
        $_SESSION['login_attempt_email'] = $_POST['email'] ?? '';
        $_SESSION['login_attempt_type'] = $_POST['user_type'] ?? '';
        header("Location: " . $loginViewPath . "?error=" . urlencode("Incomplete login information submitted."));
        exit();
    }

    // Sanitize and retrieve POST data
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Don't trim password
    $user_type = trim($_POST['user_type']);

    // Store attempt details for repopulating form on error
    $_SESSION['login_attempt_email'] = $email;
    $_SESSION['login_attempt_type'] = $user_type;


    // --- Server-Side Validation ---
    if (empty($email) || empty($password) || empty($user_type)) {
        $error_message = "Account type, email, and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format provided.";
    } elseif (!in_array($user_type, ['admin', 'pilote', 'student'])) {
        $error_message = "Invalid account type selected.";
    } else {
        // --- Validation Passed - Attempt Login via Authenticator ---
        try {
            // **** Instantiate the Authenticator ****
            $authenticator = new Authenticator($conn);

            // **** Call the login method on the Authenticator ****
            $loggedInUser = $authenticator->login($email, $password, $user_type);

            if ($loggedInUser && is_array($loggedInUser)) {
                // Login SUCCESSFUL! Authenticator returned user data.
                $sessionStarted = AuthSession::startUserSession($loggedInUser);

                if ($sessionStarted) {
                    // Session started successfully, clear temporary attempt data
                    unset($_SESSION['login_attempt_email'], $_SESSION['login_attempt_type']);

                    // --- Redirect based on the 'type' from $loggedInUser ---
                    $redirectUrl = $loginViewPath . '?error=' . urlencode('Login successful, but role redirection failed.'); // Fallback
                    switch ($loggedInUser['type']) {
                        case 'admin':   $redirectUrl = '../View/admin.php'; break;
                        case 'pilote':  $redirectUrl = '../View/pilote.php'; break;
                        case 'student': $redirectUrl = '../View/student.php'; break;
                    }
                    header("Location: " . $redirectUrl);
                    exit(); // IMPORTANT: Stop script

                } else {
                    // startUserSession failed (e.g., data validation failed within it)
                    $error_message = "Login successful, but failed to initialize session. Please try again.";
                    // Error logged within AuthSession::startUserSession
                }

            } else {
                // Login FAILED (Authenticator returned false)
                // Get error message from the Authenticator if available
                $authError = $authenticator->getError();
                $error_message = $authError ?: "Invalid credentials or account type mismatch.";
            }

        } catch (PDOException $e) {
            error_log("Login Controller DB Error (Type: {$user_type}, Email: {$email}): " . $e->getMessage());
            $error_message = "A database error occurred during login. Please try again later.";
        } catch (Exception $e) { // Catch potential Authenticator instantiation errors etc.
            error_log("Login Controller General Error (Type: {$user_type}, Email: {$email}): " . $e->getMessage());
            $error_message = "An unexpected error occurred during login. Please try again later.";
        }
    }

    // If login failed or a non-redirect error occurred, redirect back with error.
    if (!empty($error_message)) {
         header("Location: " . $loginViewPath . "?error=" . urlencode($error_message));
         exit(); // IMPORTANT: Stop script
    }

}
// B) Handle GET requests (or other methods)
else {
    // --- Not a POST request ---
    if (AuthSession::isUserLoggedIn()) {
        // Redirect logged-in users away from login page
        $role = AuthSession::getUserData('user_role');
        $redirectUrl = $loginViewPath; // Default back to login if role invalid
        switch ($role) {
            case 'admin': $redirectUrl = '../View/admin.php'; break;
            case 'pilote': $redirectUrl = '../View/pilote.php'; break;
            case 'student': $redirectUrl = '../View/student.php'; break;
        }
        header("Location: " . $redirectUrl);
        exit();
    } else {
        // If not logged in and not POST, show the login page by redirecting
        header("Location: " . $loginViewPath);
        exit();
    }
}

// Fallback - should not be reached
error_log("Login Controller reached end unexpectedly. Method: {$_SERVER['REQUEST_METHOD']}");
echo "Login controller finished unexpectedly.";
?>
