<?php
// Location: src/Model/internship.php

class Internship {
    private $conn;
    private $error;

    public function __construct($connection) {
        if (!$connection || !($connection instanceof PDO)) {
            throw new InvalidArgumentException("Invalid database connection provided");
        }
        $this->conn = $connection;
        $this->error = null;
    }

    /**
     * Create a new internship offer
     *
     * @param string $title Title of the internship
     * @param string $description Description of the internship
     * @param int $companyId ID of the company offering the internship
     * @param float $remuneration Optional remuneration amount
     * @param string $offreDate Date of the offer
     * @param int|null $creatorPiloteId ID of the pilote creating this offer (not stored, for future use)
     * @return int|bool Returns the new offer ID on success, false on failure
     */
    public function create($title, $description, $companyId, $remuneration = null, $offreDate = null, $creatorPiloteId = null) {
        $this->error = null;

        // Basic validation
        if (empty($title) || empty($description) || empty($companyId)) {
            $this->error = "Title, description, and company are required.";
            return false;
        }

        // Use current date if none provided
        $offerDate = $offreDate ?? date('Y-m-d');

        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Insert into internship table - removed created_by_pilote_id
            $query = "INSERT INTO internship 
                     (id_company, title, description, remuneration, offre_date) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$companyId, $title, $description, $remuneration, $offerDate]);
            
            // Get the ID of the new internship
            $internshipId = $this->conn->lastInsertId();
            
            // Commit transaction
            $this->conn->commit();
            
            return $internshipId;
        } catch (PDOException $e) {
            // Rollback on error
            $this->conn->rollBack();
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get all internship offers with company info
     * 
     * @param int|null $creatorPiloteId Not used since column doesn't exist (for future use)
     * @return array|bool Array of internships or false on failure
     */
    public function readAll($creatorPiloteId = null) {
        $this->error = null;
        
        try {
            $query = "SELECT i.*, c.name_company 
                     FROM internship i
                     JOIN company c ON i.id_company = c.id_company";
            
            // Cannot filter by creator since column doesn't exist
            // For now, we return all internships regardless of creatorPiloteId
            
            $query .= " ORDER BY i.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get specific internship offer by ID
     * 
     * @param int $id Internship ID
     * @return array|bool The internship data or false on failure
     */
    public function read($id) {
        $this->error = null;
        
        if (empty($id)) {
            $this->error = "Internship ID is required.";
            return false;
        }
        
        try {
            $query = "SELECT i.*, c.name_company 
                     FROM internship i
                     JOIN company c ON i.id_company = c.id_company
                     WHERE i.id_internship = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                $this->error = "Internship not found.";
                return false;
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Delete an internship offer
     * 
     * @param int $id Internship ID
     * @return bool Success/failure
     */
    public function delete($id) {
        $this->error = null;
        
        if (empty($id)) {
            $this->error = "Internship ID is required.";
            return false;
        }
        
        try {
            // Check if the internship has applications
            $checkQuery = "SELECT COUNT(*) FROM application WHERE id_internship = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$id]);
            $count = $checkStmt->fetchColumn();
            
            if ($count > 0) {
                $this->error = "Cannot delete internship, it has associated applications.";
                return false;
            }
            
            // Check if in wishlists
            $wishlistCheck = "SELECT COUNT(*) FROM wishlist WHERE id_internship = ?";
            $wishlistStmt = $this->conn->prepare($wishlistCheck);
            $wishlistStmt->execute([$id]);
            $wishlistCount = $wishlistStmt->fetchColumn();
            
            if ($wishlistCount > 0) {
                $this->error = "Cannot delete internship, it is in students' wishlists.";
                return false;
            }
            
            // Delete the internship
            $query = "DELETE FROM internship WHERE id_internship = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = "Database error: " . $e->getMessage();
            
            // Specific error message for foreign key constraints
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $this->error = "Cannot delete internship, it has associated records.";
            }
            
            return false;
        }
    }

    /**
     * Update an existing internship offer
     * 
     * @param int $id Internship ID 
     * @param array $data Associative array of fields to update
     * @return bool Success/failure
     */
    public function update($id, $data) {
        $this->error = null;
        
        if (empty($id)) {
            $this->error = "Internship ID is required.";
            return false;
        }
        
        if (empty($data) || !is_array($data)) {
            $this->error = "No update data provided.";
            return false;
        }
        
        // Validate required fields if present
        if (isset($data['title']) && empty($data['title'])) {
            $this->error = "Title cannot be empty.";
            return false;
        }
        
        if (isset($data['description']) && empty($data['description'])) {
            $this->error = "Description cannot be empty.";
            return false;
        }
        
        if (isset($data['id_company']) && empty($data['id_company'])) {
            $this->error = "Company ID cannot be empty.";
            return false;
        }
        
        try {
            // Check if this internship exists
            $checkQuery = "SELECT id_internship FROM internship WHERE id_internship = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                $this->error = "Internship not found.";
                return false;
            }
            
            // Build update query
            $updateFields = [];
            $updateParams = [];
            
            // Add each field that needs updating
            if (isset($data['title'])) {
                $updateFields[] = "title = ?";
                $updateParams[] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = "description = ?";
                $updateParams[] = $data['description'];
            }
            
            if (isset($data['id_company'])) {
                $updateFields[] = "id_company = ?";
                $updateParams[] = $data['id_company'];
            }
            
            if (isset($data['remuneration'])) {
                $updateFields[] = "remuneration = ?";
                $updateParams[] = $data['remuneration'];
            }
            
            if (isset($data['offre_date'])) {
                $updateFields[] = "offre_date = ?";
                $updateParams[] = $data['offre_date'];
            }
            
            // No fields to update
            if (empty($updateFields)) {
                $this->error = "No valid fields to update.";
                return false;
            }
            
            // Complete the query
            $query = "UPDATE internship SET " . implode(', ', $updateFields);
            $query .= " WHERE id_internship = ?";
            
            // Add ID to parameters
            $updateParams[] = $id;
            
            // Execute update
            $stmt = $this->conn->prepare($query);
            $stmt->execute($updateParams);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get companies for dropdown selection
     * 
     * @param int|null $piloteId Only get companies created by this pilote (optional)
     * @return array|bool Company list or false on failure
     */
    public function getCompaniesForSelection($piloteId = null) {
        $this->error = null;
        
        try {
            $query = "SELECT id_company, name_company FROM company";
            $params = [];
            
            if ($piloteId !== null) {
                $query .= " WHERE created_by_pilote_id = ?";
                $params[] = $piloteId;
            }
            
            $query .= " ORDER BY name_company";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Database error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get the last error message
     * 
     * @return string|null The last error message or null if no error
     */
    public function getError() {
        return $this->error;
    }
}
?> 