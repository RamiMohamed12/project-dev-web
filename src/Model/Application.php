<?php
// Location: src/Model/Application.php

class Application {
    private $conn;
    private $error;
    private $table_name = "application"; // Added table name property for consistency

    public function __construct($db) {
        $this->conn = $db;
        // Optional: Set error mode if not done globally
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
     * @param string|null $cvPath Path to uploaded CV file (optional) - Assuming this stores the filename/path
     * @return bool Success or failure
     */
    public function createApplication($studentId, $internshipId, $motivationLetter, $cvPath = null) {
        try {
            if ($cvPath === null || trim($cvPath) === '') {
                 $cvPath = null;
            }

            $sql = "INSERT INTO " . $this->table_name . " (id_student, id_internship, cover_letter, cv, status)
                    VALUES (:student_id, :internship_id, :motivation_letter, :cv_path, 'pending')";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internshipId, PDO::PARAM_INT);
            $stmt->bindParam(':motivation_letter', $motivationLetter, PDO::PARAM_STR);

            if ($cvPath === null) {
                 $stmt->bindValue(':cv_path', null, PDO::PARAM_NULL);
            } else {
                 $stmt->bindParam(':cv_path', $cvPath, PDO::PARAM_STR);
            }

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
            $sql = "SELECT COUNT(*) FROM " . $this->table_name . "
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
     * Get all applications for a student, including internship and company details.
     *
     * @param int $studentId Student ID
     * @return array|false List of applications with merged details, or empty array if none, or false on DB error.
     */
    public function getStudentApplications($studentId) {
        try {
            // --- MODIFIED SQL QUERY: Removed i.duration ---
            $sql = "SELECT
                        app.*,
                        i.title, i.description AS internship_description,
                        -- i.duration,  -- <<< REMOVED THIS LINE
                        i.remuneration, i.offre_date, i.id_company,
                        c.name_company, c.location AS company_location, c.company_picture, c.company_picture_mime
                    FROM
                        " . $this->table_name . " app
                    JOIN
                        internship i ON app.id_internship = i.id_internship
                    JOIN
                        company c ON i.id_company = c.id_company
                    WHERE
                        app.id_student = :student_id
                    ORDER BY
                        app.created_at DESC";
            // --- END MODIFIED SQL QUERY ---

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $applications; // Returns empty array [] if no rows found

        } catch (PDOException $e) {
            $this->error = "Error retrieving applications: " . $e->getMessage();
            error_log("Application getStudentApplications error: " . $e->getMessage() . " (SQLSTATE: " . $e->getCode() . ")");
            return false; // Return false on database error
        }
    } 
    /**
     * Counts the number of applications submitted for a specific internship.
     *
     * @param int $internshipId The ID of the internship.
     * @return int The number of applications, or 0 on error.
     */
    public function countApplicationsForInternship(int $internshipId): int
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE id_internship = :id_internship";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_internship', $internshipId, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return (int)$count;

        } catch (PDOException $exception) {
            error_log("Database error in countApplicationsForInternship (Internship ID: $internshipId): " . $exception->getMessage());
            return 0;
        }
    }

    // --- Add other Application model methods here as needed ---

}
?>
