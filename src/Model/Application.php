<?php
// Location: src/Model/Application.php

class Application {
    private $conn;
    private $error;
    private $table_name = "application";
    // Define allowed status values based on your application table's ENUM definition
    // This should match your `DESC application;` output for the status column
    private $allowed_statuses = ['pending', 'accepted', 'rejected'];

    public function __construct($db) {
        if (!$db) {
             // Handle the case where the database connection is not valid
             // You might throw an exception or log a fatal error
             throw new InvalidArgumentException("Database connection is required.");
        }
        $this->conn = $db;
        // It's generally good practice to set error mode in the connection setup (config.php)
        // but setting it here ensures it's set for this model's operations.
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getError() {
        return $this->error;
    }

    /**
     * Creates a new application record.
     *
     * @param int $studentId ID of the student applying.
     * @param int $internshipId ID of the internship being applied for.
     * @param string $motivationLetter The student's cover letter/motivation.
     * @param string|null $cvPath Relative path to the uploaded CV file, or null if none.
     * @return bool True on success, false on failure.
     */
    public function createApplication($studentId, $internshipId, $motivationLetter, $cvPath = null) {
        // Basic validation
        if (empty($studentId) || empty($internshipId) || trim($motivationLetter) === '') {
            $this->error = "Missing required application data (studentId, internshipId, motivationLetter).";
            return false;
        }

        // Ensure CV path is null if empty or just whitespace
        if ($cvPath !== null && trim($cvPath) === '') {
            $cvPath = null;
        }

        // SQL query using the correct column names
        $sql = "INSERT INTO " . $this->table_name . " (id_student, id_internship, cover_letter, cv, status, created_at)
                VALUES (:student_id, :internship_id, :motivation_letter, :cv_path, 'pending', NOW())";

        try {
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internshipId, PDO::PARAM_INT);
            $stmt->bindParam(':motivation_letter', $motivationLetter, PDO::PARAM_STR);

            // Bind CV path carefully, allowing NULL
            if ($cvPath === null) {
                $stmt->bindValue(':cv_path', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':cv_path', $cvPath, PDO::PARAM_STR);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            // Log detailed error for debugging
            error_log("Database Error in createApplication: " . $e->getMessage() . " (SQL: $sql)");
            // Set a user-friendly error message
            if ($e->getCode() == '23000') { // Integrity constraint violation (e.g., duplicate entry, foreign key fail)
                 // Check if it's likely a duplicate application based on unique constraints (if any)
                 if ($this->hasApplied($studentId, $internshipId)) {
                      $this->error = "You have already applied for this internship.";
                 } else {
                      $this->error = "Could not create application due to a data conflict. Please check the internship details.";
                 }
            } else {
                $this->error = "A database error occurred while submitting the application.";
            }
            return false;
        }
    }

    /**
     * Checks if a student has already applied for a specific internship.
     *
     * @param int $studentId ID of the student.
     * @param int $internshipId ID of the internship.
     * @return bool True if an application exists, false otherwise or on error.
     */
     public function hasApplied($studentId, $internshipId) {
        if (empty($studentId) || empty($internshipId)) {
            return false; // Invalid input
        }

        $sql = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE id_student = :student_id AND id_internship = :internship_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internshipId, PDO::PARAM_INT);
            $stmt->execute();
            // fetchColumn returns the value of the first column (COUNT(*)) or false if no rows
            $count = $stmt->fetchColumn();
            return ($count > 0);

        } catch (PDOException $e) {
            error_log("Database Error in hasApplied: " . $e->getMessage());
            $this->error = "Could not check existing applications."; // Keep error message generic for security
            return false; // Indicate an error occurred during the check
        }
    }

    /**
     * Retrieves all applications submitted by a specific student, along with internship and company details.
     *
     * @param int $studentId The ID of the student.
     * @return array|false An array of applications (associative arrays) or false on failure.
     */
    public function getStudentApplications($studentId) {
        if (empty($studentId)) {
            $this->error = "Student ID is required.";
            return false;
        }

        // Select specific columns for clarity and potential performance benefit
        // Use aliases for potentially conflicting column names (e.g., description)
        $sql = "SELECT
                    app.id_application, app.id_student, app.id_internship, app.cover_letter, app.cv,
                    app.status, app.feedback, app.created_at AS application_date,
                    i.title AS internship_title, i.description AS internship_description, i.remuneration,
                    i.offre_date, i.id_company,
                    c.name_company, c.location AS company_location, c.company_picture, c.company_picture_mime
                FROM " . $this->table_name . " app
                JOIN internship i ON app.id_internship = i.id_internship
                JOIN company c ON i.id_company = c.id_company
                WHERE app.id_student = :student_id
                ORDER BY app.created_at DESC";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database Error in getStudentApplications: " . $e->getMessage());
            $this->error = "Could not retrieve student applications.";
            return false;
        }
    }


    /**
     * Counts the number of applications for a specific internship.
     *
     * @param int $internshipId The ID of the internship.
     * @return int The number of applications, or 0 on error or if none.
     */
     public function countApplicationsForInternship(int $internshipId): int {
        if (empty($internshipId)) {
             return 0;
        }
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE id_internship = :id_internship";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_internship', $internshipId, PDO::PARAM_INT);
            $stmt->execute();
            // fetchColumn returns '0' if no applications, which (int) casts correctly
            return (int)$stmt->fetchColumn();
        } catch (PDOException $exception) {
            error_log("Database Error in countApplicationsForInternship: ".$exception->getMessage());
            $this->error = "Could not count applications.";
            return 0; // Return 0 on error
        }
    }

    // --- NEW METHOD for Statistics ---
    /**
     * Gets application statistics (total, pending, accepted, rejected) for a specific student.
     *
     * @param int $studentId The ID of the student.
     * @return array|false An associative array with counts ['total', 'pending', 'accepted', 'rejected'] or false on error.
     */
    public function getApplicationStatisticsByStudent(int $studentId) {
         if (empty($studentId)) {
             $this->error = "Student ID is required for statistics.";
             return false;
         }

        $stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];
        // Query to count applications grouped by their status for the given student
        $sql = "SELECT status, COUNT(*) as count
                FROM " . $this->table_name . "
                WHERE id_student = :student_id
                GROUP BY status";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->execute();

            $total_count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $status = strtolower($row['status']); // Ensure status is lowercase for key matching
                $count = (int)$row['count'];
                // Check if the status from DB is one we track
                if (array_key_exists($status, $stats)) {
                    $stats[$status] = $count;
                }
                $total_count += $count; // Increment total count regardless of tracked status
            }
            $stats['total'] = $total_count; // Assign the calculated total
            return $stats;

        } catch (PDOException $e) {
            $this->error = "DB Error fetching application stats: " . $e->getMessage();
            error_log($this->error . " (Student ID: $studentId)");
            return false; // Return false on database error
        }
    }

    // --- NEW METHOD for Recent Applications ---
    /**
     * Gets the most recent applications for a specific student, including internship and company details.
     *
     * @param int $studentId The ID of the student.
     * @param int $limit The maximum number of recent applications to retrieve. Defaults to 3.
     * @return array|false An array of associative arrays representing applications, or false on error.
     */
    public function getRecentApplicationsByStudent(int $studentId, int $limit = 3) {
        if (empty($studentId)) {
            $this->error = "Student ID is required for recent applications.";
            return false;
        }
        if ($limit <= 0) {
            $limit = 3; // Ensure a positive limit
        }

        // Select necessary fields including company picture info for display
        // Use aliases for clarity
        $sql = "SELECT
                    app.status, app.created_at AS application_date,
                    i.title AS internship_title,
                    c.name_company, c.company_picture, c.company_picture_mime
                FROM " . $this->table_name . " app
                JOIN internship i ON app.id_internship = i.id_internship
                JOIN company c ON i.id_company = c.id_company
                WHERE app.id_student = :student_id
                ORDER BY app.created_at DESC
                LIMIT :limit"; // Use named placeholder for limit

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            // Bind the limit as an integer
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $this->error = "DB Error fetching recent applications: " . $e->getMessage();
            error_log($this->error . " (Student ID: $studentId, Limit: $limit)");
            return false; // Return false on database error
        }
    }


    /**
     * Retrieves applications for management view (Admin or Pilote).
     * Can be filtered by the ID of the Pilote who created the associated internship.
     *
     * @param int|null $piloteId If provided, filters applications for internships created by this Pilote. If null, fetches all.
     * @return array|false An array of applications with student, internship, and company details, or false on failure.
     */
    public function getApplicationsForManagement($piloteId = null) {
        // Select specific, relevant columns with aliases
        $sql = "SELECT
                    app.id_application, app.id_student, app.id_internship, app.cv, app.cover_letter,
                    app.status, app.feedback, app.created_at AS application_date,
                    s.name AS student_name, s.email AS student_email,
                    i.title AS internship_title, i.created_by_pilote_id,
                    c.name_company, c.id_company
                FROM
                    " . $this->table_name . " app
                JOIN
                    student s ON app.id_student = s.id_student
                JOIN
                    internship i ON app.id_internship = i.id_internship
                JOIN
                    company c ON i.id_company = c.id_company";

        // Add WHERE clause conditionally for Pilote filtering
        if ($piloteId !== null && filter_var($piloteId, FILTER_VALIDATE_INT)) {
            $sql .= " WHERE i.created_by_pilote_id = :pilote_id";
        }
        $sql .= " ORDER BY app.created_at DESC"; // Order by application date

        try {
            $stmt = $this->conn->prepare($sql);

            // Bind pilote_id only if it's provided and valid
            if ($piloteId !== null && filter_var($piloteId, FILTER_VALIDATE_INT)) {
                $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database Error in getApplicationsForManagement: " . $e->getMessage());
            $this->error = "Could not retrieve applications for management.";
            return false;
        }
    }

    /**
     * Updates the status of a specific application.
     *
     * @param int $applicationId The ID of the application to update.
     * @param string $newStatus The new status ('pending', 'accepted', 'rejected').
     * @return bool True if the status was updated successfully, false otherwise.
     */
    public function updateStatus(int $applicationId, string $newStatus): bool
    {
         // Validate application ID
         if (empty($applicationId)) {
            $this->error = "Application ID is required.";
            return false;
         }

        // Normalize and validate the new status against the allowed list
        $newStatus = strtolower(trim($newStatus)); // Ensure lowercase and no extra whitespace
        if (!in_array($newStatus, $this->allowed_statuses)) {
            // Log the attempt with the invalid status for diagnostics
            error_log("Invalid status update attempt for App ID {$applicationId}. Provided status: '{$newStatus}'. Allowed: " . implode(', ', $this->allowed_statuses));
            $this->error = "Invalid status value provided ('" . htmlspecialchars($newStatus) . "'). Allowed statuses are: " . implode(', ', $this->allowed_statuses) . ".";
            return false;
        }

        // SQL to update the status using the correct primary key 'id_application'
        // Note: Your `application` table DESC output doesn't show an `updated_at` column. If it existed, you'd add `updated_at = NOW()` here.
        $sql = "UPDATE " . $this->table_name . "
                SET status = :new_status
                WHERE id_application = :application_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_STR);
            $stmt->bindParam(':application_id', $applicationId, PDO::PARAM_INT);
            $stmt->execute();

            // Check if any row was actually affected
            // rowCount() is reliable for UPDATE statements in PDO with MySQL/MariaDB
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Database Error in updateStatus for App ID {$applicationId}: " . $e->getMessage());
            $this->error = "A database error occurred while updating the application status.";
            return false;
        }
    }


    /**
     * Retrieves a single application by its ID, including the ID of the Pilote who created the associated internship.
     *
     * @param int $applicationId The ID of the application.
     * @return array|false An associative array with application details or false if not found or on error.
     */
    public function getApplicationById(int $applicationId) {
         if (empty($applicationId)) {
            $this->error = "Application ID is required.";
            return false;
         }

        // SQL to get application details and the pilote ID from the joined internship table
        // Use the correct primary key 'id_application'
        $sql = "SELECT app.*, i.created_by_pilote_id
                FROM " . $this->table_name . " app
                JOIN internship i ON app.id_internship = i.id_internship
                WHERE app.id_application = :application_id";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':application_id', $applicationId, PDO::PARAM_INT);
            $stmt->execute();
            // fetch returns the single row or false if not found
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                $this->error = "Application not found.";
            }
            return $result; // Returns the associative array or false

        } catch (PDOException $e) {
            error_log("Database Error in getApplicationById for App ID {$applicationId}: " . $e->getMessage());
            $this->error = "Could not retrieve application details.";
            return false;
        }
    }
}
?>
