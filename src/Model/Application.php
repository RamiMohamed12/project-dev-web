<?php
// Location: src/Model/Application.php

class Application {
    private $conn;
    private $error;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getError() {
        return $this->error;
    }

    /**
     * Create a new application
     * 
     * @param int $studentId Student ID
     * @param int $internshipId Internship ID
     * @param string $motivationLetter Motivation letter text
     * @param string|null $cvPath Path to uploaded CV file (optional)
     * @param string|null $cvFilename Original filename of CV (optional)
     * @return bool Success or failure
     */
    public function createApplication($studentId, $internshipId, $motivationLetter, $cvPath = null, $cvFilename = null) {
        try {
            // If CV path is null, set a default value
            if ($cvPath === null) {
                $cvPath = "No CV uploaded";
            }
            
            // Updated column names to match your actual database structure
            $sql = "INSERT INTO application (id_student, id_internship, cover_letter, cv) 
                    VALUES (:student_id, :internship_id, :motivation_letter, :cv_path)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internshipId, PDO::PARAM_INT);
            $stmt->bindParam(':motivation_letter', $motivationLetter, PDO::PARAM_STR);
            $stmt->bindParam(':cv_path', $cvPath, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->error = "Error creating application: " . $e->getMessage();
            error_log("Application create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if student has already applied for an internship
     * 
     * @param int $studentId Student ID
     * @param int $internshipId Internship ID
     * @return bool True if already applied, false otherwise
     */
    public function hasApplied($studentId, $internshipId) {
        try {
            $sql = "SELECT COUNT(*) FROM application 
                    WHERE id_student = :student_id AND id_internship = :internship_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internshipId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ($stmt->fetchColumn() > 0);
        } catch (PDOException $e) {
            error_log("Application hasApplied error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all applications for a student
     * 
     * @param int $studentId Student ID
     * @return array List of applications with internship and company details
     */
    public function getStudentApplications($studentId) {
        try {
            // Ajout de logs pour le débogage
            error_log("Fetching applications for student ID: " . $studentId);
            
            // Requête simplifiée pour obtenir les applications
            $simpleSql = "SELECT * FROM application WHERE id_student = :student_id";
            $simpleStmt = $this->conn->prepare($simpleSql);
            $simpleStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $simpleStmt->execute();
            $applications = $simpleStmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($applications) . " applications");
            
            // Si aucune application n'est trouvée, retourner un tableau vide
            if (empty($applications)) {
                return [];
            }
            
            // Pour chaque application, récupérer les détails de l'offre et de l'entreprise
            foreach ($applications as &$app) {
                // Récupérer les détails de l'offre
                $internshipSql = "SELECT * FROM internship WHERE id_internship = :id_internship";
                $internshipStmt = $this->conn->prepare($internshipSql);
                $internshipStmt->bindParam(':id_internship', $app['id_internship'], PDO::PARAM_INT);
                $internshipStmt->execute();
                $internship = $internshipStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($internship) {
                    // Fusionner les détails de l'offre
                    foreach ($internship as $key => $value) {
                        if (!isset($app[$key])) {
                            $app[$key] = $value;
                        }
                    }
                    
                    // Récupérer les détails de l'entreprise
                    $companySql = "SELECT * FROM company WHERE id_company = :id_company";
                    $companyStmt = $this->conn->prepare($companySql);
                    $companyStmt->bindParam(':id_company', $internship['id_company'], PDO::PARAM_INT);
                    $companyStmt->execute();
                    $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($company) {
                        // Fusionner les détails de l'entreprise
                        foreach ($company as $key => $value) {
                            if (!isset($app[$key])) {
                                $app[$key] = $value;
                            }
                        }
                    }
                }
            }
            
            return $applications;
        } catch (PDOException $e) {
            $this->error = "Error retrieving applications: " . $e->getMessage();
            error_log("Application getStudentApplications error: " . $e->getMessage());
            return [];
        }
    }
}
?>
