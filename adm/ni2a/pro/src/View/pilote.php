<?php
require_once __DIR__ . '/../Auth/AuthCheck.php';
AuthCheck::checkUserAuth('pilote');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilote Dashboard</title>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <nav>
            <ul>
                <li><a href="pilote_dashboard.php">Dashboard</a></li>
                <li><a href="manage_students.php">Manage Students</a></li>
                <li><a href="../Controller/logoutController.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Pilote Dashboard</h2>
        <div class="pilote-info">
            <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            <p>Role: Pilote</p>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Your Application Name</p>
    </footer>
</body>
</html>