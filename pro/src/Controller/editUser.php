<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php';

$user = new User($conn);

// Check if user ID and type are provided
if (!isset($_GET['id']) || !isset($_GET['type']) || empty($_GET['id']) || empty($_GET['type'])) {
    header("Location: userController.php");
    exit();
}

$id = (int) $_GET['id'];
$type = $_GET['type'];

// Fetch user details based on type
$userDetails = null;
switch($type) {
    case 'student':
        $userDetails = $user->readStudent($id);
        break;
    case 'pilote':
        $userDetails = $user->readPilote($id);
        break;
    case 'admin':
        $userDetails = $user->readAdmin($id);
        break;
    default:
        header("Location: userController.php");
        exit();
}

if (!$userDetails) {
    echo "Error: User not found.";
    exit();
}

// Handle form submission for updating the user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
    // Add update logic here once you implement update methods in User class
    echo "Update functionality will be implemented soon.";
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            width: 100%;
        }
        
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        
        a {
            text-decoration: none;
            color: #333;
        }
        
        a:hover {
            color: #4CAF50;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <a href="userController.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <h2>Edit <?= ucfirst($type) ?></h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="update">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($userDetails['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($userDetails['email']) ?>" required>
            </div>

            <?php if ($type !== 'admin'): ?>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($userDetails['location']) ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($userDetails['phone_number']) ?>">
            </div>
            <?php endif; ?>

            <?php if ($type === 'student'): ?>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth']) ?>" required>
            </div>
            <div class="form-group">
                <label for="year">Year:</label>
                <select name="year" required>
                    <?php 
                    $years = ['1st', '2nd', '3rd', '4th', '5th'];
                    foreach ($years as $y) {
                        $selected = ($userDetails['year'] === $y) ? 'selected' : '';
                        echo "<option value=\"$y\" $selected>$y Year</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description"><?= htmlspecialchars($userDetails['description']) ?></textarea>
            </div>
            <?php endif; ?>

            <button type="submit">Update User</button>
        </form>
    </div>
</body>
</html>