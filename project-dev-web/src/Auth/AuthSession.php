<?php
// Location: /home/demy/project-dev-web/src/Auth/AuthSession.php

class AuthSession {

    /**
     * Starts the user session after successful login.
     * Regenerates session ID for security.
     *
     * @param array $userData Array containing user details (id, name, email, type/role).
     *                        'id' should be the primary key for the specific user type table.
     *                        'type' MUST be 'student', 'pilote', or 'admin'.
     * @return bool True on success, false on failure (e.g., missing data).
     */
    public static function startUserSession(array $userData) : bool {
        if (session_status() == PHP_SESSION_NONE) {
            // Start session only if not already started
            if (!session_start()) {
                error_log("AuthSession Error: Failed to start session.");
                return false; // Cannot proceed without a session
            }
        }

        // Prevent session fixation
        if (!session_regenerate_id(true)) {
             error_log("AuthSession Error: Failed to regenerate session ID.");
             // Decide if this is critical enough to stop login. Maybe not.
        }

        // Validate required keys before storing
        if (!isset($userData['id'], $userData['name'], $userData['email'], $userData['type'])) {
             error_log("AuthSession Error: Missing required user data for session start. Data: " . print_r($userData, true));
             // Don't destroy session here, let controller handle redirect, but signal failure
             return false; // Indicate failure
        }

        // Ensure type is valid
        $validTypes = ['admin', 'pilote', 'student'];
        if (!in_array($userData['type'], $validTypes)) {
            error_log("AuthSession Error: Invalid user type '{$userData['type']}' provided for session start.");
            return false; // Indicate failure
        }

        // Clear any old session data before setting new values (optional but cleaner)
        $_SESSION = [];

        // Store essential user data in the session
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_role'] = $userData['type']; // Store the validated role/type
        $_SESSION['logged_in_time'] = time();

        // Check if session variables were actually set (paranoid check, but can help debug)
        if (!isset($_SESSION['user_id'])) {
             error_log("AuthSession Error: Failed to set session variables.");
             return false;
        }

        error_log("AuthSession: Session started successfully for user ID {$userData['id']}, type {$userData['type']}."); // Debug log
        return true; // Indicate success
    }

    /**
     * Checks if a user is currently logged in with essential data set.
     *
     * @return bool True if logged in, false otherwise.
     */
    public static function isUserLoggedIn() : bool {
         // If no session is active/started, they cannot be logged in.
         if (session_status() !== PHP_SESSION_ACTIVE) {
             // Avoid starting session just to check, they aren't logged in if session isn't active.
             return false;
         }

         // Check if the essential session variables are set and role is valid
         return isset($_SESSION['user_id'], $_SESSION['user_role'])
                && in_array($_SESSION['user_role'], ['admin', 'pilote', 'student']);
    }

    /**
     * Destroys the current session (logout).
     */
    public static function destroySession() : void {
         // Ensure session exists and is active before trying to destroy
         if (session_status() !== PHP_SESSION_ACTIVE) {
             // If no session active, maybe start one just to clear cookie? Or just return.
             // Let's try starting it to ensure cookie clearing works if needed.
             if (!session_start()) {
                 error_log("AuthSession Warning: Tried to destroy session, but failed to start non-active session.");
                 return; // Cannot proceed
             }
         }

        // Unset all session variables
        $_SESSION = array();

        // Delete the session cookie if used
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session data on the server.
        session_destroy();
    }

    /**
     * Gets a specific piece of user data from the session.
     *
     * @param string $key The session key ('user_id', 'user_name', 'user_email', 'user_role', 'logged_in_time').
     * @return mixed|null The value if set, otherwise null.
     */
    public static function getUserData(string $key) {
        // Ensure session is active before accessing $_SESSION
        // If called when not logged in (e.g., on login page check), session might not be active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Optionally start it, but if checking data, maybe null is correct if inactive
            // Let's start it to be safe, as AuthCheck might call this.
             if (!session_start()) {
                 error_log("AuthSession Warning: getUserData called when session not active and failed to start.");
                 return null;
             }
        }
        return $_SESSION[$key] ?? null;
    }
}
?>
