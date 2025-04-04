<?php
// Location: /home/demy/project-dev-web/src/View/login.php

// Start session VERY FIRST
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Redirect if already logged in ---
// Include AFTER starting session
require_once __DIR__ . '/../Auth/AuthSession.php';
if (AuthSession::isUserLoggedIn()) {
    $role = AuthSession::getUserData('user_role');
    $redirectUrl = null;
     switch ($role) {
        case 'admin': $redirectUrl = 'admin.php'; break;
        case 'pilote': $redirectUrl = 'pilote.php'; break;
        case 'student': $redirectUrl = 'student.php'; break;
        default:
             AuthSession::destroySession();
             header("Location: login.php?error=" . urlencode("Invalid session. Please login again."));
             exit();
    }
    if ($redirectUrl) {
        header("Location: " . $redirectUrl);
        exit();
    }
}

// --- Message Handling ---
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars(urldecode($_GET['error']));
}
$success_message = '';
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = "You have been logged out successfully.";
}

// --- Form Persistence ---
$prev_email = isset($_SESSION['login_attempt_email']) ? htmlspecialchars($_SESSION['login_attempt_email']) : '';
$prev_type = isset($_SESSION['login_attempt_type']) ? htmlspecialchars($_SESSION['login_attempt_type']) : '';
unset($_SESSION['login_attempt_email'], $_SESSION['login_attempt_type']); // Clear after use

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Project Dev Web</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Basic styles */
        body { font-family: sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background-color: #fff; padding: 30px 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; box-sizing: border-box; }
        .form-container h2 { text-align: center; color: #333; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: bold; }
        .form-group label i { margin-right: 8px; }
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        .form-group input:focus,
        .form-group select:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25); }
        .submit-btn { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em; width: 100%; transition: background-color 0.3s ease; }
        .submit-btn:hover { background-color: #0056b3; }
        .message { padding: 12px 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; font-size: 0.95em; text-align: center; }
        .error-message { color: #D8000C; background-color: #FFD2D2; border-color: #D8000C; }
        .success-message { color: #4F8A10; background-color: #DFF2BF; border-color: #4F8A10; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>User Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?= $error_message ?></div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <form method="post" action="../Controller/loginController.php">
            <div class="form-group">
                <label for="user_type"><i class="fa-solid fa-users"></i> Account Type:</label>
                <select id="user_type" name="user_type" required>
                    <option value="" disabled <?= empty($prev_type) ? 'selected' : '' ?>>-- Select Account Type --</option>
                    <option value="admin" <?= ($prev_type === 'admin') ? 'selected' : '' ?>>Administrator</option>
                    <option value="pilote" <?= ($prev_type === 'pilote') ? 'selected' : '' ?>>Pilote</option>
                    <option value="student" <?= ($prev_type === 'student') ? 'selected' : '' ?>>Student</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email Address:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= $prev_email ?>" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>
    </div>
</body>
</html>
