<?php

require_once __DIR__ . '/../../config/config.php';

class User {
    private $conn;
    private $error = '';

    public function __construct($conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    // Create a student
    public function createStudent($name, $email, $password, $location, $phone, $dob, $year, $description) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);
            $description = $this->sanitize($description);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO student (name, email, password, location, phone_number, date_of_birth, year, description) 
                    VALUES (:name, :email, :password, :location, :phone, :dob, :year, :description)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Create a pilote
    public function createPilote($name, $email, $password, $location, $phone) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO pilote (name, email, password, location, phone_number) 
                    VALUES (:name, :email, :password, :location, :phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Create an admin
    public function createAdmin($name, $email, $password) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admin (name, email, password) 
                    VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Read user methods
    public function readStudent($id_student) {
        try {
            $sql = "SELECT * FROM student WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function readPilote($id_pilote) {
        try {
            $sql = "SELECT * FROM pilote WHERE id_pilote = :id_pilote";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_pilote', $id_pilote);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function readAdmin($id_admin) {
        try {
            $sql = "SELECT * FROM admin WHERE id_admin = :id_admin";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_admin', $id_admin);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }


    public function getAllStudents() {
        try {
            $sql = "SELECT * FROM student";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllPilotes() {
        try {
            $sql = "SELECT * FROM pilote";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllAdmins() {
        try {
            $sql = "SELECT * FROM admin";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }


    // Get error message
    // Add this method to the User class if it doesn't exist already
    public function getError() {
        return $this->error;
    }

    public function deleteAdmin($id) {
        try {
            $sql = "DELETE FROM admin WHERE id_admin = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Authentication methods
    public function authenticateStudent($email, $password, &$userData) {
        try {
            $sql = "SELECT * FROM student WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $userData = $user;
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function authenticatePilote($email, $password, &$userData) {
        try {
            $sql = "SELECT * FROM pilote WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $userData = $user;
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function authenticateAdmin($email, $password, &$userData) {
        try {
            $sql = "SELECT * FROM admin WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $userData = $user;
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }
}

?>