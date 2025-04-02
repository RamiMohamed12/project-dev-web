<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php';

$company = new Company($conn);

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
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

        if ($company->create($name, $location, $description, $email, $phone)) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: Could not add company. " . $company->error;
        }
    } elseif ($action == 'delete') {
        $id = (int) $_POST['id'];

        if ($company->delete($id)) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: Could not delete company. " . $company->error;
        }
    }
}

// Fetch all companies to display in the table
$companies = $company->readAll();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Companies</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h2>Add Company</h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" placeholder="Name" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" name="location" placeholder="Location" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" placeholder="Description" required></textarea>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" name="phone" placeholder="Phone" required>
            </div>
            <button type="submit">Add Company</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Company List</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($companies)): ?>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?= htmlspecialchars($company['id_company']) ?></td>
                            <td><?= htmlspecialchars($company['name_company']) ?></td>
                            <td><?= htmlspecialchars($company['location']) ?></td>
                            <td><?= htmlspecialchars($company['description']) ?></td>
                            <td><?= htmlspecialchars($company['email']) ?></td>
                            <td><?= htmlspecialchars($company['phone_number']) ?></td>
                            <td>
                                <form method="post" action="" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $company['id_company'] ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this company?');">Delete</button>
                                </form>
                                <a href="editCompany.php?id=<?= $company['id_company'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No companies found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>