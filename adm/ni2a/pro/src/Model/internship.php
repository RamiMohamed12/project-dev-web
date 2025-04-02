<?php

require_once __DIR__ . '/../../config/config.php';

class Internship {
    private $conn;
    private $error = '';

    public function __construct($conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    // Create a new internship
    public function createInternship($title, $location, $duration, $description, $manager_name, $manager_email, $manager_phone) {
        try {
            $title = $this->sanitize($title);
            $location = $this->sanitize($location);
            $description = $this->sanitize($description);
            $manager_name = $this->sanitize($manager_name);
            $manager_email = $this->sanitize($manager_email);
            $manager_phone = $this->sanitize($manager_phone);

            $sql = "INSERT INTO internships (id_company, title, description, remuneration) 
            VALUES (:id_company, :title, :description, :remuneration)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id_company', $id_company);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':remuneration', $remuneration);
    $stmt->execute();
    return $this->conn->lastInsertId(); // Return the new ID
} catch (Exception $e) {
    $this->error = 'Error: ' . $e->getMessage();
    return false;
}
    }

    // Read a single internship
    public function readInternship($id) {
        try {
            $sql = "SELECT * FROM internships WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Update an internship
    public function updateInternship($id, $title, $location, $duration, $description, $manager_name, $manager_email, $manager_phone) {
        try {
            $title = $this->sanitize($title);
            $location = $this->sanitize($location);
            $description = $this->sanitize($description);
            $manager_name = $this->sanitize($manager_name);
            $manager_email = $this->sanitize($manager_email);
            $manager_phone = $this->sanitize($manager_phone);

            $sql = "UPDATE internships SET 
                    title = :title, 
                    location = :location, 
                    duration = :duration, 
                    description = :description, 
                    manager_name = :manager_name, 
                    manager_email = :manager_email, 
                    manager_phone = :manager_phone 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':duration', $duration);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':manager_name', $manager_name);
            $stmt->bindParam(':manager_email', $manager_email);
            $stmt->bindParam(':manager_phone', $manager_phone);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Delete an internship
    public function deleteInternship($id) {
        try {
            $sql = "DELETE FROM internships WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Get all internships
    public function getAllInternships() {
        try {
            $sql = "SELECT * FROM internships";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Get internships by location
    public function getInternshipsByLocation($location) {
        try {
            $sql = "SELECT * FROM internships WHERE location = :location";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':location', $location);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Get error message
    public function getError() {
        return $this->error;
    }
}
?>