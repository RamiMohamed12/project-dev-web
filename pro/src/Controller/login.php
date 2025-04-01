<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';
    
    if (empty($email) || empty($password) || empty($userType)) {
        $error = "All fields are required";
    } else {
        $user = new User($conn);
        $authenticated = false;
        $userData = null;
        
        switch ($userType) {
            case 'student':
                $authenticated = $user->authenticateStudent($email, $password, $userData);
                break;
            case 'pilote':
                $authenticated = $user->authenticatePilote($email, $password, $userData);
                break;
            case 'admin':
                $authenticated = $user->authenticateAdmin($email, $password, $userData);
                break;
        }
        
        if ($authenticated && $userData) {
            // Set session variables
            $_SESSION['user_id'] = $userData['id_' . $userType];
            $_SESSION['user_name'] = $userData['name'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_type'] = $userType;
            
            // Redirect based on user type
            switch ($userType) {
                case 'student':
                    header("Location: ../View/student.php");
                    break;
                case 'pilote':
                    header("Location: ../View/pilote.php");
                    break;
                case 'admin':
                    header("Location: dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../View/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Login to Your Account</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="user_type">I am a:</label>
                    <select name="user_type" id="user_type" required>
                        <option value="student">Student</option>
                        <option value="pilote">Pilote</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>