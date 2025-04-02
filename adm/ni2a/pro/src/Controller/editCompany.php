<!-- filepath: c:\xampp\htdocs\newproject\src\Controller\editCompany.php -->
<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php';

$company = new Company($conn);

// Check if the company ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: companyController.php"); // Redirect back if no ID is provided
    exit();
}

$id = (int) $_GET['id'];

// Fetch the company details
$companyDetails = $company->read($id);

if (!$companyDetails) {
    echo "Error: Company not found.";
    exit();
}

// Handle form submission for updating the company
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';

    // Validate phone number using regex
    if (!preg_match('/^\d+$/', $phone)) {
        echo "Error: Phone number must contain only numbers.";
        exit();
    }

    if ($company->update($id, $name, $location, $description, $email, $phone)) {
        header("Location: companyController.php"); // Redirect back to the main page
        exit();
    } else {
        echo "Error: Could not update company. " . $company->error;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Company</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    
    <div class="form-container">
        <a href="companyController.php" style="text-decoration: none; font-size: 20px;">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <h2>Edit Company</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="update">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($companyDetails['name_company']) ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($companyDetails['location']) ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" required><?= htmlspecialchars($companyDetails['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($companyDetails['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($companyDetails['phone_number']) ?>" required>
            </div>
            <button type="submit">Update Company</button>
        </form>
    </div>
</body>
</html>