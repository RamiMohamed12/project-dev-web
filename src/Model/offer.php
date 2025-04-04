<?php
// Location: /home/demy/project-dev-web/src/Model/offer.php
require_once __DIR__ . '/../Config/Database.php';

class Offer {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Create a new internship offer
     * 
     * @param string $title Offer title
     * @param int $company_id Company ID
     * @param string $location Location of the internship
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param string $description Offer description
     * @param string $compensation Optional compensation details
     * @param string $skills_required Optional skills required
     * @param int $created_by_pilote_id ID of the pilote who created the offer (null if admin)
     * @return array Associative array with 'success' boolean and 'message' string
     */
    public function createOffer($title, $company_id, $location, $start_date, $end_date, $description, $compensation = null, $skills_required = null, $created_by_pilote_id = null) {
        try {
            $status = 'active'; // Default status for new offers
            
            $query = "INSERT INTO internship_offers 
                    (title, company_id, location, start_date, end_date, description, 
                    compensation, skills_required, status, created_by_pilote_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sisssssssi", 
                $title, 
                $company_id, 
                $location, 
                $start_date, 
                $end_date, 
                $description, 
                $compensation, 
                $skills_required, 
                $status, 
                $created_by_pilote_id
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Offer created successfully',
                    'id' => $this->conn->insert_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error creating offer: ' . $stmt->error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all internship offers with company name
     * 
     * @param int $pilote_id Optional pilote ID to filter offers by creator
     * @return array List of offers with company details
     */
    public function getAllOffers($pilote_id = null) {
        try {
            $query = "SELECT o.*, c.name_company 
                     FROM internship_offers o
                     JOIN companies c ON o.company_id = c.id_company";
            
            // If pilote ID is provided, filter offers created by that pilote
            if ($pilote_id !== null) {
                $query .= " WHERE o.created_by_pilote_id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $pilote_id);
            } else {
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $offers = [];
            while ($row = $result->fetch_assoc()) {
                $offers[] = $row;
            }
            
            return $offers;
        } catch (Exception $e) {
            // Return empty array with error message for logging
            error_log('Error fetching offers: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a single offer by ID with company name
     * 
     * @param int $id Offer ID
     * @return array|null Offer data or null if not found
     */
    public function getOfferById($id) {
        try {
            $query = "SELECT o.*, c.name_company 
                     FROM internship_offers o
                     JOIN companies c ON o.company_id = c.id_company
                     WHERE o.id_offer = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                return null;
            }
        } catch (Exception $e) {
            error_log('Error fetching offer by ID: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Delete an offer by ID
     * 
     * @param int $id Offer ID to delete
     * @param int $user_id ID of the user attempting to delete (for permission check)
     * @param string $role Role of the user attempting to delete ('admin' or 'pilote')
     * @return array Associative array with 'success' boolean and 'message' string
     */
    public function deleteOffer($id, $user_id, $role) {
        try {
            // First check if offer exists and get created_by_pilote_id
            $query = "SELECT created_by_pilote_id FROM internship_offers WHERE id_offer = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Offer not found'
                ];
            }
            
            $offer = $result->fetch_assoc();
            
            // Check permissions - admin can delete any, pilote can only delete their own
            if ($role === 'pilote' && $offer['created_by_pilote_id'] != $user_id) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to delete this offer'
                ];
            }
            
            // Proceed with deletion
            $query = "DELETE FROM internship_offers WHERE id_offer = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Offer deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error deleting offer: ' . $stmt->error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing internship offer
     * 
     * @param int $id Offer ID to update
     * @param array $data Associative array of fields to update
     * @param int $user_id ID of the user attempting to update (for permission check)
     * @param string $role Role of the user attempting to update ('admin' or 'pilote')
     * @return array Associative array with 'success' boolean and 'message' string
     */
    public function updateOffer($id, $data, $user_id, $role) {
        try {
            // First check if offer exists and get created_by_pilote_id
            $query = "SELECT created_by_pilote_id FROM internship_offers WHERE id_offer = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Offer not found'
                ];
            }
            
            $offer = $result->fetch_assoc();
            
            // Check permissions - admin can update any, pilote can only update their own
            if ($role === 'pilote' && $offer['created_by_pilote_id'] != $user_id) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to update this offer'
                ];
            }
            
            // Build the update query dynamically based on provided data
            $updateFields = [];
            $params = [];
            $types = '';
            
            $allowedFields = [
                'title' => 's', 
                'company_id' => 'i', 
                'location' => 's', 
                'start_date' => 's', 
                'end_date' => 's', 
                'description' => 's',
                'compensation' => 's', 
                'skills_required' => 's',
                'status' => 's'
            ];
            
            foreach ($allowedFields as $field => $type) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= $type;
                }
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No fields to update'
                ];
            }
            
            $query = "UPDATE internship_offers SET " . implode(', ', $updateFields);
            $query .= ", updated_at = NOW() WHERE id_offer = ?";
            
            $stmt = $this->conn->prepare($query);
            
            // Add the ID parameter
            $params[] = $id;
            $types .= 'i';
            
            // Create the full parameter array for bind_param
            $bindParams = array_merge([$types], $params);
            
            // Use reflection to call bind_param with dynamic arguments
            $refStmt = new ReflectionClass('mysqli_stmt');
            $refBindParam = $refStmt->getMethod('bind_param');
            $refBindParam->invokeArgs($stmt, $this->refValues($bindParams));
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Offer updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error updating offer: ' . $stmt->error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Helper method for dynamic parameter binding
     * 
     * @param array $arr Array to convert to references
     * @return array Array with references
     */
    private function refValues($arr) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    
    /**
     * Get companies for dropdown selection
     * 
     * @return array List of companies with id and name
     */
    public function getCompanies() {
        try {
            $query = "SELECT id_company, name_company FROM companies ORDER BY name_company";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $companies = [];
            while ($row = $result->fetch_assoc()) {
                $companies[] = $row;
            }
            
            return $companies;
        } catch (Exception $e) {
            error_log('Error fetching companies: ' . $e->getMessage());
            return [];
        }
    }
} 