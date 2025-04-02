<?php
class AuthCheck {
    public static function checkUserAuth($requiredType) {
        session_start();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            header('Location: ../Controller/loginController.php');
            exit();
        }

        // Check if user has correct type
        if ($_SESSION['user_type'] !== $requiredType) {
            header('Location: ../Controller/loginController.php');
            exit();
        }
    }
}
?>