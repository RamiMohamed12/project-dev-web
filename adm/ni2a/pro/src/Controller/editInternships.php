<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/internship.php';

$internship = new Internship($conn);

// Check if internship ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: internshipController.php");
    exit();
}

$id = (int) $_GET['id'];

// Fetch internship details
$internshipDetails = $internship->readInternship($id);

if (!$internshipDetails) {
    echo "Error: Internship not found.";
    exit();
}

// Handle form submission for updating the internship
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title'])) : '';
    $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
    $duration = isset($_POST['duration']) ? htmlspecialchars(trim($_POST['duration'])) : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $manager_name = isset($_POST['manager_name']) ? htmlspecialchars(trim($_POST['manager_name'])) : '';
    $manager_email = isset($_POST['manager_email']) ? htmlspecialchars(trim($_POST['manager_email'])) : '';
    $manager_phone = isset($_POST['manager_phone']) ? htmlspecialchars(trim($_POST['manager_phone'])) : '';

    // Validate required fields
    if (empty($title) || empty($location) || empty($duration) || empty($manager_name) || empty($manager_email)) {
        echo "Error: Title, location, duration, manager name, and manager email are required fields.";
        exit();
    }

    // Validate email format
    if (!filter_var($manager_email, FILTER_VALIDATE_EMAIL)) {
        echo "Error: Invalid manager email format.";
        exit();
    }

    // Update internship
    $success = $internship->updateInternship(
        $id,
        $title, 
        $location, 
        $duration, 
        $description, 
        $manager_name, 
        $manager_email, 
        $manager_phone
    );

    if ($success) {
        header("Location: internshipController.php");
        exit();
    } else {
        echo "Error: Could not update internship. " . $internship->getError();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Internship</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" type="text/css" href="../View/internship.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <a href="internshipController.php" style="text-decoration: none; font-size: 20px;">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <h2>Edit Internship</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="update">
            
            <div class="form-group">
                <label for="title">Internship Title:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($internshipDetails['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($internshipDetails['location']) ?>" required>
            </div>

            <div class="form-group">
                <label for="duration">Duration (months):</label>
                <input type="number" name="duration" min="1" max="12" 
                       value="<?= htmlspecialchars($internshipDetails['duration']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($internshipDetails['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="manager_name">Manager Name:</label>
                <input type="text" name="manager_name" 
                       value="<?= htmlspecialchars($internshipDetails['manager_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="manager_email">Manager Email:</label>
                <input type="email" name="manager_email" 
                       value="<?= htmlspecialchars($internshipDetails['manager_email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="manager_phone">Manager Phone:</label>
                <input type="tel" name="manager_phone" 
                       value="<?= htmlspecialchars($internshipDetails['manager_phone']) ?>">
            </div>

            <button type="submit">Update Internship</button>
        </form>
    </div>
</body>
</html>