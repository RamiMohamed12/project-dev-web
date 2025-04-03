<?php
// Location: /home/demy/project-dev-web/src/Model/user.php

// require_once __DIR__ . '/../../config/config.php'; // No longer needed here, passed in constructor

class User {
    private $conn;
    private $error = '';

    // Accepts PDO connection object
    public function __construct(PDO $conn) { // Changed parameter name for clarity
        if (!$conn) {
            // Throw exception if connection is invalid
            throw new InvalidArgumentException("Database connection is required and must be a valid PDO object.");
        }
        $this->conn = $conn;
        // Set error mode within constructor for consistency
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Keep sanitize method as is
    public function sanitize($data) {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

    // Keep getError method as is
    public function getError() {
        return $this->error;
    }

    // --- Modified Create Methods ---

    /**
     * Creates a student, optionally associating with the creating pilote.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string|null $location
     * @param string|null $phone
     * @param string $dob Date of Birth
     * @param string $year Academic year
     * @param string|null $description
     * @param int|null $creatorPiloteId ID of the pilote creating this student, NULL if admin created.
     * @return int|false Number of affected rows (usually 1) on success, false on failure.
     */
    public function createStudent($name, $email, $password, $location, $phone, $dob, $year, $description, $creatorPiloteId = null) {
        try {
            // Sanitize inputs (keep existing sanitization)
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);
            $dob = $this->sanitize($dob);
            $year = $this->sanitize($year); // Sanitize year string
            $description = $this->sanitize($description);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.'; return false;
            }
            if (empty($dob)) { // Add validation for required fields if needed
                $this->error = 'Error: Date of Birth is required.'; return false;
            }
            $validYears = ['1st', '2nd', '3rd', '4th', '5th'];
             if (!in_array($year, $validYears)) {
                $this->error = 'Error: Invalid year selected.'; return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Added created_by_pilote_id to SQL
            $sql = "INSERT INTO student (name, email, password, location, phone_number, date_of_birth, year, description, created_by_pilote_id)
                    VALUES (:name, :email, :password, :location, :phone, :dob, :year, :description, :creator_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':description', $description);
            // Bind creator ID: Use PDO::PARAM_INT if it's an integer, PDO::PARAM_NULL if it's null
            if ($creatorPiloteId !== null) {
                $stmt->bindParam(':creator_id', $creatorPiloteId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':creator_id', null, PDO::PARAM_NULL);
            }

            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { $this->error = 'Error: Email already exists.'; }
             else { $this->error = 'Error creating student: ' . $e->getMessage(); }
             error_log("DB Error createStudent: " . $e->getMessage()); // Log detailed error
             return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            error_log("General Error createStudent: " . $e->getMessage());
            return false;
        }
    }

    // createPilote - No changes needed in parameters, logic handled by controller authorization
    public function createPilote($name, $email, $password, $location, $phone) {
         try {
            $name = $this->sanitize($name); $email = $this->sanitize($email);
            $location = $this->sanitize($location); $phone = $this->sanitize($phone);
             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
             $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
             $sql = "INSERT INTO pilote (name, email, password, location, phone_number) VALUES (:name, :email, :password, :location, :phone)";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email);
             $stmt->bindParam(':password', $hashedPassword); $stmt->bindParam(':location', $location);
             $stmt->bindParam(':phone', $phone);
             $stmt->execute(); return $stmt->rowCount();
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { $this->error = 'Email already exists.'; }
             else { $this->error = 'DB Error creating pilote.'; }
             error_log("DB Error createPilote: " . $e->getMessage());
             return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            error_log("General Error createPilote: " . $e->getMessage());
            return false;
        }
    }

    // createAdmin - No changes needed, logic handled by controller authorization
    public function createAdmin($name, $email, $password) {
       try {
            $name = $this->sanitize($name); $email = $this->sanitize($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute(); return $stmt->rowCount();
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { $this->error = 'Email already exists.'; }
             else { $this->error = 'DB Error creating admin.'; }
             error_log("DB Error createAdmin: " . $e->getMessage());
             return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            error_log("General Error createAdmin: " . $e->getMessage());
            return false;
        }
    }

    // --- Modified Read Methods ---

    /**
     * Reads details for a single student, including creator ID.
     */
    public function readStudent($id_student) {
        try {
            // Include created_by_pilote_id in the SELECT
            $sql = "SELECT *, created_by_pilote_id FROM student WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Returns user data or false if not found
        } catch (PDOException $e) {
            $this->error = 'Error reading student.';
            error_log("DB Error readStudent (ID: $id_student): " . $e->getMessage());
            return false;
        }
    }

    // readPilote, readAdmin - no changes needed
    public function readPilote($id_pilote) {
         try {
            $sql = "SELECT * FROM pilote WHERE id_pilote = :id_pilote"; // Don't fetch password hash unless needed
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT);
            $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error reading pilote.'; error_log("DB Error readPilote: " . $e->getMessage()); return false;}
    }
     public function readAdmin($id_admin) {
         try {
            $sql = "SELECT * FROM admin WHERE id_admin = :id_admin"; // Don't fetch password hash unless needed
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
            $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error reading admin.'; error_log("DB Error readAdmin: " . $e->getMessage()); return false;}
    }

    /**
     * Gets all students, optionally filtered by the creating pilote ID.
     *
     * @param int|null $piloteId If provided, only returns students created by this pilote.
     * @return array|false Array of students or false on failure.
     */
    public function getAllStudents($piloteId = null) {
        try {
            $sql = "SELECT *, created_by_pilote_id FROM student"; // Select creator ID
            if ($piloteId !== null) {
                $sql .= " WHERE created_by_pilote_id = :pilote_id";
            }
            $sql .= " ORDER BY name ASC";

            $stmt = $this->conn->prepare($sql);
            if ($piloteId !== null) {
                $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching students.';
            error_log("DB Error getAllStudents (PiloteID: $piloteId): " . $e->getMessage());
            return false;
        }
    }

    // getAllPilotes, getAllAdmins - no changes needed
    public function getAllPilotes() {
        try {
            $sql = "SELECT id_pilote, name, email, location, phone_number, created_at, updated_at FROM pilote ORDER BY name ASC"; // Exclude password
            $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching pilotes.'; error_log("DB Error getAllPilotes: " . $e->getMessage()); return false;}
    }
    public function getAllAdmins() {
        try {
            $sql = "SELECT id_admin, name, email, created_at, updated_at FROM admin ORDER BY name ASC"; // Exclude password
            $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching admins.'; error_log("DB Error getAllAdmins: " . $e->getMessage()); return false;}
    }

    // Get by email methods - no changes needed for now
    public function getStudentByEmail($email) { /* ... keep existing ... */ }
    public function getPiloteByEmail($email) { /* ... keep existing ... */ }
    public function getAdminByEmail($email) { /* ... keep existing ... */ }


    // --- Update Methods (No change in signature needed, logic is in controller) ---
    // The controller will check ownership *before* calling these.
    public function updateStudent($id_student, $name, $email, $location, $phone, $dob, $year, $description, $password = null) {
         try {
            // Existing sanitization...
             $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location);
             $phone = $this->sanitize($phone); $dob = $this->sanitize($dob); $year = $this->sanitize($year);
             $description = $this->sanitize($description);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            if (empty($dob)) { $this->error = 'DOB required.'; return false; }
             $validYears = ['1st', '2nd', '3rd', '4th', '5th']; if (!in_array($year, $validYears)) { $this->error = 'Invalid year.'; return false; }

            $sql = "UPDATE student SET name = :name, email = :email, location = :location, phone_number = :phone, date_of_birth = :dob, year = :year, description = :description";
            if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT); $sql .= ", password = :password"; }
            $sql .= " WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email);
            $stmt->bindParam(':location', $location); $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':dob', $dob); $stmt->bindParam(':year', $year);
            $stmt->bindParam(':description', $description);
            if (!empty($password)) { $stmt->bindParam(':password', $hashedPassword); }
            $stmt->execute(); return true; // Return true on success
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $this->error = 'Error: Email already exists.'; }
            else { $this->error = 'Error updating student.'; }
            error_log("DB Error updateStudent (ID: $id_student): " . $e->getMessage());
            return false;
        } catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updateStudent: " . $e->getMessage()); return false; }
    }

    public function updatePilote($id_pilote, $name, $email, $location, $phone, $password = null) {
         try {
            // Existing sanitization...
             $name = $this->sanitize($name); $email = $this->sanitize($email);
             $location = $this->sanitize($location); $phone = $this->sanitize($phone);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }

            $sql = "UPDATE pilote SET name = :name, email = :email, location = :location, phone_number = :phone";
            if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT); $sql .= ", password = :password"; }
            $sql .= " WHERE id_pilote = :id_pilote";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email);
            $stmt->bindParam(':location', $location); $stmt->bindParam(':phone', $phone);
            if (!empty($password)) { $stmt->bindParam(':password', $hashedPassword); }
            $stmt->execute(); return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $this->error = 'Error: Email already exists.'; }
            else { $this->error = 'Error updating pilote.'; }
            error_log("DB Error updatePilote (ID: $id_pilote): " . $e->getMessage());
            return false;
        } catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updatePilote: " . $e->getMessage()); return false; }
    }

    public function updateAdmin($id_admin, $name, $email, $password = null) {
        try {
            // Existing sanitization...
            $name = $this->sanitize($name); $email = $this->sanitize($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }

            $sql = "UPDATE admin SET name = :name, email = :email";
            if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT); $sql .= ", password = :password"; }
            $sql .= " WHERE id_admin = :id_admin";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email);
            if (!empty($password)) { $stmt->bindParam(':password', $hashedPassword); }
            $stmt->execute(); return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $this->error = 'Error: Email already exists.'; }
            else { $this->error = 'Error updating admin.'; }
             error_log("DB Error updateAdmin (ID: $id_admin): " . $e->getMessage());
            return false;
        } catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updateAdmin: " . $e->getMessage()); return false; }
    }

    // --- Delete Methods ---
    // Delete methods need authorization check in the CONTROLLER before calling.
    public function deleteStudent($id_student) {
        try {
            $sql = "DELETE FROM student WHERE id_student = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_student, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = 'Error deleting student.';
             error_log("DB Error deleteStudent (ID: $id_student): " . $e->getMessage());
            // Handle foreign key constraints if necessary (e.g., applications exist)
            return false;
        }
    }
     public function deletePilote($id_pilote) {
         try {
            $sql = "DELETE FROM pilote WHERE id_pilote = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_pilote, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = 'Error deleting pilote.';
             error_log("DB Error deletePilote (ID: $id_pilote): " . $e->getMessage());
            // Handle foreign key constraints (e.g., students/companies created by them)
            return false;
        }
    }
     public function deleteAdmin($id_admin) {
         try {
            $sql = "DELETE FROM admin WHERE id_admin = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id_admin, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->error = 'Error deleting admin.';
            error_log("DB Error deleteAdmin (ID: $id_admin): " . $e->getMessage());
            return false;
        }
    }

}
?>
