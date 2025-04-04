<?php

class Login {
    private $conn;
    private $error = '';
    private $allowedUserTypes = ['student', 'pilote', 'admin'];

    public function __construct($conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function validateUser($email, $userType, $password) {
        try {
            // Validate user type
            if (!in_array($userType, $this->allowedUserTypes)) {
                throw new Exception('Invalid user type');
            }

            // Use prepared statements to prevent SQL injection
            $sql = "SELECT * FROM " . $userType . " WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return [
                    'id' => $user['id_' . $userType],
                    'name' => $user['name'],
                    'type' => $userType,
                    'email' => $user['email']
                ];
            }
            
            error_log("Failed login attempt for email: $email, user type: $userType");
            return false;
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->error = 'Error during login attempt';
            return false;
        }
    }

    public function getError() {
        return $this->error;
    }
}
?>
