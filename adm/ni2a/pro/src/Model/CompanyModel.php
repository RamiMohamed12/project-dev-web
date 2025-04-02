<?php
require_once __DIR__ . '/../../config/config.php';

class CompanyModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function countCompanies() {
        try {
            $sql = "SELECT COUNT(*) as count FROM companies";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            // En cas d'erreur, retourner 0
            return 0;
        }
    }
    
    public function getCompanyGrowthPercentage() {
        // Cette méthode simule un pourcentage de croissance
        // Dans une application réelle, vous compareriez avec les données du mois précédent
        return 8; // 8% de croissance
    }
    
    public function getAllCompanies() {
        try {
            $sql = "SELECT * FROM companies ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getCompanyById($id) {
        try {
            $sql = "SELECT * FROM companies WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createCompany($data) {
        try {
            $sql = "INSERT INTO companies (name, industry, location, description, website, contact_email, contact_phone) 
                    VALUES (:name, :industry, :location, :description, :website, :contact_email, :contact_phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':industry', $data['industry'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':website', $data['website'], PDO::PARAM_STR);
            $stmt->bindParam(':contact_email', $data['contact_email'], PDO::PARAM_STR);
            $stmt->bindParam(':contact_phone', $data['contact_phone'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateCompany($id, $data) {
        try {
            $sql = "UPDATE companies SET 
                    name = :name, 
                    industry = :industry, 
                    location = :location, 
                    description = :description, 
                    website = :website, 
                    contact_email = :contact_email, 
                    contact_phone = :contact_phone 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':industry', $data['industry'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':website', $data['website'], PDO::PARAM_STR);
            $stmt->bindParam(':contact_email', $data['contact_email'], PDO::PARAM_STR);
            $stmt->bindParam(':contact_phone', $data['contact_phone'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function deleteCompany($id) {
        try {
            $sql = "DELETE FROM companies WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>