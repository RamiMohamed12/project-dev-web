<?php
// Location: /home/demy/project-dev-web/src/Auth/Authenticator.php

class Authenticator {
    private $conn;
    private $error;

    public function __construct(PDO $db) {
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ensure errors throw exceptions
    }

    /**
     * Gets the last error message.
     * @return string|null
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Attempts to log in a user based on their type, email, and password
     * by querying the corresponding table (admin, pilote, or student).
     *
     * @param string $email The user's email address.
     * @param string $password The user's plain text password.
     * @param string $user_type The type of user ('admin', 'pilote', 'student'). Determines which table to query.
     * @return array|false An array containing user data (id, name, email, type) on success, false on failure.
     */
    public function login(string $email, string $password, string $user_type) {
        $this->error = null; // Reset error

        // 1. Validate user type and determine table name + column names
        $allowed_types = ['admin', 'pilote', 'student'];
        if (!in_array($user_type, $allowed_types)) {
            $this->error = "Invalid account type specified.";
            error_log("Authenticator: Attempt with invalid type: " . $user_type);
            return false;
        }
        $tableName = $user_type; // Direct mapping: type 'admin' -> table 'admin'

        // ********************************************************************
        // ******** !! IMPORTANT !! ADJUST THESE COLUMN NAMES !! ************
        // ********************************************************************
        // Set the correct column names based on your ACTUAL database schema
        // for EACH table (admin, pilote, student).
        // Use the names from your Model/user.php as a guide.
        $idColumn = '';
        $nameColumn = 'name';             // Likely 'name' in all tables based on your model
        $emailColumn = 'email';           // Likely 'email' in all tables based on your model
        $passwordColumn = 'password';     // Likely 'password' in all tables based on your model

        switch($tableName) {
            case 'admin':
                $idColumn = 'id_admin';     // Example based on your readAdmin method
                // Confirm name, email, password column names for admin table
                break;
            case 'pilote':
                $idColumn = 'id_pilote';    // Example based on your readPilote method
                // Confirm name, email, password column names for pilote table
                break;
            case 'student':
                $idColumn = 'id_student';   // Example based on your readStudent method
                // Confirm name, email, password column names for student table
                break;
            default:
                // This case should not be reached due to the in_array check above
                $this->error = "Internal error: Unhandled user type.";
                error_log("Authenticator: Reached default case in switch for type: " . $user_type);
                return false;
        }

        // Double-check if column names were set
        if (empty($idColumn) || empty($nameColumn) || empty($emailColumn) || empty($passwordColumn)) {
             $this->error = "Internal configuration error: Column names not set for type '{$user_type}'.";
             error_log($this->error);
             return false;
        }
        // ********************************************************************

        try {
            // 2. Prepare SQL Query using the correct table and column names
            $sql = "SELECT `{$idColumn}`, `{$nameColumn}`, `{$emailColumn}`, `{$passwordColumn}`
                    FROM `{$tableName}`
                    WHERE `{$emailColumn}` = :email
                    LIMIT 1";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // 3. Fetch user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 4. Verify Password
                // Assumes passwords in DB are hashed using password_hash()
                if (password_verify($password, $user[$passwordColumn])) {
                    // Password is correct! Return standardized user data.
                    // Keys here MUST match what AuthSession::startUserSession expects.
                    return [
                        'id'    => $user[$idColumn],
                        'name'  => $user[$nameColumn],
                        'email' => $user[$emailColumn],
                        'type'  => $user_type // Return the type they logged in as
                    ];
                } else {
                    // Password incorrect
                    $this->error = "Invalid email or password for the selected account type.";
                    return false;
                }
            } else {
                // User email not found in the specified table
                $this->error = "Invalid email or password for the selected account type.";
                return false;
            }

        } catch (PDOException $e) {
            // Database error during query
            $this->error = "A database error occurred during login.";
            error_log("Authenticator DB Error (Table: {$tableName}, Email: {$email}): " . $e->getMessage());
            return false;
        } catch (Exception $e) {
             // Catch potential errors (e.g., wrong column name used in fetch/verify)
             $this->error = "An unexpected processing error occurred during login.";
             error_log("Authenticator General Error (Table: {$tableName}, Email: {$email}): " . $e->getMessage());
             return false;
        }
    }
}
?>
