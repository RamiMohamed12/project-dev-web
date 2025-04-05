<?php
// Location: /home/demy/project-dev-web/src/Model/user.php

class User {
    private $conn;
    private $error = '';

    public function __construct(PDO $conn) {
        if (!$conn) {
            throw new InvalidArgumentException("Database connection is required and must be a valid PDO object.");
        }
        $this->conn = $conn;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function sanitize($data) {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

    public function getError() {
        return $this->error;
    }

    // --- Create Methods (Unchanged from previous correct versions) ---
    public function createStudent($name, $email, $password, $location, $phone, $dob, $year, $description, $school = null, $creatorPiloteId = null) {
        // ... (Full implementation from previous correct version) ...
        try {
            if (empty($name) || empty($email) || empty($password) || empty($dob) || empty($year)) { $this->error = 'Required fields missing.'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $validYears = ['1st', '2nd', '3rd', '4th', '5th']; if (!in_array($year, $validYears)) { $this->error = 'Invalid year.'; return false; }
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location);
            $phone = $this->sanitize($phone); $dob = $this->sanitize($dob); $year = $this->sanitize($year);
            $description = $this->sanitize($description); $school = $this->sanitize($school);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO student (name, email, password, location, phone_number, date_of_birth, year, description, school, created_by_pilote_id)
                    VALUES (:name, :email, :password, :location, :phone, :dob, :year, :description, :school, :creator_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email); $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location); $stmt->bindParam(':phone', $phone); $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':year', $year); $stmt->bindParam(':description', $description); $stmt->bindParam(':school', $school);
            if ($creatorPiloteId !== null) { $stmt->bindParam(':creator_id', $creatorPiloteId, PDO::PARAM_INT); } else { $stmt->bindValue(':creator_id', null, PDO::PARAM_NULL); }
            $stmt->execute(); return $stmt->rowCount();
        } catch (PDOException $e) { if ($e->getCode() == 23000) { $this->error = 'Email exists.'; } else { $this->error = 'DB Error.'; } error_log("DB Error createStudent: " . $e->getMessage()); return false; }
        catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error createStudent: " . $e->getMessage()); return false; }
    }
    public function createPilote($name, $email, $password, $location, $phone) {
        // ... (Full implementation from previous correct version) ...
        try {
            if (empty($name) || empty($email) || empty($password)) { $this->error = 'Required fields missing.'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location); $phone = $this->sanitize($phone);
             $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
             $sql = "INSERT INTO pilote (name, email, password, location, phone_number) VALUES (:name, :email, :password, :location, :phone)";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email); $stmt->bindParam(':password', $hashedPassword);
             $stmt->bindParam(':location', $location); $stmt->bindParam(':phone', $phone);
             $stmt->execute(); return $stmt->rowCount();
        } catch (PDOException $e) { if ($e->getCode() == 23000) { $this->error = 'Email exists.'; } else { $this->error = 'DB Error.'; } error_log("DB Error createPilote: " . $e->getMessage()); return false; }
         catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error createPilote: " . $e->getMessage()); return false; }
    }
    public function createAdmin($name, $email, $password) {
        // ... (Full implementation from previous correct version) ...
       try {
            if (empty($name) || empty($email) || empty($password)) { $this->error = 'Required fields missing.'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $name = $this->sanitize($name); $email = $this->sanitize($email);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email); $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute(); return $stmt->rowCount();
        } catch (PDOException $e) { if ($e->getCode() == 23000) { $this->error = 'Email exists.'; } else { $this->error = 'DB Error.'; } error_log("DB Error createAdmin: " . $e->getMessage()); return false; }
        catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error createAdmin: " . $e->getMessage()); return false; }
    }

    // --- Read Single Methods (Fetch BLOB for single profile view) ---
    public function readStudent($id_student) {
        try { $sql = "SELECT *, created_by_pilote_id, school, profile_picture, profile_picture_mime FROM student WHERE id_student = :id_student"; $stmt = $this->conn->prepare($sql); $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
        catch (PDOException $e) { $this->error = 'Error reading student.'; error_log("DB Error readStudent (ID: $id_student): " . $e->getMessage()); return false; }
    }
    public function readPilote($id_pilote) {
         try { $sql = "SELECT *, profile_picture, profile_picture_mime FROM pilote WHERE id_pilote = :id_pilote"; $stmt = $this->conn->prepare($sql); $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
         catch (PDOException $e) { $this->error = 'Error reading pilote.'; error_log("DB Error readPilote (ID: $id_pilote): " . $e->getMessage()); return false;}
    }
    public function readAdmin($id_admin) {
         try { $sql = "SELECT *, profile_picture, profile_picture_mime FROM admin WHERE id_admin = :id_admin"; $stmt = $this->conn->prepare($sql); $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT); $stmt->execute(); return $stmt->fetch(PDO::FETCH_ASSOC); }
         catch (PDOException $e) { $this->error = 'Error reading admin.'; error_log("DB Error readAdmin (ID: $id_admin): " . $e->getMessage()); return false;}
    }

    // --- CORRECTED getAll Methods (Exclude BLOBs) ---
    public function getAllStudents($piloteId = null) {
        try {
            // ***** EXCLUDE `profile_picture` *****
            $sql = "SELECT id_student, name, email, location, phone_number, date_of_birth, year, description, school, created_by_pilote_id, profile_picture_mime FROM student";
            if ($piloteId !== null) { $sql .= " WHERE created_by_pilote_id = :pilote_id"; }
            $sql .= " ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            if ($piloteId !== null) { $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT); }
            $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching students list.'; error_log("DB Error getAllStudents (PiloteID: $piloteId): " . $e->getMessage()); return false; }
    }
    public function getAllPilotes() {
        try {
            // ***** EXCLUDE `profile_picture` *****
            $sql = "SELECT id_pilote, name, email, location, phone_number, created_at, updated_at, profile_picture_mime FROM pilote ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching pilotes list.'; error_log("DB Error getAllPilotes: " . $e->getMessage()); return false;}
    }
    public function getAllAdmins() {
        try {
             // ***** EXCLUDE `profile_picture` *****
            $sql = "SELECT id_admin, name, email, created_at, updated_at, profile_picture_mime FROM admin ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching admins list.'; error_log("DB Error getAllAdmins: " . $e->getMessage()); return false;}
    }

    // --- Get by email methods (Unchanged) ---
    public function getStudentByEmail($email) { /* ... */ }
    public function getPiloteByEmail($email) { /* ... */ }
    public function getAdminByEmail($email) { /* ... */ }

    // --- Update Methods (Unchanged from previous correct versions - handle BLOBs) ---
    public function updateStudent($id_student, $name, $email, $location, $phone, $dob, $year, $description, $school = null, $password = null, $profilePictureData = null, $profilePictureMime = null, $removeProfilePicture = false) {
        // ... (Full implementation from previous correct version) ...
        try {
            if (empty($name) || empty($email) || empty($dob) || empty($year)) { $this->error = 'Required fields missing.'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $validYears = ['1st', '2nd', '3rd', '4th', '5th']; if (!in_array($year, $validYears)) { $this->error = 'Invalid year.'; return false; }
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location);
            $phone = $this->sanitize($phone); $dob = $this->sanitize($dob); $year = $this->sanitize($year);
            $description = $this->sanitize($description); $school = $this->sanitize($school);
            $sqlParts = ["name = :name", "email = :email", "location = :location", "phone_number = :phone", "date_of_birth = :dob", "year = :year", "description = :description", "school = :school"];
            $params = [ ':id_student' => $id_student, ':name' => $name, ':email' => $email, ':location' => $location, ':phone' => $phone, ':dob' => $dob, ':year' => $year, ':description' => $description, ':school' => $school ];
            if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT); $sqlParts[] = "password = :password"; $params[':password'] = $hashedPassword; }
            if ($removeProfilePicture) { $sqlParts[] = "profile_picture = NULL"; $sqlParts[] = "profile_picture_mime = NULL"; }
            elseif ($profilePictureData !== null && $profilePictureMime !== null) { $sqlParts[] = "profile_picture = :pfp_data"; $sqlParts[] = "profile_picture_mime = :pfp_mime"; $params[':pfp_data'] = $profilePictureData; $params[':pfp_mime'] = $profilePictureMime; }
            $sql = "UPDATE student SET " . implode(', ', $sqlParts) . " WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => &$value) { if ($key === ':pfp_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); } elseif ($key === ':id_student') { $stmt->bindParam($key, $value, PDO::PARAM_INT); } else { $stmt->bindParam($key, $value); } } unset($value);
            $stmt->execute(); return true;
        } catch (PDOException $e) { if ($e->getCode() == 23000) { $this->error = 'Email exists.'; } else { $this->error = 'Error updating student.'; } error_log("DB Error updateStudent (ID: $id_student): " . $e->getMessage()); return false; }
        catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updateStudent: " . $e->getMessage()); return false; }
    }
    public function updatePilote($id_pilote, $name, $email, $location, $phone, $password = null, $profilePictureData = null, $profilePictureMime = null, $removeProfilePicture = false) {
        // ... (Full implementation from previous correct version) ...
         try {
            if (empty($name) || empty($email)) { $this->error = 'Required fields missing.'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location); $phone = $this->sanitize($phone);
            $sqlParts = ["name = :name", "email = :email", "location = :location", "phone_number = :phone"];
            $params = [ ':id_pilote' => $id_pilote, ':name' => $name, ':email' => $email, ':location' => $location, ':phone' => $phone ];
            if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT); $sqlParts[] = "password = :password"; $params[':password'] = $hashedPassword; }
            if ($removeProfilePicture) { $sqlParts[] = "profile_picture = NULL"; $sqlParts[] = "profile_picture_mime = NULL"; }
            elseif ($profilePictureData !== null && $profilePictureMime !== null) { $sqlParts[] = "profile_picture = :pfp_data"; $sqlParts[] = "profile_picture_mime = :pfp_mime"; $params[':pfp_data'] = $profilePictureData; $params[':pfp_mime'] = $profilePictureMime; }
            $sql = "UPDATE pilote SET " . implode(', ', $sqlParts) . " WHERE id_pilote = :id_pilote";
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => &$value) { if ($key === ':pfp_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); } elseif ($key === ':id_pilote') { $stmt->bindParam($key, $value, PDO::PARAM_INT); } else { $stmt->bindParam($key, $value); } } unset($value);
            $stmt->execute(); return true;
        } catch (PDOException $e) { if ($e->getCode() == 23000) { $this->error = 'Email exists.'; } else { $this->error = 'Error updating pilote.'; } error_log("DB Error updatePilote (ID: $id_pilote): " . $e->getMessage()); return false; }
        catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updatePilote: " . $e->getMessage()); return false; }
    }
    public function updateAdmin($id_admin, $name, $email, $password = null, $profilePictureData = null, $profilePictureMime = null, $removeProfilePicture = false) {
        // ... (Full implementation from previous correct version) ...
        try {
             if (empty($name) || empty($email)) { $this->error = 'Required fields missing.'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email.'; return false; }
            $name = $this->sanitize($name); $email = $this->sanitize($email);
            $sqlParts = ["name = :name", "email = :email"];
            $params = [ ':id_admin' => $id_admin, ':name' => $name, ':email' => $email ];
            if (!empty($password)) { $hashedPassword = password_hash($password, PASSWORD_DEFAULT); $sqlParts[] = "password = :password"; $params[':password'] = $hashedPassword; }
            if ($removeProfilePicture) { $sqlParts[] = "profile_picture = NULL"; $sqlParts[] = "profile_picture_mime = NULL"; }
            elseif ($profilePictureData !== null && $profilePictureMime !== null) { $sqlParts[] = "profile_picture = :pfp_data"; $sqlParts[] = "profile_picture_mime = :pfp_mime"; $params[':pfp_data'] = $profilePictureData; $params[':pfp_mime'] = $profilePictureMime; }
            $sql = "UPDATE admin SET " . implode(', ', $sqlParts) . " WHERE id_admin = :id_admin";
            $stmt = $this->conn->prepare($sql);
             foreach ($params as $key => &$value) { if ($key === ':pfp_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); } elseif ($key === ':id_admin') { $stmt->bindParam($key, $value, PDO::PARAM_INT); } else { $stmt->bindParam($key, $value); } } unset($value);
            $stmt->execute(); return true;
        } catch (PDOException $e) { if ($e->getCode() == 23000) { $this->error = 'Email exists.'; } else { $this->error = 'Error updating admin.'; } error_log("DB Error updateAdmin (ID: $id_admin): " . $e->getMessage()); return false; }
        catch (Exception $e) { $this->error = 'Error: ' . $e->getMessage(); error_log("General Error updateAdmin: " . $e->getMessage()); return false; }
    }

    // --- Delete Methods (Unchanged from previous correct versions) ---
    public function deleteStudent($id_student) { /* ... */ }
    public function deletePilote($id_pilote) { /* ... */ }
    public function deleteAdmin($id_admin) { /* ... */ }

    // --- Add or Modify these methods for Pagination ---

    /**
     * Get a paginated list of students.
     * @param int $limit Number of records per page.
     * @param int $offset Starting record number.
     * @param int|null $creatorPiloteId Optional filter by creator pilote ID.
     * @return array|false Array of students or false on failure.
     */
    public function getStudentsPaginated($limit, $offset, $creatorPiloteId = null) {
        try {
            $sql = "SELECT id_student, name, email, year, school, location, created_by_pilote_id
                    FROM student";
            $params = [];
            if ($creatorPiloteId !== null) {
                $sql .= " WHERE created_by_pilote_id = :creator_id";
                $params[':creator_id'] = $creatorPiloteId;
            }
            $sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error fetching paginated students: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get the total count of students.
     * @param int|null $creatorPiloteId Optional filter by creator pilote ID.
     * @return int|false Total count or false on failure.
     */
    public function getTotalStudentsCount($creatorPiloteId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM student";
            $params = [];
            if ($creatorPiloteId !== null) {
                $sql .= " WHERE created_by_pilote_id = :creator_id";
                $params[':creator_id'] = $creatorPiloteId;
            }
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->error = "Error counting students: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get a paginated list of pilotes.
     * @param int $limit Number of records per page.
     * @param int $offset Starting record number.
     * @return array|false Array of pilotes or false on failure.
     */
    public function getPilotesPaginated($limit, $offset) {
        try {
            $sql = "SELECT id_pilote, name, email, location
                    FROM pilote
                    ORDER BY name ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error fetching paginated pilotes: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get the total count of pilotes.
     * @return int|false Total count or false on failure.
     */
    public function getTotalPilotesCount() {
        try {
            $sql = "SELECT COUNT(*) FROM pilote";
            $stmt = $this->conn->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->error = "Error counting pilotes: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get a paginated list of admins.
     * @param int $limit Number of records per page.
     * @param int $offset Starting record number.
     * @return array|false Array of admins or false on failure.
     */
    public function getAdminsPaginated($limit, $offset) {
        try {
            $sql = "SELECT id_admin, name, email
                    FROM admin
                    ORDER BY name ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error fetching paginated admins: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get the total count of admins.
     * @return int|false Total count or false on failure.
     */
    public function getTotalAdminsCount() {
        try {
            $sql = "SELECT COUNT(*) FROM admin";
            $stmt = $this->conn->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->error = "Error counting admins: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }

} // End Class User
?>