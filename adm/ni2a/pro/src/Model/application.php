<?php

require_once __DIR__ . '/../../config/config.php';

class Application {
    private $conn;
    private $error = '';

    public function __construct($conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Sanitize input data to prevent XSS attacks
     * @param string $data Input data to sanitize
     * @return string Sanitized data
     */
    public function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate date format (YYYY-MM-DD)
     * @param string $date Date to validate
     * @return bool True if valid, false otherwise
     */
    private function validateDate($date) {
        return (bool)strtotime($date);
    }

    /**
     * Create a new application
     * @param int $id_student Student ID
     * @param int $id_internship Internship ID
     * @param string $cv Path to CV file
     * @param string $cover_letter Path to cover letter file
     * @param string $application_date Application date (YYYY-MM-DD)
     * @return int|false ID of the created application or false on failure
     */
    public function createApplication($id_student, $id_internship, $cv, $cover_letter, $application_date = null) {
        try {
            // Set default application date to current date if not provided
            if ($application_date === null) {
                $application_date = date('Y-m-d');
            }

            // Validate date format
            if (!$this->validateDate($application_date)) {
                $this->error = 'Invalid application date format';
                return false;
            }

            $sql = "INSERT INTO application 
                    (id_student, id_internship, cv, cover_letter, application_date) 
                    VALUES (:id_student, :id_internship, :cv, :cover_letter, :application_date)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->bindParam(':cv', $cv);
            $stmt->bindParam(':cover_letter', $cover_letter);
            $stmt->bindParam(':application_date', $application_date);
            $stmt->execute();
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get an application by ID
     * @param int $id_app Application ID
     * @return array|false Application data or false if not found
     */
    public function getApplication($id_app) {
        try {
            $sql = "SELECT * FROM application WHERE id_app = :id_app";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_app', $id_app, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : false;
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get all applications for a specific student
     * @param int $id_student Student ID
     * @return array|false Array of applications or false on failure
     */
    public function getApplicationsByStudent($id_student) {
        try {
            $sql = "SELECT * FROM application WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get all applications for a specific internship
     * @param int $id_internship Internship ID
     * @return array|false Array of applications or false on failure
     */
    public function getApplicationsByInternship($id_internship) {
        try {
            $sql = "SELECT * FROM application WHERE id_internship = :id_internship";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get all applications
     * @return array|false Array of all applications or false on failure
     */
    public function getAllApplications() {
        try {
            $sql = "SELECT * FROM application";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Update an application's documents
     * @param int $id_app Application ID
     * @param string $cv New CV file path
     * @param string $cover_letter New cover letter file path
     * @return int|false Number of affected rows or false on failure
     */
    public function updateApplicationDocuments($id_app, $cv, $cover_letter) {
        try {
            $sql = "UPDATE application SET 
                    cv = :cv, 
                    cover_letter = :cover_letter 
                    WHERE id_app = :id_app";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_app', $id_app, PDO::PARAM_INT);
            $stmt->bindParam(':cv', $cv);
            $stmt->bindParam(':cover_letter', $cover_letter);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Delete an application
     * @param int $id_app Application ID
     * @return int|false Number of affected rows or false on failure
     */
    public function deleteApplication($id_app) {
        try {
            $sql = "DELETE FROM application WHERE id_app = :id_app";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_app', $id_app, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->error = 'Database error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get the last error message
     * @return string Error message
     */
    public function getError() {
        return $this->error;
    }
}