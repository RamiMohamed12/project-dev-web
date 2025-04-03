<?php
// Location: src/Model/company.php (Example Structure)

class Company {
    private $conn;
    public $error = ''; // Make public or add getError()

    public function __construct(PDO $conn) {
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Creates a company, optionally associating with the creating pilote.
     *
     * @param string $name
     * @param string $location
     * @param string|null $description
     * @param string $email
     * @param string $phone
     * @param int|null $creatorPiloteId ID of the pilote creating this company, NULL if admin created.
     * @return int|false Number of affected rows (usually 1) on success, false on failure.
     */
    public function create($name, $location, $description, $email, $phone, $creatorPiloteId = null) {
        try {
             // Add basic validation if needed
            if (empty($name) || empty($location) || empty($email) || empty($phone)) {
                 $this->error = "Name, location, email, and phone are required."; return false;
             }
             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 $this->error = "Invalid email format."; return false;
             }
            if (!preg_match('/^\+?[0-9\s\-]+$/', $phone)) { // Allow +, digits, spaces, hyphens
                $this->error = "Invalid phone number format."; return false;
            }


            $sql = "INSERT INTO company (name_company, location, description, email, phone_number, created_by_pilote_id)
                    VALUES (:name, :location, :description, :email, :phone, :creator_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
             // Bind creator ID
            if ($creatorPiloteId !== null) {
                $stmt->bindParam(':creator_id', $creatorPiloteId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':creator_id', null, PDO::PARAM_NULL);
            }

            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $this->error = 'Error: Company email already exists.'; }
            else { $this->error = 'Error creating company.'; }
             error_log("DB Error createCompany: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reads details for a single company, including creator ID.
     */
    public function read($id_company) {
        try {
            $sql = "SELECT *, created_by_pilote_id FROM company WHERE id_company = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_company, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error reading company.';
            error_log("DB Error readCompany (ID: $id_company): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets all companies, optionally filtered by the creating pilote ID.
     *
     * @param int|null $piloteId If provided, only returns companies created by this pilote.
     * @return array|false Array of companies or false on failure.
     */
    public function readAll($piloteId = null) {
        try {
            $sql = "SELECT *, created_by_pilote_id FROM company"; // Select creator ID
            if ($piloteId !== null) {
                $sql .= " WHERE created_by_pilote_id = :pilote_id";
            }
            $sql .= " ORDER BY name_company ASC";

            $stmt = $this->conn->prepare($sql);
            if ($piloteId !== null) {
                $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching companies.';
            error_log("DB Error readAllCompanies (PiloteID: $piloteId): " . $e->getMessage());
            return false;
        }
    }

    // Update method - No change in signature needed, authorization is in controller
    public function update($id, $name, $location, $description, $email, $phone) {
        try {
             // Add validation if needed
            if (empty($name) || empty($location) || empty($email) || empty($phone)) {
                 $this->error = "Name, location, email, and phone are required."; return false;
             }
             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = "Invalid email format."; return false; }
             if (!preg_match('/^\+?[0-9\s\-]+$/', $phone)) { $this->error = "Invalid phone number format."; return false; }

            $sql = "UPDATE company SET name_company = :name, location = :location, description = :description, email = :email, phone_number = :phone
                    WHERE id_company = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            return true; // Return true on success
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { $this->error = 'Error: Company email already exists.'; }
             else { $this->error = 'Error updating company.'; }
             error_log("DB Error updateCompany (ID: $id): " . $e->getMessage());
            return false;
        }
    }

    // Delete method - No change needed, authorization is in controller
    public function delete($id) {
        try {
            $sql = "DELETE FROM company WHERE id_company = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = 'Error deleting company.';
            error_log("DB Error deleteCompany (ID: $id): " . $e->getMessage());
             // Handle foreign key constraints (e.g., internships exist)
            return false;
        }
    }
}
?>
