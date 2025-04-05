<?php
// Location: src/Model/company.php

class Company {
    private $conn;
    public $error = '';

    public function __construct(PDO $conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function sanitize($data) {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }
     public function getError() {
        return $this->error;
    }

    /**
     * Creates a company with URL, optionally associating with the creating pilote.
     * Picture is NOT handled on create via this method for simplicity, add via edit.
     * @return int|false Number of affected rows or false
     */
    public function create($name, $location, $description, $email, $phone, $url = null, $creatorPiloteId = null) {
        try {
             // Validation
            if (empty($name) || empty($location) || empty($email) || empty($phone)) { $this->error = "Required fields missing."; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = "Invalid email format."; return false; }
            if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) { $this->error = "Invalid company URL format."; return false; }
            if (!preg_match('/^\+?[0-9\s\-()]+$/', $phone)) { $this->error = "Invalid phone number format."; return false; } // Allow more chars

             // Sanitize
             $name = $this->sanitize($name); $location = $this->sanitize($location); $description = $this->sanitize($description);
             $email = $this->sanitize($email); $phone = $this->sanitize($phone); $url = $this->sanitize($url);


            $sql = "INSERT INTO company (name_company, location, description, email, phone_number, company_url, created_by_pilote_id)
                    VALUES (:name, :location, :description, :email, :phone, :url, :creator_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':location', $location); $stmt->bindParam(':description', $description);
            $stmt->bindParam(':email', $email); $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':url', $url); // Bind URL
            if ($creatorPiloteId !== null) { $stmt->bindParam(':creator_id', $creatorPiloteId, PDO::PARAM_INT); }
             else { $stmt->bindValue(':creator_id', null, PDO::PARAM_NULL); }
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $this->error = 'Company email exists.'; } else { $this->error = 'DB Error creating company.'; }
            error_log("DB Error createCompany: " . $e->getMessage()); return false;
        } catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error createCompany: " . $e->getMessage()); return false; }
    }

    /**
     * Reads details for a single company, including picture and URL.
     */
    public function read($id_company) {
        try {
            // Select new columns
            $sql = "SELECT *, created_by_pilote_id, company_url, company_picture, company_picture_mime
                    FROM company WHERE id_company = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_company, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error reading company.';
            error_log("DB Error readCompany (ID: $id_company): " . $e->getMessage()); return false;
        }
    }

    /**
     * Gets all companies for list view, excluding picture BLOB. Includes URL.
     * Optionally filtered by the creating pilote ID.
     */
    public function readAll($piloteId = null) {
        try {
             // Select URL, exclude BLOB
            $sql = "SELECT id_company, name_company, location, description, email, phone_number, company_url,
                           number_of_students, created_at, updated_at, created_by_pilote_id, company_picture_mime
                    FROM company";
            if ($piloteId !== null) { $sql .= " WHERE created_by_pilote_id = :pilote_id"; }
            $sql .= " ORDER BY name_company ASC";
            $stmt = $this->conn->prepare($sql);
            if ($piloteId !== null) { $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT); }
            $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching companies.';
            error_log("DB Error readAllCompanies (PiloteID: $piloteId): " . $e->getMessage()); return false;
        }
    }

    /**
     * Updates company details, including URL and picture.
     * @param int $id Company ID
     * @param string $name, $location, $email, $phone
     * @param string|null $description, $url
     * @param string|null $pictureData Binary image data
     * @param string|null $pictureMime Mime type
     * @param bool $removePicture Flag to remove existing picture
     * @return bool True on success, false on failure.
     */
    public function update($id, $name, $location, $description, $email, $phone, $url = null, $pictureData = null, $pictureMime = null, $removePicture = false) {
        try {
            // Validation
             if (empty($name) || empty($location) || empty($email) || empty($phone)) { $this->error = "Required fields missing."; return false; }
             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = "Invalid email format."; return false; }
             if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) { $this->error = "Invalid company URL format."; return false; }
             if (!preg_match('/^\+?[0-9\s\-()]+$/', $phone)) { $this->error = "Invalid phone number format."; return false; }

             // Sanitize
             $name = $this->sanitize($name); $location = $this->sanitize($location); $description = $this->sanitize($description);
             $email = $this->sanitize($email); $phone = $this->sanitize($phone); $url = $this->sanitize($url);

            // Build SQL
            $sqlParts = [ "name_company = :name", "location = :location", "description = :description", "email = :email", "phone_number = :phone", "company_url = :url" ];
            $params = [ ':id' => $id, ':name' => $name, ':location' => $location, ':description' => $description, ':email' => $email, ':phone' => $phone, ':url' => $url ];

            // Handle picture update/removal
            if ($removePicture) {
                $sqlParts[] = "company_picture = NULL";
                $sqlParts[] = "company_picture_mime = NULL";
            } elseif ($pictureData !== null && $pictureMime !== null) {
                $sqlParts[] = "company_picture = :pic_data";
                $sqlParts[] = "company_picture_mime = :pic_mime";
                $params[':pic_data'] = $pictureData;
                $params[':pic_mime'] = $pictureMime;
            }

            $sql = "UPDATE company SET " . implode(', ', $sqlParts) . " WHERE id_company = :id";
            $stmt = $this->conn->prepare($sql);

            // Bind parameters dynamically
             foreach ($params as $key => &$value) {
                 if ($key === ':pic_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); }
                 elseif ($key === ':id') { $stmt->bindParam($key, $value, PDO::PARAM_INT); }
                 else { $stmt->bindParam($key, $value); }
             } unset($value);

            $stmt->execute();
            return true; // Return true on success
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { $this->error = 'Company email exists.'; } else { $this->error = 'Error updating company.'; }
             error_log("DB Error updateCompany (ID: $id): " . $e->getMessage()); return false;
        } catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updateCompany: " . $e->getMessage()); return false; }
    }

    // Delete method (remains unchanged)
    public function delete($id) {
        try {
            $sql = "DELETE FROM company WHERE id_company = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = 'Error deleting company.';
             if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                 $this->error = 'Cannot delete company, it has associated internships.';
             }
            error_log("DB Error deleteCompany (ID: $id): " . $e->getMessage());
            return false;
        }
    }
    // *** NEW RATING METHODS ***

    /**
     * Get the average rating and count for a specific company.
     *
     * @param int $companyId
     * @return array ['average' => float|null, 'count' => int]
     */
    public function getCompanyAverageRating($companyId) {
        if (!filter_var($companyId, FILTER_VALIDATE_INT)) {
             $this->error = 'Invalid company ID for rating.';
             return ['average' => null, 'count' => 0];
         }
        try {
            $sql = "SELECT AVG(rating_value) as average, COUNT(*) as count
                    FROM company_ratings
                    WHERE company_id = :company_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Return formatted results
            return [
                'average' => ($result && $result['count'] > 0) ? round((float)$result['average'], 2) : null,
                'count' => ($result) ? (int)$result['count'] : 0
            ];
        } catch (PDOException $e) {
            $this->error = "Error getting average rating: " . $e->getMessage();
            error_log($this->error);
            return ['average' => null, 'count' => 0]; // Return default on error
        }
    }

    /**
     * Check if a specific student has already rated a specific company.
     *
     * @param int $studentId
     * @param int $companyId
     * @return bool True if rated, false otherwise or on error.
     */
    public function hasStudentRatedCompany($studentId, $companyId) {
        if (!filter_var($studentId, FILTER_VALIDATE_INT) || !filter_var($companyId, FILTER_VALIDATE_INT)) {
            $this->error = 'Invalid student or company ID for checking rating.';
            return false; // Or perhaps throw an exception depending on desired handling
        }
         try {
             $sql = "SELECT COUNT(*) FROM company_ratings
                     WHERE student_id = :student_id AND company_id = :company_id";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
             $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
             $stmt->execute();
             return (int)$stmt->fetchColumn() > 0;
         } catch (PDOException $e) {
             $this->error = "Error checking if student rated company: " . $e->getMessage();
             error_log($this->error);
             return false; // Treat error as "not rated" for safety, but log it
         }
     }

     /**
      * Add a new rating for a company by a student.
      *
      * @param int $companyId
      * @param int $studentId
      * @param int $ratingValue (1-5)
      * @param string|null $comment
      * @return bool True on success, false on failure.
      */
     public function addRating($companyId, $studentId, $ratingValue, $comment = null) {
          // Validate input
         if (!filter_var($companyId, FILTER_VALIDATE_INT) || !filter_var($studentId, FILTER_VALIDATE_INT)) {
             $this->error = "Invalid company or student ID."; return false;
         }
         if (!filter_var($ratingValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]])) {
             $this->error = "Invalid rating value. Must be between 1 and 5."; return false;
         }
         $comment = ($comment === null) ? null : $this->sanitize(trim($comment)); // Sanitize comment

         try {
             // Optional: Check if already rated first if you don't rely solely on UNIQUE constraint
             // if ($this->hasStudentRatedCompany($studentId, $companyId)) {
             //     $this->error = "You have already rated this company.";
             //     return false;
             // }

             $sql = "INSERT INTO company_ratings (company_id, student_id, rating_value, comment)
                     VALUES (:company_id, :student_id, :rating_value, :comment)";
              // Using INSERT IGNORE is another way to handle duplicates if UNIQUE constraint exists
             // $sql = "INSERT IGNORE INTO company_ratings ...";

             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
             $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
             $stmt->bindParam(':rating_value', $ratingValue, PDO::PARAM_INT);
             $stmt->bindValue(':comment', $comment, $comment === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

             $stmt->execute();
             // Check if insert worked (rowCount > 0 if not using INSERT IGNORE)
             return $stmt->rowCount() > 0;

         } catch (PDOException $e) {
             // Check for duplicate entry if UNIQUE constraint is active
             if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                 $this->error = "You have already rated this company.";
             }
             // Check for foreign key violations (e.g., student or company doesn't exist)
             elseif (isset($e->errorInfo[1]) && ($e->errorInfo[1] == 1452)) {
                  $this->error = "Invalid company or student reference for rating.";
             }
              else {
                 $this->error = "Database error adding rating.";
             }
             error_log("DB Error addRating: " . $e->getMessage());
             return false;
         } catch (Exception $e) {
             $this->error = 'An unexpected error occurred while adding rating: ' . $e->getMessage();
             error_log("General Error addRating: " . $e->getMessage());
             return false;
         }
     }

}


?>
