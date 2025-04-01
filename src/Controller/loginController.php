<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/login.php';

class LoginController {
    private $loginModel;
    private $maxLoginAttempts = 3;
    private $lockoutTime = 900; // 15 minutes in seconds
    private $error = '';

    public function __construct($conn) {
        $this->loginModel = new Login($conn);
    }
    
    public function processLogin() {
        // Generate new CSRF token on GET requests or if it doesn't exist
        if ($_SERVER['REQUEST_METHOD'] === 'GET' || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verify CSRF token
                if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                    throw new Exception('Invalid request.');
                }

                // Check for brute force attempts
                if ($this->isIPBlocked()) {
                    throw new Exception('Too many login attempts. Please try again later.');
                }

                // Validate required fields
                $requiredFields = ['email', 'password', 'user_type'];
                foreach ($requiredFields as $field) {
                    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                        throw new Exception(ucfirst($field) . ' is required.');
                    }
                }

                // Sanitize and validate inputs
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = $_POST['password'];
                $userType = filter_var($_POST['user_type'], FILTER_SANITIZE_STRING);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format.');
                }

                if (!in_array($userType, ['student', 'pilote', 'admin'])) {
                    throw new Exception('Invalid user type.');
                }

                // Validate user credentials
                $userData = $this->loginModel->validateUser($email, $userType, $password);
                if ($userData) {
                    // Reset login attempts on successful login
                    $this->resetLoginAttempts();

                    // Set session variables
                    $_SESSION['user_id'] = $userData['id'];
                    $_SESSION['user_type'] = $userData['type'];
                    $_SESSION['user_name'] = htmlspecialchars($userData['name']);
                    $_SESSION['user_email'] = htmlspecialchars($userData['email']);
                    $_SESSION['last_activity'] = time();
                    
                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);
                    
                    // Build redirection path using absolute path
                    $basePath = '/newproject/src/View/';
                    $redirectPath = $basePath . strtolower($userType) . '.php';
                    
                    // Debug logs (ensure production logs do not expose sensitive info)
                    error_log("Redirecting to: " . $redirectPath);
                    error_log("User type: " . $userType);
                    error_log("User data: " . print_r($userData, true));
                    
                    header('Location: ' . $redirectPath);
                    exit();
                } else {
                    // Increment failed login attempts and show error
                    $this->incrementLoginAttempts();
                    throw new Exception('Invalid email or password.');
                }
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                error_log('Login error: ' . $this->error);
            }
        }
        
        // Do not regenerate the CSRF token here if a POST was processed.
        // Instead, use the token already set in the session.
        // Pass the error variable to the view if needed.
        include __DIR__ . '/../View/login.php';
    }

    private function isIPBlocked() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $attempts = isset($_SESSION['login_attempts'][$ip]) ? $_SESSION['login_attempts'][$ip] : ['count' => 0, 'time' => 0];
        
        if ($attempts['count'] >= $this->maxLoginAttempts) {
            $timePassed = time() - $attempts['time'];
            if ($timePassed < $this->lockoutTime) {
                return true;
            }
            // Reset attempts if lockout time has passed
            $this->resetLoginAttempts();
        }
        return false;
    }

    private function incrementLoginAttempts() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = ['count' => 0, 'time' => 0];
        }
        $_SESSION['login_attempts'][$ip]['count']++;
        $_SESSION['login_attempts'][$ip]['time'] = time();
    }

    private function resetLoginAttempts() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $_SESSION['login_attempts'][$ip] = ['count' => 0, 'time' => 0];
    }

    public function getError() {
        return $this->error;
    }
}

// Initialize and run the controller
$loginController = new LoginController($conn);
$loginController->processLogin();
?>
