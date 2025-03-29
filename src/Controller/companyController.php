<?php

require_once '../Model/company.php';
require_once '../../config/config.php';

class CompanyController {
    private $company;
    private $feedback = ''; 
    private $hasError = false;
    private $csrf_token;

    public function __construct($dbConnection) {
        $this->company = new Company($dbConnection);
        
        // Initialize CSRF protection
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->csrf_token = $_SESSION['csrf_token'];
    }

    public function getCsrfToken() {
        return $this->csrf_token;
    }

    public function getFeedback() {
        return $this->feedback;
    }

    public function hasError() {
        return $this->hasError;
    }

    // Helper method for validation
    private function validateCompanyData($name, $location, $email, $phone) {
        // Validate required fields
        if (empty($name) || empty($location)) {
            $this->feedback = 'Please fill in the company name and location.';
            $this->hasError = true;
            return false;
        }
        
        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->feedback = 'Please enter a valid email address.';
            $this->hasError = true;
            return false;
        }
        
        // Validate phone if provided
        if (!empty($phone) && !preg_match('/^[0-9+\-\s()]*$/', $phone)) {
            $this->feedback = 'Please enter a valid phone number.';
            $this->hasError = true;
            return false;
        }
        
        return true;
    }

    public function handleCreateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_company'])) {
            // CSRF protection
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->csrf_token) {
                $this->feedback = 'Security validation failed. Please try again.';
                $this->hasError = true;
                return false;
            }
            
            // Get inputs (no need to sanitize as model will do this)
            $name = $_POST['name'] ?? '';
            $location = $_POST['location'] ?? '';
            $description = $_POST['description'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';

            // Validate inputs based on business rules
            if (!$this->validateCompanyData($name, $location, $email, $phone)) {
                return false;
            }

            // Call the model (which handles sanitization)
            $result = $this->company->create($name, $location, $description, $email, $phone);

            if ($result) {
                $this->feedback = "Company created successfully. Rows affected: $result";
            } else {
                $this->feedback = "Failed to create company. Error: " . $this->company->error;
                $this->hasError = true;
            }
        }
    }

    public function handleReadRequest($id) {
        // Validate ID
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $this->feedback = "Invalid company ID.";
            $this->hasError = true;
            return false;
        }

        $result = $this->company->read($id);
        if ($result === false) {
            $this->feedback = "Failed to retrieve company. Error: " . $this->company->error;
            $this->hasError = true;
        }
        return $result;
    }

    public function handleUpdateRequest($id) {
        // Validate ID first
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $this->feedback = "Invalid company ID.";
            $this->hasError = true;
            return false;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company'])) {
            // CSRF protection
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->csrf_token) {
                $this->feedback = 'Security validation failed. Please try again.';
                $this->hasError = true;
                return false;
            }
            
            // Get inputs
            $name = $_POST['name'] ?? '';
            $location = $_POST['location'] ?? '';
            $description = $_POST['description'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';

            // Validate inputs
            if (!$this->validateCompanyData($name, $location, $email, $phone)) {
                return false;
            }

            $result = $this->company->update($id, $name, $location, $description, $email, $phone);

            if ($result) {
                $this->feedback = "Company updated successfully. Rows affected: $result";
            } else {
                $this->feedback = "Failed to update company. Error: " . $this->company->error;
                $this->hasError = true;
            }
        }
        
        // Get the company data for form pre-population
        return $this->company->read($id);
    }

    public function handleDeleteRequest($id) {
        // Validate ID
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            $this->feedback = "Invalid company ID.";
            $this->hasError = true;
            return false;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_company'])) {
            // CSRF protection
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $this->csrf_token) {
                $this->feedback = 'Security validation failed. Please try again.';
                $this->hasError = true;
                return false;
            }
            
            $result = $this->company->delete($id);
            if ($result) {
                $this->feedback = "Company deleted successfully. Rows affected: $result";
            } else {
                $this->feedback = "Failed to delete company. Error: " . $this->company->error;
                $this->hasError = true;
            }
        }
    }
}

// Initialize the controller
try {
    if (!isset($conn)) {
        throw new Exception("Database connection not initialized.");
    }
    $companyController = new CompanyController($conn);
} catch (Exception $e) {
    die("Error initializing controller: " . $e->getMessage());
}

?>