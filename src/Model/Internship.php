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

    // Get all internships with company details (for student view / generic use)
    public function getAllInternshipsWithCompanyDetails($search = '', $sort = 'newest') {
        try {
            $sql = "SELECT i.*, c.name_company, c.company_picture, c.company_picture_mime
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company";

            // Add search condition if provided
            if (!empty($search)) {
                // Assuming 'title' and 'name_company' are searchable
                $sql .= " WHERE i.title LIKE :search OR c.name_company LIKE :search";
                 // Add other searchable fields if necessary, e.g., i.description
            }

            // Add sorting
            switch ($sort) {
                case 'oldest':
                    $sql .= " ORDER BY i.offre_date ASC";
                    break;
                // Example for remuneration if needed (adjust column name if different)
                // case 'remuneration_high':
                //     $sql .= " ORDER BY i.remuneration DESC";
                //     break;
                // case 'remuneration_low':
                //     $sql .= " ORDER BY i.remuneration ASC";
                //     break;
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


    // Read a single internship with company details AND company creator ID
    public function readInternship($id_internship) {
        try {
            // Select internship details, internship creator, company details, company creator
            $sql = "SELECT i.*, i.created_by_pilote_id AS internship_creator_id,
                           c.name_company, c.company_picture, c.company_picture_mime, c.created_by_pilote_id AS company_creator_id
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company
                    WHERE i.id_internship = :id_internship";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            $this->error = 'Error reading internship.';
            error_log("DB Error readInternship (ID: $id_internship): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Read all internships (Primarily for Admin view or general lists)
     * Includes company details AND relevant creator IDs.
     *
     * @param array|null $allowedCompanyIds Optional array of company IDs to filter by (Not used in Admin flow, potentially useful elsewhere)
     * @return array|false Returns an array of internships or false on failure
     */
    public function readAll($allowedCompanyIds = null) { // Kept param for potential future use, but default Admin flow doesn't use it
        try {
            // Select internship details, internship creator, company details, company creator
             $sql = "SELECT i.*, i.created_by_pilote_id AS internship_creator_id,
                           c.name_company, c.company_picture, c.company_picture_mime, c.created_by_pilote_id AS company_creator_id
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company";

            // Filter logic (if needed, e.g., if a pilote were restricted only to specific companies)
            if ($allowedCompanyIds !== null && is_array($allowedCompanyIds) && !empty($allowedCompanyIds)) {
                // Ensure all IDs are integers for security
                $intCompanyIds = array_map('intval', $allowedCompanyIds);
                $placeholders = implode(',', array_fill(0, count($intCompanyIds), '?'));
                $sql .= " WHERE i.id_company IN ($placeholders)";
            }

            $sql .= " ORDER BY i.offre_date DESC"; // Or your preferred sorting

            $stmt = $this->conn->prepare($sql);

            // Bind company ID parameters if filtering
            if ($allowedCompanyIds !== null && is_array($allowedCompanyIds) && !empty($intCompanyIds)) {
                $paramIndex = 1;
                foreach ($intCompanyIds as $companyId) {
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

    /**
     * Read all internships relevant to a specific pilote.
     * Fetches internships where the company is managed by the pilote
     * OR the internship itself was created by the pilote.
     * Includes company details AND relevant creator IDs.
     *
     * @param int $piloteId The ID of the logged-in pilote
     * @return array|false Returns an array of internships or false on failure
     */
    public function readAllForPilote($piloteId) {
        try {
            // Selects internship details, internship creator, company details, company creator
            $sql = "SELECT i.*, i.created_by_pilote_id AS internship_creator_id,
                           c.name_company, c.company_picture, c.company_picture_mime, c.created_by_pilote_id AS company_creator_id
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company
                    WHERE c.created_by_pilote_id = :pilote_id_company OR i.created_by_pilote_id = :pilote_id_internship
                    ORDER BY i.offre_date DESC"; // Or your preferred sorting

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pilote_id_company', $piloteId, PDO::PARAM_INT);
            $stmt->bindParam(':pilote_id_internship', $piloteId, PDO::PARAM_INT); // Bind same ID again for the OR condition

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error retrieving internships for pilote: " . $e->getMessage();
            error_log("DB Error readAllForPilote (Pilote ID: $piloteId): " . $e->getMessage());
            return false;
        }
    }


    /**
     * Create a new internship offer
     *
     * @param int $id_company The company ID offering the internship
     * @param string $title The title of the internship
     * @param string $description The description of the internship
     * @param float|string|null $remuneration The remuneration amount (can be null)
     * @param string $offre_date The date the offer was posted (YYYY-MM-DD format expected)
     * @param int|null $creatorPiloteId The ID of the pilote who created this (null for admin)
     * @return int|bool The new internship ID on success, false on failure
     */
    public function create($id_company, $title, $description, $remuneration, $offre_date, $creatorPiloteId = null) {
        // Basic validation before DB attempt
        if (empty($id_company) || empty($title) || empty($description) || empty($offre_date)) {
             $this->error = "Missing required fields for creating internship.";
             return false;
         }
         // Optional: Add date validation if needed
         // if (DateTime::createFromFormat('Y-m-d', $offre_date) === false) {
         //     $this->error = "Invalid offer date format. Please use YYYY-MM-DD.";
         //     return false;
         // }

        try {
            // Assumes 'created_by_pilote_id' column exists in 'internship' table
            $sql = "INSERT INTO internship (id_company, title, description, remuneration, offre_date, created_by_pilote_id)
                    VALUES (:id_company, :title, :description, :remuneration, :offre_date, :created_by_pilote_id)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_company', $id_company, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            // Bind remuneration as STR to handle NULL or numeric values correctly
            // Ensure $remuneration is actually null if empty or non-numeric before binding
            if ($remuneration === '' || !is_numeric($remuneration)) {
                $remuneration = null;
            }
            $stmt->bindParam(':remuneration', $remuneration, $remuneration === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':offre_date', $offre_date, PDO::PARAM_STR);
            // Bind creator ID, handling NULL correctly
            $stmt->bindParam(':created_by_pilote_id', $creatorPiloteId, $creatorPiloteId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            } else {
                $this->error = "Database error during internship creation: " . implode(" - ", $stmt->errorInfo());
                error_log("Internship create error: " . implode(" | ", $stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            $this->error = "Database exception during internship creation: " . $e->getMessage();
            error_log("Internship create exception: " . $e->getMessage());
            // Check for specific foreign key errors if helpful
            // if ($e->getCode() == '23000') { ... }
            return false;
        }
    }

    /**
     * Delete an internship offer.
     * IMPORTANT: This method assumes the database constraint `wishlist_ibfk_2` (or similar)
     *            has been configured with `ON DELETE CASCADE`. Otherwise, deletion will fail
     *            if any wishlist items reference this internship.
     *
     * @param int $id_internship The ID of the internship to delete
     * @return bool True on success, false on failure
     */
    public function delete($id_internship) {
        if (empty($id_internship) || !filter_var($id_internship, FILTER_VALIDATE_INT)) {
            $this->error = "Invalid ID provided for deletion.";
            return false;
        }
        try {
            $sql = "DELETE FROM internship WHERE id_internship = :id_internship";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $success = $stmt->execute();

            if (!$success) {
                 // Capture error before checking rowCount
                 $this->error = "Error deleting internship: " . implode(" - ", $stmt->errorInfo());
                 error_log("DB Error delete internship (ID: $id_internship): " . implode(" | ", $stmt->errorInfo()));
                 return false; // Return false on execution failure
            }

            // Check if any row was actually deleted AFTER successful execution
            if ($stmt->rowCount() == 0) {
                 $this->error = "Internship with ID $id_internship not found for deletion.";
                 return false; // Indicate not found
            }

             return true; // Return true only if execution succeeded AND rows were affected

        } catch (PDOException $e) {
            // This catch block will now primarily handle errors other than the FK constraint
            // IF ON DELETE CASCADE is properly set up. If it's NOT set up, the constraint
            // violation (SQLSTATE 23000) will likely be caught here.
            $this->error = "Database exception during internship deletion: " . $e->getMessage();
            error_log("DB Error delete internship exception (ID: $id_internship): " . $e->getMessage());

             // Optional: Provide a more user-friendly message for the constraint violation
             if ($e->getCode() == '23000') {
                 $this->error = "Database Error: Cannot delete this internship because it is referenced elsewhere (e.g., wishlists, applications). Please ensure `ON DELETE CASCADE` is set on the relevant foreign keys or remove references manually.";
             }

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
     * @param float|string|null $remuneration The remuneration amount (can be null)
     * @param string $offre_date The date the offer was posted (YYYY-MM-DD format expected)
     * @return bool True on success, false on failure
     */
    public function updateInternship($id_internship, $id_company, $title, $description, $remuneration, $offre_date) {
        // Basic validation
         if (empty($id_internship) || !filter_var($id_internship, FILTER_VALIDATE_INT) ||
             empty($id_company) || !filter_var($id_company, FILTER_VALIDATE_INT) ||
             empty($title) || empty($description) || empty($offre_date)) {
             $this->error = "Missing required fields for updating internship.";
             return false;
         }
         // Optional: Add date validation if needed
         // if (DateTime::createFromFormat('Y-m-d', $offre_date) === false) {
         //     $this->error = "Invalid offer date format. Please use YYYY-MM-DD.";
         //     return false;
         // }

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
             // Bind remuneration as STR to handle NULL or numeric values correctly
             if ($remuneration === '' || !is_numeric($remuneration)) {
                 $remuneration = null;
             }
            $stmt->bindParam(':remuneration', $remuneration, $remuneration === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':offre_date', $offre_date, PDO::PARAM_STR);

            $success = $stmt->execute();
            if (!$success) {
                $this->error = "Error updating internship: " . implode(" - ", $stmt->errorInfo());
                error_log("DB Error updateInternship (ID: $id_internship): " . implode(" | ", $stmt->errorInfo()));
                return false; // Return false on failure
            }

            // Optionally check rowCount, but success means the query ran without error.
            // Not finding the row or data being unchanged isn't necessarily a failure state.
            // if ($stmt->rowCount() == 0) {
            //     // $this->error = "Internship with ID $id_internship not found or data was unchanged.";
            //     // Decide if this should return false or true
            // }

            return true; // Return true if execute succeeded

        } catch (PDOException $e) {
            $this->error = "Database exception during internship update: " . $e->getMessage();
            error_log("DB Error updateInternship exception (ID: $id_internship): " . $e->getMessage());
            // Check for specific foreign key errors if helpful
            // if ($e->getCode() == '23000') { ... }
            return false;
        }
    }
}
?>
