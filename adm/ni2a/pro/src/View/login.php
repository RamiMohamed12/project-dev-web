<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <?php
    // Display error if available
    if (isset($loginController) && method_exists($loginController, 'getError')) {
        $error = $loginController->getError();
        if (!empty($error)) {
            echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
        }
    }
    ?>
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label>Email:</label>
        <input type="email" name="email" required>
        <br>
        <label>Password:</label>
        <input type="password" name="password" required>
        <br>
        <label>User Type:</label>
        <select name="user_type" required>
            <option value="student">Student</option>
            <option value="pilote">Pilote</option>
            <option value="admin">Admin</option>
        </select>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
