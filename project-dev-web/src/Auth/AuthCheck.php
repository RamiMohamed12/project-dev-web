<?php
// Location: /home/demy/project-dev-web/src/Auth/AuthCheck.php

require_once __DIR__ . '/AuthSession.php'; // Use the session helper

class AuthCheck {

    /**
     * Checks if the user is logged in and has the required role.
     * Redirects to the login page if checks fail.
     *
     * @param string $requiredRole The role required to access the page ('admin', 'pilote', 'student').
     * @param string $loginPath Relative path to the login page from the file including this check. Defaults to '../View/login.php'.
     */
    public static function checkUserAuth(string $requiredRole, string $loginPath = '../View/login.php') {
        // Ensure session is started (safe even if already started)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Check if user is logged in at all
        if (!AuthSession::isUserLoggedIn()) {
            // Not logged in, redirect to login page
            header("Location: " . $loginPath . "?error=" . urlencode("Please login to access this page."));
            exit();
        }

        // 2. Check if the user has the required role
        $userRole = $_SESSION['user_role'] ?? null;

        if ($userRole !== $requiredRole) {
            // Logged in, but wrong role. Log them out and redirect to login.
            AuthSession::destroySession();
            header("Location: " . $loginPath . "?error=" . urlencode("Access denied. Insufficient privileges."));
            exit();
        }

        // If checks pass, execution continues on the original page.
    }
}
?>
