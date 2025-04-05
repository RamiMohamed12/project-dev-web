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
    // NOTE: This version doesn't include company_creator_id by default
    // If needed elsewhere, modify its SQL too.
    public function getAllInternshipsWithCompanyDetails($search = '', $sort = 'newest') {
        try {
            $sql = "SELECT i.*, c.name_company, c.company_picture, c.company_picture_mime
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company";

            // Add search condition if provided
            if (!empty($search)) {
                // Make sure 'location' exists in your 'internship' table if you use it here
                $sql .= " WHERE i.title LIKE :search OR c.name_company LIKE :search"; // Removed i.location unless confirmed
                 // Add other searchable fields if necessary, e.g., i.description
            }

            // Add sorting
            switch ($sort) {
                case 'oldest':
                    $sql .= " ORDER BY i.offre_date ASC";
                    break;
                // Make sure 'salary' exists in your 'internship' table if you use it here
                // case 'salary_high':
                //     $sql .= " ORDER BY i.salary DESC"; // Column 'salary' assumed
                //     break;
                // case 'salary_low':
                //     $sql .= " ORDER BY i.salary ASC"; // Column 'salary' assumed
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
            // *** MODIFIED SQL: Added c.created_by_pilote_id AS company_creator_id ***
            $sql = "SELECT i.*, c.name_company, c.company_picture, c.company_picture_mime, c.created_by_pilote_id AS company_creator_id
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company
                    WHERE i.id_internship = :id_internship";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_internship', $id_internship, PDO::PARAM_INT);
            $stmt->execute();
            // Fetch also needs internship creator ID if it exists in the table
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Manually add created_by_pilote_id from internship table if it exists in the result
            // This assumes 'created_by_pilote_id' is directly selected by 'i.*'
            // If the column name is different, adjust accordingly.
            // This part is a bit redundant if 'i.*' already includes it, but safe to ensure it's present.
             if ($result && !isset($result['created_by_pilote_id'])) {
                 // Check if the column exists in the table schema before trying to access it
                 // This might require another query or prior knowledge of the schema.
                 // A simpler approach is to explicitly list it in the SELECT i.* if needed reliably.
                 // For now, we assume i.* includes it if it exists.
             }


            return $result;
        } catch (PDOException $e) {
            $this->error = 'Error reading internship.';
            error_log("DB Error readInternship (ID: $id_internship): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Read all internships (Primarily for Admin view or general lists)
     * Includes company details AND company creator ID.
     *
     * @param array|null $allowedCompanyIds Optional array of company IDs to filter by (for specific pilotes)
     * @return array|false Returns an array of internships or false on failure
     */
    public function readAll($allowedCompanyIds = null) {
        try {
            // *** MODIFIED SQL: Added c.created_by_pilote_id AS company_creator_id ***
            // Also explicitly selecting i.created_by_pilote_id to be sure
             $sql = "SELECT i.*, i.created_by_pilote_id AS internship_creator_id,
                           c.name_company, c.company_picture, c.company_picture_mime, c.created_by_pilote_id AS company_creator_id
                    FROM internship i
                    JOIN company c ON i.id_company = c.id_company";

            // If we need to filter by allowed company IDs (e.g., for pilotes using Option 1 in controller)
            if ($allowedCompanyIds !== null) {
                if (empty($allowedCompanyIds) || !is_array($allowedCompanyIds)) {
                    // No companies to show or invalid input
                    return [];
                }
                // Ensure all IDs are integers for security
                $intCompanyIds = array_map('intval', $allowedCompanyIds);
                $placeholders = implode(',', array_fill(0, count($intCompanyIds), '?'));
                $sql .= " WHERE i.id_company IN ($placeholders)";
            }

            $sql .= " ORDER BY i.offre_date DESC"; // Or your preferred sorting

            $stmt = $this->conn->prepare($sql);

            // Bind company ID parameters if filtering
            if ($allowedCompanyIds !== null && !empty($intCompanyIds)) {
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
     * *** NEW METHOD ***
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
            $query = "
                SELECT i.*, c.name_company, c.company_location, c.created_by_pilote_id AS company_creator_id
                FROM internships i
                INNER JOIN companies c ON i.id_company = c.id_company
                WHERE c.created_by_pilote_id = :piloteId
                   OR i.created_by_pilote_id = :piloteId
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':piloteId', $piloteId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in readAllForPilote: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Create a new internship offer
     *
     * @param int $id_company The company ID offering the internship
     * @param string $title The title of the internship
     * @param string $description The description of the internship
     * @param float|string|null $remuneration The remuneration amount (can be null) - Use PARAM_STR for flexibility
     * @param string $offre_date The date the offer was posted
     * @param int|null $creatorPiloteId The ID of the pilote who created this (null for admin)
     * @return int|bool The new internship ID on success, false on failure
     */
    public function create($id_company, $title, $description, $remuneration, $offre_date, $creatorPiloteId = null) {
        // Basic validation before DB attempt
        if (empty($id_company) || empty($title) || empty($description) || empty($offre_date)) {
             $this->error = "Missing required fields for creating internship.";
             return false;
         }

        try {
            // Assumes 'created_by_pilote_id' column exists in 'internship' table
            $sql = "INSERT INTO internship (id_company, title, description, remuneration, offre_date, created_by_pilote_id)
                    VALUES (:id_company, :title, :description, :remuneration, :offre_date, :created_by_pilote_id)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_company', $id_company, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            // Bind remuneration as STR to handle NULL or numeric values correctly
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
                 $this->error = "Error deleting internship: " . implode(" - ", $stmt->errorInfo());
                 error_log("DB Error delete internship (ID: $id_internship): " . implode(" | ", $stmt->errorInfo()));
             } elseif ($stmt->rowCount() == 0) {
                 $this->error = "Internship with ID $id_internship not found for deletion.";
                 return false; // Indicate not found rather than true success
             }
             return $success;
        } catch (PDOException $e) {
            $this->error = "Database exception during internship deletion: " . $e->getMessage();
            error_log("DB Error delete internship exception (ID: $id_internship): " . $e->getMessage());
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
     * @param string $offre_date The date the offer was posted
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
            $stmt->bindParam(':remuneration', $remuneration, $remuneration === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':offre_date', $offre_date, PDO::PARAM_STR);

            $success = $stmt->execute();
            if (!$success) {
                $this->error = "Error updating internship: " . implode(" - ", $stmt->errorInfo());
                error_log("DB Error updateInternship (ID: $id_internship): " . implode(" | ", $stmt->errorInfo()));
            }
            // Optionally check rowCount to see if any rows were actually affected
            // if ($success && $stmt->rowCount() == 0) {
            //     $this->error = "Internship with ID $id_internship not found or data was unchanged.";
            //     // return false; // Decide if this should be an error or just an indication
            // }
            return $success;

        } catch (PDOException $e) {
            $this->error = "Database exception during internship update: " . $e->getMessage();
            error_log("DB Error updateInternship exception (ID: $id_internship): " . $e->getMessage());
            return false;
        }
    }
 }
?>