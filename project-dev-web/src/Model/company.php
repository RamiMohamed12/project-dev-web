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
}
?>
