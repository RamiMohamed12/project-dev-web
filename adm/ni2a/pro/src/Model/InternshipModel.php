<?php
require_once __DIR__ . '/../../config/config.php';

class InternshipModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function countInternships() {
        try {
            $sql = "SELECT COUNT(*) as count FROM internships";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getInternshipGrowthPercentage() {
        // Simulation d'un pourcentage de croissance
        return -2; // -2% (diminution)
    }
    
    public function getAllInternships() {
        try {
            $sql = "SELECT i.*, c.name as company_name 
                    FROM internships i 
                    JOIN companies c ON i.company_id = c.id 
                    ORDER BY i.created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getInternshipById($id) {
        try {
            $sql = "SELECT i.*, c.name as company_name 
                    FROM internships i 
                    JOIN companies c ON i.company_id = c.id 
                    WHERE i.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createInternship($data) {
        try {
            $sql = "INSERT INTO internships (title, company_id, description, requirements, location, 
                    start_date, duration, is_paid, salary, application_deadline, status) 
                    VALUES (:title, :company_id, :description, :requirements, :location, 
                    :start_date, :duration, :is_paid, :salary, :application_deadline, :status)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':company_id', $data['company_id'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':requirements', $data['requirements'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':start_date', $data['start_date'], PDO::PARAM_STR);
            $stmt->bindParam(':duration', $data['duration'], PDO::PARAM_STR);
            $stmt->bindParam(':is_paid', $data['is_paid'], PDO::PARAM_BOOL);
            $stmt->bindParam(':salary', $data['salary'], PDO::PARAM_STR);
            $stmt->bindParam(':application_deadline', $data['application_deadline'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateInternship($id, $data) {
        try {
            $sql = "UPDATE internships SET 
                    title = :title, 
                    company_id = :company_id, 
                    description = :description, 
                    requirements = :requirements, 
                    location = :location, 
                    start_date = :start_date, 
                    duration = :duration, 
                    is_paid = :is_paid, 
                    salary = :salary, 
                    application_deadline = :application_deadline, 
                    status = :status 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':company_id', $data['company_id'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':requirements', $data['requirements'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':start_date', $data['start_date'], PDO::PARAM_STR);
            $stmt->bindParam(':duration', $data['duration'], PDO::PARAM_STR);
            $stmt->bindParam(':is_paid', $data['is_paid'], PDO::PARAM_BOOL);
            $stmt->bindParam(':salary', $data['salary'], PDO::PARAM_STR);
            $stmt->bindParam(':application_deadline', $data['application_deadline'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function deleteInternship($id) {
        try {
            $sql = "DELETE FROM internships WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>