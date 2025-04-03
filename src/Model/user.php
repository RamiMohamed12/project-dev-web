<?php

require_once __DIR__ . '/../../config/config.php';

class User {
    private $conn;
    private $error = '';

    public function __construct($conn) {
        if (!$conn) {
            throw new Exception("Database connection is required.");
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

    // Create a student
    public function createStudent($name, $email, $password, $location, $phone, $dob, $year, $description) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);
            $dob = $this->sanitize($dob); // Sanitize date
            $year = filter_var($year, FILTER_SANITIZE_NUMBER_INT); // Sanitize year as integer
            $description = $this->sanitize($description);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.';
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO student (name, email, password, location, phone_number, date_of_birth, year, description)
                    VALUES (:name, :email, :password, :location, :phone, :dob, :year, :description)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT); // Bind as integer
            $stmt->bindParam(':description', $description);
            $stmt->execute();
            return $stmt->rowCount(); // Returns 1 on success, 0 on failure typically
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { // Integrity constraint violation (e.g., duplicate email)
                $this->error = 'Error: Email already exists.';
            } else {
                $this->error = 'Error creating student: ' . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Create a pilote
    public function createPilote($name, $email, $password, $location, $phone) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);

             if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.';
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO pilote (name, email, password, location, phone_number)
                    VALUES (:name, :email, :password, :location, :phone)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                $this->error = 'Error: Email already exists.';
            } else {
                $this->error = 'Error creating pilote: ' . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Create an admin
    public function createAdmin($name, $email, $password) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.';
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admin (name, email, password)
                    VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                $this->error = 'Error: Email already exists.';
            } else {
                $this->error = 'Error creating admin: ' . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Read user methods
    public function readStudent($id_student) {
        try {
            $sql = "SELECT * FROM student WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error reading student: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function readPilote($id_pilote) {
        try {
            $sql = "SELECT * FROM pilote WHERE id_pilote = :id_pilote";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error reading pilote: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function readAdmin($id_admin) {
        try {
            $sql = "SELECT * FROM admin WHERE id_admin = :id_admin";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error reading admin: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllStudents() {
        try {
            $sql = "SELECT * FROM student ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching all students: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllPilotes() {
        try {
            $sql = "SELECT * FROM pilote ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching all pilotes: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getAllAdmins() {
        try {
            $sql = "SELECT * FROM admin ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching all admins: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getStudentByEmail($email) {
        try {
            $email = $this->sanitize($email);
            $sql = "SELECT * FROM student WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching student by email: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getPiloteByEmail($email) {
        try {
            $email = $this->sanitize($email);
            $sql = "SELECT * FROM pilote WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching pilote by email: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function getAdminByEmail($email) {
        try {
            $email = $this->sanitize($email);
            $sql = "SELECT * FROM admin WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = 'Error fetching admin by email: ' . $e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateStudent($id_student, $name, $email, $location, $phone, $dob, $year, $description, $password = null) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);
            $dob = $this->sanitize($dob); // Sanitize date
            $year = filter_var($year, FILTER_SANITIZE_NUMBER_INT); // Sanitize year as integer
            $description = $this->sanitize($description);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.';
                return false;
            }

            $sql = "UPDATE student SET name = :name, email = :email, location = :location, phone_number = :phone, date_of_birth = :dob, year = :year, description = :description";

            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
            }

            $sql .= " WHERE id_student = :id_student";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':id_student', $id_student, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':dob', $dob);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description);

            if (!empty($password)) {
                $stmt->bindParam(':password', $hashedPassword);
            }

            $stmt->execute();
            return true; // Return true on successful execution
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                $this->error = 'Error: Email already exists for another user.';
            } else {
                $this->error = 'Error updating student: ' . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updatePilote($id_pilote, $name, $email, $location, $phone, $password = null) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);
            $location = $this->sanitize($location);
            $phone = $this->sanitize($phone);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.';
                return false;
            }

            $sql = "UPDATE pilote SET name = :name, email = :email, location = :location, phone_number = :phone";

            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
            }

            $sql .= " WHERE id_pilote = :id_pilote";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':id_pilote', $id_pilote, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':phone', $phone);

            if (!empty($password)) {
                $stmt->bindParam(':password', $hashedPassword);
            }

            $stmt->execute();
             return true;
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                 $this->error = 'Error: Email already exists for another user.';
            } else {
                $this->error = 'Error updating pilote: ' . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateAdmin($id_admin, $name, $email, $password = null) {
        try {
            $name = $this->sanitize($name);
            $email = $this->sanitize($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error = 'Error: Invalid email format.';
                return false;
            }

            $sql = "UPDATE admin SET name = :name, email = :email";

            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
            }

            $sql .= " WHERE id_admin = :id_admin";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);

            if (!empty($password)) {
                $stmt->bindParam(':password', $hashedPassword);
            }

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                 $this->error = 'Error: Email already exists for another user.';
            } else {
                $this->error = 'Error updating admin: ' . $e->getMessage();
            }
            return false;
        } catch (Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            return false;
        }
    }

} // End of User class
