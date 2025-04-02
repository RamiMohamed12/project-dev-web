<?php
require_once __DIR__ . '/../../config/config.php';

class UserModel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function countUsers() {
        try {
            $sql = "SELECT 
                    (SELECT COUNT(*) FROM students) +
                    (SELECT COUNT(*) FROM pilotes) +
                    (SELECT COUNT(*) FROM admins) as total_users";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_users'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getUserGrowthPercentage() {
        // Simulation d'un pourcentage de croissance
        return 5; // 5% de croissance
    }
    
    public function getRecentActivities($limit = 5) {
        // Cette méthode simule des activités récentes
        // Dans une application réelle, vous récupéreriez ces données de la base de données
        
        $activities = [
            [
                'type' => 'user',
                'title' => 'New User Registered',
                'description' => 'John Doe has registered as a student.',
                'time_ago' => '2 hours ago',
                'link' => '#',
                'link_text' => 'View Profile'
            ],
            [
                'type' => 'company',
                'title' => 'New Company Added',
                'description' => 'Tech Solutions Inc. has been added to the platform.',
                'time_ago' => '4 hours ago',
                'link' => '#',
                'link_text' => 'View Company'
            ],
            [
                'type' => 'internship',
                'title' => 'New Internship Posted',
                'description' => 'Web Developer Internship at Google has been posted.',
                'time_ago' => 'Yesterday',
                'link' => '#',
                'link_text' => 'View Internship'
            ],
            [
                'type' => 'application',
                'title' => 'Application Submitted',
                'description' => 'Sarah Johnson applied for Data Analyst position at Microsoft.',
                'time_ago' => '2 days ago',
                'link' => '#',
                'link_text' => 'Review Application'
            ],
            [
                'type' => 'user',
                'title' => 'User Profile Updated',
                'description' => 'Michael Brown updated his profile information.',
                'time_ago' => '3 days ago',
                'link' => '#',
                'link_text' => 'View Profile'
            ]
        ];
        
        return array_slice($activities, 0, $limit);
    }
    
    public function getAllStudents() {
        try {
            $sql = "SELECT * FROM students ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAllPilotes() {
        try {
            $sql = "SELECT * FROM pilotes ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAllAdmins() {
        try {
            $sql = "SELECT * FROM admins ORDER BY name ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getStudentById($id) {
        try {
            $sql = "SELECT * FROM students WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getPiloteById($id) {
        try {
            $sql = "SELECT * FROM pilotes WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAdminById($id) {
        try {
            $sql = "SELECT * FROM admins WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createStudent($data) {
        try {
            $sql = "INSERT INTO students (name, email, password, date_of_birth, year, location, phone_number, description) 
                    VALUES (:name, :email, :password, :date_of_birth, :year, :location, :phone_number, :description)";
            $stmt = $this->conn->prepare($sql);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth'], PDO::PARAM_STR);
            $stmt->bindParam(':year', $data['year'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createPilote($data) {
        try {
            $sql = "INSERT INTO pilotes (name, email, password, location, phone_number) 
                    VALUES (:name, :email, :password, :location, :phone_number)";
            $stmt = $this->conn->prepare($sql);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function createAdmin($data) {
        try {
            $sql = "INSERT INTO admins (name, email, password) 
                    VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateStudent($id, $data) {
        try {
            $sql = "UPDATE students SET 
                    name = :name, 
                    email = :email, 
                    date_of_birth = :date_of_birth, 
                    year = :year, 
                    location = :location, 
                    phone_number = :phone_number, 
                    description = :description 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth'], PDO::PARAM_STR);
            $stmt->bindParam(':year', $data['year'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updatePilote($id, $data) {
        try {
            $sql = "UPDATE pilotes SET 
                    name = :name, 
                    email = :email, 
                    location = :location, 
                    phone_number = :phone_number 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateAdmin($id, $data) {
        try {
            $sql = "UPDATE admins SET 
                    name = :name, 
                    email = :email 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function deleteStudent($id) {
        try {
            $sql = "DELETE FROM students WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function deletePilote($id) {
        try {
            $sql = "DELETE FROM pilotes WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function deleteAdmin($id) {
        try {
            $sql = "DELETE FROM admins WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>