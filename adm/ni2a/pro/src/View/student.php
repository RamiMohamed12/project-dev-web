<?php
require_once __DIR__ . '/../Auth/AuthCheck.php';
AuthCheck::checkUserAuth('student');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <nav>
            <ul>
                <li><a href="student_dashboard.php">Dashboard</a></li>
                <li><a href="view_profile.php">My Profile</a></li>
                <li><a href="../Controller/logoutController.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Student Dashboard</h2>
        <div class="student-info">
            <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            <p>Role: Student</p>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Your Application Name</p>
    </footer>
</body>
</html>