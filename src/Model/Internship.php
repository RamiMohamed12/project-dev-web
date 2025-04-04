<?php
// Location: src/Model/Internship.php

class Internship {
    private $conn;
    private $error;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getError() {
        return $this->error;
    }

    // Get all internships with company details (for student view)
    public function getAllInternshipsWithCompanyDetails($search = '', $sort = 'newest') {
        try {
            $sql = "SELECT i.*, c.name_company, c.company_picture, c.company_picture_mime 
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company";
            
            // Add search condition if provided
            if (!empty($search)) {
                $sql .= " WHERE i.title LIKE :search OR i.location LIKE :search OR c.name_company LIKE :search";
            }
            
            // Add sorting
            switch ($sort) {
                case 'oldest':
                    $sql .= " ORDER BY i.offre_date ASC";
                    break;
                case 'salary_high':
                    $sql .= " ORDER BY i.salary DESC";
                    break;
                case 'salary_low':
                    $sql .= " ORDER BY i.salary ASC";
                    break;
                case 'newest':
                default:
                    $sql .= " ORDER BY i.offre_date DESC";
                    break;
            }
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind search parameter if provided
            if (!empty($search)) {
                $searchParam = '%' . $search . '%';
                $stmt->bindParam(':search', $searchParam);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error retrieving internships.';
            error_log("DB Error getAllInternshipsWithCompanyDetails: " . $e->getMessage());
            return [];
        }
    }

    // Add other existing methods here...
    
    // Read a single internship with company details
    public function readInternship($id_internship) {
        try {
            $sql = "SELECT i.*, c.name_company, c.company_picture, c.company_picture_mime 
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company
                    WHERE i.id_internship = :id_internship";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error reading internship.';
            error_log("DB Error readInternship (ID: $id_internship): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Read all internships
     * 
     * @param array|null $allowedCompanyIds Optional array of company IDs to filter by (for pilotes)
     * @return array|false Returns an array of internships or false on failure
     */
    public function readAll($allowedCompanyIds = null) {
        try {
            $sql = "SELECT i.*, c.name_company, c.company_picture, c.company_picture_mime 
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company";
            
            // If we need to filter by allowed company IDs (for pilotes)
            if ($allowedCompanyIds !== null) {
                if (empty($allowedCompanyIds)) {
                    // No companies to show
                    return [];
                }
                $placeholders = implode(',', array_fill(0, count($allowedCompanyIds), '?'));
                $sql .= " WHERE i.id_company IN ($placeholders)";
            }
            
            $sql .= " ORDER BY i.offre_date DESC";
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind company ID parameters if filtering
            if ($allowedCompanyIds !== null && !empty($allowedCompanyIds)) {
                $paramIndex = 1;
                foreach ($allowedCompanyIds as $companyId) {
                    $stmt->bindValue($paramIndex++, $companyId, PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error retrieving internships: " . $e->getMessage();
            error_log("DB Error readAll: " . $e->getMessage());
            return false;
        }
    }
    
    // Add this method to your Internship class
    
    /**
     * Create a new internship offer
     * 
     * @param int $id_company The company ID offering the internship
     * @param string $title The title of the internship
     * @param string $description The description of the internship
     * @param float|null $remuneration The remuneration amount (can be null)
     * @param string $offre_date The date the offer was posted
     * @param int|null $creatorPiloteId The ID of the pilote who created this (null for admin)
     * @return int|bool The new internship ID on success, false on failure
     */
    public function create($id_company, $title, $description, $remuneration, $offre_date, $creatorPiloteId = null) {
        try {
            $sql = "INSERT INTO internship (id_company, title, description, remuneration, offre_date, created_by_pilote_id) 
                    VALUES (:id_company, :title, :description, :remuneration, :offre_date, :created_by_pilote_id)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_company', $id_company, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':remuneration', $remuneration, PDO::PARAM_STR);
            $stmt->bindParam(':offre_date', $offre_date, PDO::PARAM_STR);
            $stmt->bindParam(':created_by_pilote_id', $creatorPiloteId, PDO::PARAM_INT);
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->error = "Database error: " . $e->getMessage();
            error_log("Internship create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete an internship offer
     * 
     * @param int $id_internship The ID of the internship to delete
     * @return bool True on success, false on failure
     */
    public function delete($id_internship) {
        try {
            $sql = "DELETE FROM internship WHERE id_internship = :id_internship";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->error = "Error deleting internship: " . $e->getMessage();
            error_log("DB Error delete internship (ID: $id_internship): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update an existing internship offer
     * 
     * @param int $id_internship The ID of the internship to update
     * @param int $id_company The company ID offering the internship
     * @param string $title The title of the internship
     * @param string $description The description of the internship
     * @param float|null $remuneration The remuneration amount (can be null)
     * @param string $offre_date The date the offer was posted
     * @return bool True on success, false on failure
     */
    public function updateInternship($id_internship, $id_company, $title, $description, $remuneration, $offre_date) {
        try {
            $sql = "UPDATE internship 
                    SET id_company = :id_company, 
                        title = :title, 
                        description = :description, 
                        remuneration = :remuneration, 
                        offre_date = :offre_date
                    WHERE id_internship = :id_internship";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->bindParam(':id_company', $id_company, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':remuneration', $remuneration, PDO::PARAM_STR);
            $stmt->bindParam(':offre_date', $offre_date, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->error = "Error updating internship: " . $e->getMessage();
            error_log("DB Error updateInternship (ID: $id_internship): " . $e->getMessage());
            return false;
        }
    }
}
?>
