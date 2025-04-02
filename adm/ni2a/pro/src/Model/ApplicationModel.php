<?php
require_once __DIR__ . '/../../config/config.php';

class ApplicationModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function countApplications() {
        try {
            $sql = "SELECT COUNT(*) as count FROM applications";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getApplicationGrowthPercentage() {
        // Simulation d'un pourcentage de croissance
        return 15; // 15% de croissance
    }
    
    public function getAllApplications() {
        try {
            $sql = "SELECT a.*, 
                    s.name as student_name, 
                    i.title as internship_title,
                    c.name as company_name
                    FROM applications a
                    JOIN students s ON a.student_id = s.id
                    JOIN internships i ON a.internship_id = i.id
                    JOIN companies c ON i.company_id = c.id
                    ORDER BY a.application_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getApplicationById($id) {
        try {
            $sql = "SELECT a.*, 
                    s.name as student_name, s.email as student_email,
                    i.title as internship_title,
                    c.name as company_name
                    FROM applications a
                    JOIN students s ON a.student_id = s.id
                    JOIN internships i ON a.internship_id = i.id
                    JOIN companies c ON i.company_id = c.id
                    WHERE a.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createApplication($data) {
        try {
            $sql = "INSERT INTO applications (student_id, internship_id, cover_letter, resume_path, status) 
                    VALUES (:student_id, :internship_id, :cover_letter, :resume_path, :status)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $data['student_id'], PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $data['internship_id'], PDO::PARAM_INT);
            $stmt->bindParam(':cover_letter', $data['cover_letter'], PDO::PARAM_STR);
            $stmt->bindParam(':resume_path', $data['resume_path'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateApplicationStatus($id, $status, $feedback = null) {
        try {
            $sql = "UPDATE applications SET status = :status, feedback = :feedback WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':feedback', $feedback, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function deleteApplication($id) {
        try {
            $sql = "DELETE FROM applications WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getApplicationsByStudentId($studentId) {
        try {
            $sql = "SELECT a.*, 
                    i.title as internship_title,
                    c.name as company_name
                    FROM applications a
                    JOIN internships i ON a.internship_id = i.id
                    JOIN companies c ON i.company_id = c.id
                    WHERE a.student_id = :student_id
                    ORDER BY a.application_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getApplicationsByInternshipId($internshipId) {
        try {
            $sql = "SELECT a.*, 
                    s.name as student_name, s.email as student_email
                    FROM applications a
                    JOIN students s ON a.student_id = s.id
                    WHERE a.internship_id = :internship_id
                    ORDER BY a.application_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':internship_id', $internshipId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>