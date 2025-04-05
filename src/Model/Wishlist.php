<?php
// Location: src/Model/Wishlist.php

class Wishlist {
    private $conn;
    private $error;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getError() {
        return $this->error;
    }

    /**
     * Add an internship to a student's wishlist
     * 
     * @param int $student_id The student ID
     * @param int $internship_id The internship ID
     * @return bool Success or failure
     */
    public function addToWishlist($student_id, $internship_id) {
        try {
            // Check if already in wishlist to avoid duplicates
            if ($this->isInWishlist($student_id, $internship_id)) {
                return true; // Already in wishlist, consider it a success
            }
            
            $sql = "INSERT INTO wishlist (id_student, id_internship, date_added) 
                    VALUES (:student_id, :internship_id, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internship_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->error = "Error adding to wishlist: " . $e->getMessage();
            error_log("Wishlist add error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all internships in a student's wishlist with company details
     * 
     * @param int $student_id The student ID
     * @return array List of internships in wishlist
     */
    public function getWishlist($student_id) {
        try {
            $sql = "SELECT i.*, c.name_company, c.location as company_location, 
                           c.company_picture, c.company_picture_mime, w.date_added
                    FROM wishlist w
                    JOIN internship i ON w.id_internship = i.id_internship
                    JOIN company c ON i.id_company = c.id_company
                    WHERE w.id_student = :student_id
                    ORDER BY w.date_added DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error retrieving wishlist: " . $e->getMessage();
            error_log("Wishlist get error: " . $e->getMessage());
            return [];
        }
    }

    // Remove an internship from student's wishlist
    public function removeFromWishlist($student_id, $internship_id) {
        try {
            $sql = "DELETE FROM wishlist WHERE id_student = :student_id AND id_internship = :internship_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internship_id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->error = "Error removing from wishlist.";
            error_log("DB Error removeFromWishlist: " . $e->getMessage());
            return false;
        }
    }

    // Get all wishlist items for a student with internship details
    public function getStudentWishlist($student_id) {
        try {
            $sql = "SELECT w.*, i.*, c.name_company, c.location as company_location, 
                    c.company_picture, c.company_picture_mime 
                    FROM wishlist w
                    JOIN internship i ON w.id_internship = i.id_internship
                    JOIN company c ON i.id_company = c.id_company
                    WHERE w.id_student = :student_id
                    ORDER BY w.id_wishlist DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error = "Error retrieving wishlist: " . $e->getMessage();
            error_log("DB Error getStudentWishlist: " . $e->getMessage());
            return false;
        }
    }

    // Check if an internship is in a student's wishlist
    public function isInWishlist($student_id, $internship_id) {
        try {
            $sql = "SELECT * FROM wishlist WHERE id_student = :student_id AND id_internship = :internship_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindParam(':internship_id', $internship_id, PDO::PARAM_INT);
            $stmt->execute();
            return ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("DB Error isInWishlist: " . $e->getMessage());
            return false;
        }
    }
}
?>
