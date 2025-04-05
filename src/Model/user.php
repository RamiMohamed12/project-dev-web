<?php
// Location: /home/demy/project-dev-web/src/Model/user.php

class User {
    private $conn;
    private $error = '';

    // Constructor: Ensures a valid PDO connection is provided
    public function __construct(PDO $conn) {
        if (!$conn) {
            throw new InvalidArgumentException("Database connection is required and must be a valid PDO object.");
        }
        $this->conn = $conn;
        // Set error mode to exceptions for better error handling
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Basic sanitization helper
    public function sanitize($data) {
        // Use null coalescing operator for potentially null data
        return htmlspecialchars(strip_tags($data ?? ''), ENT_QUOTES, 'UTF-8');
    }

    // Getter for the last error message
    public function getError() {
        return $this->error;
    }

    // --- Create Methods ---
    public function createStudent($name, $email, $password, $location, $phone, $dob, $year, $description, $school = null, $creatorPiloteId = null) {
        try {
            // Basic Validation
            if (empty($name) || empty($email) || empty($password) || empty($dob) || empty($year)) {
                $this->error = 'Required fields missing (Name, Email, Password, DOB, Year).'; return false;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Invalid email format.'; return false;
            }
            $validYears = ['1st', '2nd', '3rd', '4th', '5th'];
            if (!in_array($year, $validYears)) {
                $this->error = 'Invalid year selected.'; return false;
            }

            // Sanitize all input data
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location);
            $phone = $this->sanitize($phone); $dob = $this->sanitize($dob); $year = $this->sanitize($year);
            $description = $this->sanitize($description); $school = $this->sanitize($school);

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                 throw new Exception("Password hashing failed.");
            }

            $sql = "INSERT INTO student (name, email, password, location, phone_number, date_of_birth, year, description, school, created_by_pilote_id)
                    VALUES (:name, :email, :password, :location, :phone, :dob, :year, :description, :school, :creator_id)";
            $stmt = $this->conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email); $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location); $stmt->bindParam(':phone', $phone); $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':year', $year); $stmt->bindParam(':description', $description);
            // Use bindValue for null checks
            $stmt->bindValue(':school', $school === null ? null : $school, $school === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':creator_id', $creatorPiloteId === null ? null : $creatorPiloteId, $creatorPiloteId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

            $stmt->execute();
            // Check if insert was successful (rowCount > 0)
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            // Check for duplicate email constraint violation (code 23000)
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
                $this->error = 'Email already exists.';
            } else {
                $this->error = 'Database error during student creation.';
            }
            error_log("DB Error createStudent: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->error = 'An unexpected error occurred: ' . $e->getMessage();
            error_log("General Error createStudent: " . $e->getMessage());
            return false;
        }
    }

    public function createPilote($name, $email, $password, $location, $phone) {
        try {
            // Basic Validation
            if (empty($name) || empty($email) || empty($password)) { $this->error = 'Required fields missing (Name, Email, Password).'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }

            // Sanitize inputs
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location); $phone = $this->sanitize($phone);

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
             if ($hashedPassword === false) { throw new Exception("Password hashing failed."); }

             $sql = "INSERT INTO pilote (name, email, password, location, phone_number) VALUES (:name, :email, :password, :location, :phone)";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email); $stmt->bindParam(':password', $hashedPassword);
             $stmt->bindParam(':location', $location); $stmt->bindParam(':phone', $phone);

             $stmt->execute();
             return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
             if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { $this->error = 'Email already exists.'; }
             else { $this->error = 'Database error during pilote creation.'; }
             error_log("DB Error createPilote: " . $e->getMessage());
             return false;
        } catch (Exception $e) {
            $this->error = 'An unexpected error occurred: ' . $e->getMessage();
            error_log("General Error createPilote: " . $e->getMessage());
            return false;
        }
    }

    public function createAdmin($name, $email, $password) {
       try {
            // Basic Validation
            if (empty($name) || empty($email) || empty($password)) { $this->error = 'Required fields missing (Name, Email, Password).'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }

            // Sanitize inputs
            $name = $this->sanitize($name); $email = $this->sanitize($email);

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) { throw new Exception("Password hashing failed."); }

            $sql = "INSERT INTO admin (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name); $stmt->bindParam(':email', $email); $stmt->bindParam(':password', $hashedPassword);

            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { $this->error = 'Email already exists.'; }
            else { $this->error = 'Database error during admin creation.'; }
            error_log("DB Error createAdmin: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->error = 'An unexpected error occurred: ' . $e->getMessage();
            error_log("General Error createAdmin: " . $e->getMessage());
            return false;
        }
    }

    // --- Read Single Methods (Fetch BLOB for single profile view) ---
    // These seem correct for fetching profile details including picture data
    public function readStudent($id_student) {
        if (!filter_var($id_student, FILTER_VALIDATE_INT)) { $this->error = 'Invalid student ID.'; return false; }
        try {
            $sql = "SELECT *, created_by_pilote_id, school, profile_picture, profile_picture_mime FROM student WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
             if (!$result) { $this->error = "Student not found."; return false; }
            return $result;
        } catch (PDOException $e) { $this->error = 'Error reading student.'; error_log("DB Error readStudent (ID: $id_student): " . $e->getMessage()); return false; }
    }
    public function readPilote($id_pilote) {
         if (!filter_var($id_pilote, FILTER_VALIDATE_INT)) { $this->error = 'Invalid pilote ID.'; return false; }
         try {
             $sql = "SELECT *, profile_picture, profile_picture_mime FROM pilote WHERE id_pilote = :id_pilote";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT);
             $stmt->execute();
             $result = $stmt->fetch(PDO::FETCH_ASSOC);
              if (!$result) { $this->error = "Pilote not found."; return false; }
             return $result;
         } catch (PDOException $e) { $this->error = 'Error reading pilote.'; error_log("DB Error readPilote (ID: $id_pilote): " . $e->getMessage()); return false;}
    }
    public function readAdmin($id_admin) {
         if (!filter_var($id_admin, FILTER_VALIDATE_INT)) { $this->error = 'Invalid admin ID.'; return false; }
         try {
             $sql = "SELECT *, profile_picture, profile_picture_mime FROM admin WHERE id_admin = :id_admin";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
             $stmt->execute();
             $result = $stmt->fetch(PDO::FETCH_ASSOC);
              if (!$result) { $this->error = "Admin not found."; return false; }
             return $result;
         } catch (PDOException $e) { $this->error = 'Error reading admin.'; error_log("DB Error readAdmin (ID: $id_admin): " . $e->getMessage()); return false;}
    }

    // --- getAll Methods (Exclude BLOBs for list views) ---
    // These seem correct as provided earlier
    public function getAllStudents($piloteId = null) {
        try {
            // Exclude `profile_picture` BLOB
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
            // Exclude `profile_picture` BLOB
            $sql = "SELECT id_pilote, name, email, location, phone_number, created_at, updated_at, profile_picture_mime FROM pilote ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching pilotes list.'; error_log("DB Error getAllPilotes: " . $e->getMessage()); return false;}
    }
    public function getAllAdmins() {
        try {
             // Exclude `profile_picture` BLOB
            $sql = "SELECT id_admin, name, email, created_at, updated_at, profile_picture_mime FROM admin ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $this->error = 'Error fetching admins list.'; error_log("DB Error getAllAdmins: " . $e->getMessage()); return false;}
    }

    // --- Get by email methods (Implementations Needed if used) ---
    // These are often needed for login authentication
    public function getStudentByEmail($email) {
         if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }
         try {
             $sql = "SELECT * FROM student WHERE email = :email";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':email', $email);
             $stmt->execute();
             return $stmt->fetch(PDO::FETCH_ASSOC); // Return user data or false if not found
         } catch (PDOException $e) { $this->error = 'Error fetching student by email.'; error_log("DB Error getStudentByEmail: " . $e->getMessage()); return false;}
     }
    public function getPiloteByEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }
        try {
             $sql = "SELECT * FROM pilote WHERE email = :email";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':email', $email);
             $stmt->execute();
             return $stmt->fetch(PDO::FETCH_ASSOC);
         } catch (PDOException $e) { $this->error = 'Error fetching pilote by email.'; error_log("DB Error getPiloteByEmail: " . $e->getMessage()); return false;}
     }
    public function getAdminByEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }
        try {
             $sql = "SELECT * FROM admin WHERE email = :email";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':email', $email);
             $stmt->execute();
             return $stmt->fetch(PDO::FETCH_ASSOC);
         } catch (PDOException $e) { $this->error = 'Error fetching admin by email.'; error_log("DB Error getAdminByEmail: " . $e->getMessage()); return false;}
     }

    // --- Update Methods (Handle BLOBs, optional password) ---
    public function updateStudent($id_student, $name, $email, $location, $phone, $dob, $year, $description, $school = null, $password = null, $profilePictureData = null, $profilePictureMime = null, $removeProfilePicture = false) {
        try {
            // Basic Validation
            if (empty($id_student) || !filter_var($id_student, FILTER_VALIDATE_INT)) { $this->error = 'Invalid student ID.'; return false; }
            if (empty($name) || empty($email) || empty($dob) || empty($year)) { $this->error = 'Required fields missing (Name, Email, DOB, Year).'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }
            $validYears = ['1st', '2nd', '3rd', '4th', '5th']; if (!in_array($year, $validYears)) { $this->error = 'Invalid year.'; return false; }

            // Sanitize inputs
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location);
            $phone = $this->sanitize($phone); $dob = $this->sanitize($dob); $year = $this->sanitize($year);
            $description = $this->sanitize($description); $school = $this->sanitize($school);

            // Build SQL query dynamically
            $sqlParts = ["name = :name", "email = :email", "location = :location", "phone_number = :phone", "date_of_birth = :dob", "year = :year", "description = :description", "school = :school"];
            $params = [ ':id_student' => $id_student, ':name' => $name, ':email' => $email, ':location' => $location, ':phone' => $phone, ':dob' => $dob, ':year' => $year, ':description' => $description, ':school' => $school ];

            // Handle password update
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                 if ($hashedPassword === false) { throw new Exception("Password hashing failed."); }
                $sqlParts[] = "password = :password";
                $params[':password'] = $hashedPassword;
            }

            // Handle profile picture update/removal
            if ($removeProfilePicture) {
                $sqlParts[] = "profile_picture = NULL"; $sqlParts[] = "profile_picture_mime = NULL";
            } elseif ($profilePictureData !== null && $profilePictureMime !== null) {
                $sqlParts[] = "profile_picture = :pfp_data"; $sqlParts[] = "profile_picture_mime = :pfp_mime";
                $params[':pfp_data'] = $profilePictureData; $params[':pfp_mime'] = $profilePictureMime;
            }

            // Construct final SQL
            $sql = "UPDATE student SET " . implode(', ', $sqlParts) . " WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);

            // Bind parameters carefully, especially LOB
            foreach ($params as $key => &$value) { // Use reference for bindParam
                if ($key === ':pfp_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); }
                elseif ($key === ':id_student') { $stmt->bindParam($key, $value, PDO::PARAM_INT); }
                // Use bindValue for potential nulls if not using reference binding strictly
                elseif ($key === ':school') { $stmt->bindValue($key, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR); }
                elseif ($key === ':password') { $stmt->bindParam($key, $value); } // Bind if exists
                else { $stmt->bindParam($key, $value); } // Default binding
            }
            unset($value); // Break reference

            $stmt->execute();
            // Check if any row was actually updated
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { $this->error = 'Email already exists for another user.'; }
            else { $this->error = 'Error updating student.'; }
            error_log("DB Error updateStudent (ID: $id_student): " . $e->getMessage()); return false;
        } catch (Exception $e) {
            $this->error = 'An unexpected error occurred during update: ' . $e->getMessage();
            error_log("General Error updateStudent: " . $e->getMessage()); return false;
        }
    }

    public function updatePilote($id_pilote, $name, $email, $location, $phone, $password = null, $profilePictureData = null, $profilePictureMime = null, $removeProfilePicture = false) {
         try {
            // Basic Validation
            if (empty($id_pilote) || !filter_var($id_pilote, FILTER_VALIDATE_INT)) { $this->error = 'Invalid pilote ID.'; return false; }
            if (empty($name) || empty($email)) { $this->error = 'Required fields missing (Name, Email).'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }

            // Sanitize inputs
            $name = $this->sanitize($name); $email = $this->sanitize($email); $location = $this->sanitize($location); $phone = $this->sanitize($phone);

            // Build SQL query dynamically
            $sqlParts = ["name = :name", "email = :email", "location = :location", "phone_number = :phone"];
            $params = [ ':id_pilote' => $id_pilote, ':name' => $name, ':email' => $email, ':location' => $location, ':phone' => $phone ];

            // Handle password update
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                 if ($hashedPassword === false) { throw new Exception("Password hashing failed."); }
                $sqlParts[] = "password = :password";
                $params[':password'] = $hashedPassword;
            }

            // Handle profile picture update/removal
            if ($removeProfilePicture) { $sqlParts[] = "profile_picture = NULL"; $sqlParts[] = "profile_picture_mime = NULL"; }
            elseif ($profilePictureData !== null && $profilePictureMime !== null) {
                 $sqlParts[] = "profile_picture = :pfp_data"; $sqlParts[] = "profile_picture_mime = :pfp_mime";
                 $params[':pfp_data'] = $profilePictureData; $params[':pfp_mime'] = $profilePictureMime;
            }

            // Construct final SQL
            $sql = "UPDATE pilote SET " . implode(', ', $sqlParts) . " WHERE id_pilote = :id_pilote";
            $stmt = $this->conn->prepare($sql);

            // Bind parameters
            foreach ($params as $key => &$value) {
                if ($key === ':pfp_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); }
                elseif ($key === ':id_pilote') { $stmt->bindParam($key, $value, PDO::PARAM_INT); }
                elseif ($key === ':password') { $stmt->bindParam($key, $value); } // Bind if exists
                else { $stmt->bindParam($key, $value); }
            }
            unset($value);

            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { $this->error = 'Email already exists for another user.'; }
            else { $this->error = 'Error updating pilote.'; }
            error_log("DB Error updatePilote (ID: $id_pilote): " . $e->getMessage()); return false;
        } catch (Exception $e) {
            $this->error = 'An unexpected error occurred during update: ' . $e->getMessage();
            error_log("General Error updatePilote: " . $e->getMessage()); return false;
        }
    }

    public function updateAdmin($id_admin, $name, $email, $password = null, $profilePictureData = null, $profilePictureMime = null, $removeProfilePicture = false) {
        try {
             // Basic Validation
            if (empty($id_admin) || !filter_var($id_admin, FILTER_VALIDATE_INT)) { $this->error = 'Invalid admin ID.'; return false; }
            if (empty($name) || empty($email)) { $this->error = 'Required fields missing (Name, Email).'; return false; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $this->error = 'Invalid email format.'; return false; }

            // Sanitize inputs
            $name = $this->sanitize($name); $email = $this->sanitize($email);

            // Build SQL query dynamically
            $sqlParts = ["name = :name", "email = :email"];
            $params = [ ':id_admin' => $id_admin, ':name' => $name, ':email' => $email ];

            // Handle password update
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                 if ($hashedPassword === false) { throw new Exception("Password hashing failed."); }
                $sqlParts[] = "password = :password";
                $params[':password'] = $hashedPassword;
            }

             // Handle profile picture update/removal
            if ($removeProfilePicture) { $sqlParts[] = "profile_picture = NULL"; $sqlParts[] = "profile_picture_mime = NULL"; }
            elseif ($profilePictureData !== null && $profilePictureMime !== null) {
                 $sqlParts[] = "profile_picture = :pfp_data"; $sqlParts[] = "profile_picture_mime = :pfp_mime";
                 $params[':pfp_data'] = $profilePictureData; $params[':pfp_mime'] = $profilePictureMime;
            }

            // Construct final SQL
            $sql = "UPDATE admin SET " . implode(', ', $sqlParts) . " WHERE id_admin = :id_admin";
            $stmt = $this->conn->prepare($sql);

             // Bind parameters
            foreach ($params as $key => &$value) {
                 if ($key === ':pfp_data') { $stmt->bindParam($key, $value, PDO::PARAM_LOB); }
                 elseif ($key === ':id_admin') { $stmt->bindParam($key, $value, PDO::PARAM_INT); }
                 elseif ($key === ':password') { $stmt->bindParam($key, $value); } // Bind if exists
                 else { $stmt->bindParam($key, $value); }
            }
            unset($value);

            $stmt->execute();
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
             if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { $this->error = 'Email already exists for another user.'; }
             else { $this->error = 'Error updating admin.'; }
             error_log("DB Error updateAdmin (ID: $id_admin): " . $e->getMessage()); return false;
        } catch (Exception $e) {
             $this->error = 'An unexpected error occurred during update: ' . $e->getMessage();
             error_log("General Error updateAdmin: " . $e->getMessage()); return false;
        }
    }

    // --- Delete Methods ---
    public function deleteStudent($id_student) {
        if (!filter_var($id_student, FILTER_VALIDATE_INT) || $id_student <= 0) {
             $this->error = 'Invalid student ID provided for deletion.';
             return false;
        }
        try {
            // Consider foreign key constraints: deleting a student might fail if they have applications etc.
            $sql = "DELETE FROM student WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->execute();
            // Check if a row was actually deleted
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                $this->error = 'Student not found or could not be deleted.';
                return false;
            }
        } catch (PDOException $e) {
             // Check for foreign key constraint violation (common issue)
             if (isset($e->errorInfo[1]) && ($e->errorInfo[1] == 1451 || $e->errorInfo[1] == 1217)) { // MySQL error codes
                 $this->error = 'Cannot delete student due to related records (e.g., applications).';
             } else {
                 $this->error = 'Database error deleting student.';
             }
             error_log("DB Error deleteStudent (ID: $id_student): " . $e->getMessage());
             return false;
         }
    }

    public function deletePilote($id_pilote) {
        if (!filter_var($id_pilote, FILTER_VALIDATE_INT) || $id_pilote <= 0) {
             $this->error = 'Invalid pilote ID provided for deletion.';
             return false;
        }
         try {
             // Consider foreign key constraints: Pilotes might be linked to students, companies, internships
             $sql = "DELETE FROM pilote WHERE id_pilote = :id_pilote";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT);
             $stmt->execute();
             if ($stmt->rowCount() > 0) {
                 return true;
             } else {
                 $this->error = 'Pilote not found or could not be deleted.';
                 return false;
             }
         } catch (PDOException $e) {
              if (isset($e->errorInfo[1]) && ($e->errorInfo[1] == 1451 || $e->errorInfo[1] == 1217)) {
                  $this->error = 'Cannot delete pilote due to related records (e.g., students, companies).';
              } else {
                  $this->error = 'Database error deleting pilote.';
              }
              error_log("DB Error deletePilote (ID: $id_pilote): " . $e->getMessage());
              return false;
          }
    }

    public function deleteAdmin($id_admin) {
        if (!filter_var($id_admin, FILTER_VALIDATE_INT) || $id_admin <= 0) {
             $this->error = 'Invalid admin ID provided for deletion.';
             return false;
        }
         try {
             // Admins might have fewer FK constraints, but check your schema
             $sql = "DELETE FROM admin WHERE id_admin = :id_admin";
             $stmt = $this->conn->prepare($sql);
             $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
             $stmt->execute();
             if ($stmt->rowCount() > 0) {
                 return true;
             } else {
                 $this->error = 'Admin not found or could not be deleted.';
                 return false;
             }
         } catch (PDOException $e) {
              $this->error = 'Database error deleting admin.'; // Less likely to be FK constraint
              error_log("DB Error deleteAdmin (ID: $id_admin): " . $e->getMessage());
              return false;
          }
    }

    // --- Pagination Methods ---
    // These seem correct as provided earlier
    public function getStudentsPaginated($limit, $offset, $creatorPiloteId = null) {
        try {
            // Exclude `profile_picture` BLOB for list performance
            $sql = "SELECT id_student, name, email, location, phone_number, date_of_birth,
                    year, description, school, created_by_pilote_id, profile_picture_mime
                    FROM student";
            $params = [];
            if ($creatorPiloteId !== null) {
                $sql .= " WHERE created_by_pilote_id = :creator_id";
                $params[':creator_id'] = $creatorPiloteId;
            }
            $sql .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            // Bind standard parameters first
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            // Bind dynamic parameters (creator_id if exists)
            foreach ($params as $key => $value) {
                 if ($key === ':creator_id') {
                     $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                 } else {
                      $stmt->bindValue($key, $value); // Fallback, though only creator_id is dynamic here
                 }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error fetching paginated students: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }
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
                 if ($key === ':creator_id') {
                     $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                 } else {
                      $stmt->bindValue($key, $value);
                 }
            }
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->error = "Error counting students: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }
    public function getPilotesPaginated($limit, $offset) {
        try {
            // Exclude `profile_picture` BLOB
            $sql = "SELECT id_pilote, name, email, location, phone_number, profile_picture_mime
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
    public function getTotalPilotesCount() {
        try {
            $sql = "SELECT COUNT(*) FROM pilote";
            // No parameters needed, use simple query
            $stmt = $this->conn->query($sql);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->error = "Error counting pilotes: " . $e->getMessage();
            error_log($this->error);
            return false;
        }
    }
    public function getAdminsPaginated($limit, $offset) {
        try {
             // Exclude `profile_picture` BLOB
            $sql = "SELECT id_admin, name, email, profile_picture_mime
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
    public function getTotalAdminsCount() {
        try {
            $sql = "SELECT COUNT(*) FROM admin";
             // No parameters needed
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