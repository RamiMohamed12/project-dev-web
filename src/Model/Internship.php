<?php
// Location: src/Model/Internship.php

class Internship {
    private $conn;
    private $table_name = "internship";
    public $error = '';

    // Properties corresponding to table columns + JOINed data
    public $id_internship;
    public $id_company;
    public $title;
    public $description;
    public $remuneration;
    public $offre_date;
    public $created_by_pilote_id;
    public $created_at;
    public $updated_at;

    // Read-only properties from JOINs
    public $company_name;
    public $company_location;
    public $company_picture_mime;
    public $company_creator_id; // Crucial for authorization

    public function __construct(PDO $db) {
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function sanitize($data) {
        return is_scalar($data) ? htmlspecialchars(strip_tags((string)$data), ENT_QUOTES, 'UTF-8') : $data;
    }

    public function getError() {
        return $this->error;
    }

    /**
     * Creates a new internship offer.
     * @param int $id_company
     * @param string $title
     * @param string $description
     * @param float|null $remuneration
     * @param string $offre_date (YYYY-MM-DD format)
     * @param int|null $creatorPiloteId (ID of pilote creating, NULL if admin)
     * @return int|false Last inserted ID on success, false on failure.
     */
    public function create($id_company, $title, $description, $remuneration, $offre_date, $creatorPiloteId = null) {
        // Validation
        if (empty($id_company) || empty($title) || empty($description) || empty($offre_date)) { $this->error = "Company, Title, Description, Offer Date required."; return false; }
        // Consider adding date validation

        $title = $this->sanitize($title);
        $description = strip_tags($description); // Or allow specific HTML

        $sql = "INSERT INTO " . $this->table_name . "
                (id_company, title, description, remuneration, offre_date, created_by_pilote_id)
                VALUES
                (:id_company, :title, :description, :remuneration, :offre_date, :creator_id)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_company', $id_company, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            if ($remuneration === null || $remuneration === '') { $stmt->bindValue(':remuneration', null, PDO::PARAM_NULL); }
            else { $stmt->bindParam(':remuneration', $remuneration); }
            $stmt->bindParam(':offre_date', $offre_date);
            if ($creatorPiloteId !== null) { $stmt->bindParam(':creator_id', $creatorPiloteId, PDO::PARAM_INT); }
            else { $stmt->bindValue(':creator_id', null, PDO::PARAM_NULL); }

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            $this->error = "Failed to execute statement."; return false;
        } catch (PDOException $e) {
            $this->error = "DB error creating internship.";
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                 if (strpos($e->getMessage(), 'id_company')) { $this->error = "Invalid Company selected."; }
                 if (strpos($e->getMessage(), 'created_by_pilote_id')) { $this->error = "Invalid Creator ID."; }
            }
            error_log("DB Error create Internship: " . $e->getMessage()); return false;
        }
    }

    /**
     * Reads details of a single internship, joining company info.
     * @param int $id_internship
     * @return array|false Associative array of internship details or false.
     */
    public function read($id_internship) {
        // Selects necessary fields including company details needed for display & auth
        $sql = "SELECT
                    i.*, -- Select all from internship table
                    c.name_company, c.location AS company_location,
                    c.company_picture_mime, -- For potential display
                    c.created_by_pilote_id AS company_creator_id -- Needed for Pilote Auth
                FROM " . $this->table_name . " i
                LEFT JOIN company c ON i.id_company = c.id_company
                WHERE i.id_internship = :id_internship
                LIMIT 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) { $this->error = "Internship not found."; return false; }
            return $row; // Return the full array
        } catch (PDOException $e) { $this->error = "DB error reading internship."; error_log("DB Error read Internship (ID: $id_internship): " . $e->getMessage()); return false; }
    }

    /**
     * Reads all internships, joining basic company info (no BLOBs).
     * Filters based on company ownership if an array of allowed company IDs is provided.
     * @param array|null $allowedCompanyIds Array of company IDs the user is allowed to see offers for (e.g., owned by pilote). Null means no restriction (admin or student view).
     * @return array|false Array of internships or false on error.
     */
    public function readAll(array $allowedCompanyIds = null) {
        // Exclude company picture BLOB
        $sql = "SELECT
                    i.id_internship, i.id_company, i.title, i.description, i.remuneration, i.offre_date,
                    i.created_by_pilote_id, i.created_at,
                    c.name_company, c.location AS company_location, c.company_picture_mime,
                    c.created_by_pilote_id AS company_creator_id
                FROM " . $this->table_name . " i
                LEFT JOIN company c ON i.id_company = c.id_company";

        $params = [];
        // If filtering by allowed companies (typically for a Pilote)
        if ($allowedCompanyIds !== null) {
            if (empty($allowedCompanyIds)) { return []; } // Return empty if pilote owns no companies
            // Create placeholders for IN clause: ?,?,?
            $placeholders = implode(',', array_fill(0, count($allowedCompanyIds), '?'));
            $sql .= " WHERE i.id_company IN (" . $placeholders . ")";
            $params = $allowedCompanyIds; // Parameters for the IN clause
        }

        $sql .= " ORDER BY i.offre_date DESC, i.created_at DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            // Execute with parameters if filtering, otherwise execute without
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "DB error fetching internships.";
            error_log("DB Error readAll Internships: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing internship offer.
     * @param int $id_internship
     * @param int $id_company
     * @param string $title
     * @param string $description
     * @param float|null $remuneration
     * @param string $offre_date
     * @return bool True on success, false on failure.
     */
    public function update($id_internship, $id_company, $title, $description, $remuneration, $offre_date) {
        // Validation
         if (empty($id_internship) || empty($id_company) || empty($title) || empty($description) || empty($offre_date)) { $this->error = "Required fields missing."; return false; }
        $title = $this->sanitize($title); $description = strip_tags($description);

        $sql = "UPDATE " . $this->table_name . "
                SET id_company = :id_company, title = :title, description = :description,
                    remuneration = :remuneration, offre_date = :offre_date
                WHERE id_internship = :id_internship";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->bindParam(':id_company', $id_company, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title); $stmt->bindParam(':description', $description);
            if ($remuneration === null || $remuneration === '') { $stmt->bindValue(':remuneration', null, PDO::PARAM_NULL); } else { $stmt->bindParam(':remuneration', $remuneration); }
            $stmt->bindParam(':offre_date', $offre_date);
            $stmt->execute();
            return $stmt->rowCount() > 0; // True if rows were affected
        } catch (PDOException $e) {
            $this->error = "DB error updating internship.";
             if (strpos($e->getMessage(), 'foreign key constraint fails') !== false && strpos($e->getMessage(), 'id_company') !== false) { $this->error = "Invalid Company selected."; }
            error_log("DB Error update Internship (ID: $id_internship): " . $e->getMessage()); return false;
        }
    }

    /**
     * Deletes an internship offer.
     * @param int $id_internship
     * @return bool True on success, false on failure.
     */
    public function delete($id_internship) {
        if (empty($id_internship)) { $this->error = "Internship ID required."; return false; }
        $sql = "DELETE FROM " . $this->table_name . " WHERE id_internship = :id_internship";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->execute(); return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = "DB error deleting internship.";
             if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) { $this->error = "Cannot delete offer with existing applications/wishlist items."; }
            error_log("DB Error delete Internship (ID: $id_internship): " . $e->getMessage()); return false;
        }
    }
} // End Class Internship
?>
