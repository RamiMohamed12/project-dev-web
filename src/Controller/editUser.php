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

    $updateData = [
        'name' => $_POST['name'] ?? null,
        'email' => $_POST['email'] ?? null,
        'password' => $_POST['password'] ?? null, // This line was already here, ready to receive the password
    ];

    // Important: Ensure your update methods in Model/user.php handle the password
    // - They should only update the password if $updateData['password'] is not empty.
    // - They MUST hash the password before storing it (e.g., using password_hash()).

    if ($type !== 'admin') {
        $updateData['location'] = $_POST['location'] ?? null;
        $updateData['phone_number'] = $_POST['phone'] ?? null;
        // Note: date_of_birth was missing here in the original POST handling for pilote/admin
        // Add it if pilotes need DOB too, otherwise keep as is.
        // $updateData['date_of_birth'] = $_POST['dob'] ?? null;
    }
    if ($type === 'student') {
        $updateData['year'] = $_POST['year'] ?? null;
        $updateData['description'] = $_POST['description'] ?? null;
        $updateData['date_of_birth'] = $_POST['dob'] ?? null; // DOB is student-specific in the form
    }

    // Validate required fields
    if (empty($updateData['name']) || empty($updateData['email'])) {
        echo "Error: Name and email are required.";
        exit();
    }
    if ($type === 'student' && empty($updateData['year'])) {
        echo "Error: Year is required for students.";
        exit();
    }
    // Note: You might want validation for the student DOB if it's required
    // if ($type === 'student' && empty($updateData['date_of_birth'])) {
    //     echo "Error: Date of Birth is required for students.";
    //     exit();
    // }


    // Update user based on type
    $result = false;
    switch($type) {
        case 'student':
            // Make sure updateStudent accepts and handles the password parameter
            $result = $user->updateStudent(
                $id,
                $updateData['name'],
                $updateData['email'],
                $updateData['location'],
                $updateData['phone_number'],
                $updateData['date_of_birth'],
                $updateData['year'],
                $updateData['description'],
                $updateData['password'] // Pass the new password (or null/empty)
            );
            break;
        case 'pilote':
             // Make sure updatePilote accepts and handles the password parameter
            $result = $user->updatePilote(
                $id,
                $updateData['name'],
                $updateData['email'],
                $updateData['location'],
                $updateData['phone_number'],
                $updateData['password'] // Pass the new password (or null/empty)

            );
            break;
        case 'admin':
             // Make sure updateAdmin accepts and handles the password parameter
            $result = $user->updateAdmin(
                $id,
                $updateData['name'],
                $updateData['email'],
                $updateData['password'] // Pass the new password (or null/empty)
            );
            break;
    }

    if ($result) {
        header("Location: userController.php?update=success");
        exit();
    } else {
        // Provide more specific error if possible from the model
        echo "Error updating user.";
        exit();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <a href="userController.php" style="text-decoration: none; font-size: 20px;">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <h2>Edit <?= ucfirst($type) ?></h2>
        <form method="post" action="">
            <input type="hidden" name="action" value="update">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($userDetails['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($userDetails['email']) ?>" required>
            </div>

            <!-- Added Password Field -->
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current):</label>
                <input type="password" id="password" name="password">
            </div>
            <!-- End Added Password Field -->


            <?php if ($type !== 'admin'): ?>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>"> <!-- Added null coalescing for safety -->
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>"> <!-- Added null coalescing for safety -->
            </div>
            <?php endif; ?>

            <?php if ($type === 'student'): ?>
             <!-- Moved DOB field inside student-specific block as it wasn't shown for pilotes -->
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <!-- Make sure dob is required if your logic expects it -->
                <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required> <!-- Added null coalescing for safety -->
            </div>
            <div class="form-group">
                <label for="year">Year:</label>
                <select id="year" name="year" required>
                    <?php
                    $years = ['1st', '2nd', '3rd', '4th', '5th'];
                    foreach ($years as $y) {
                        // Use null coalescing for safety in case year isn't set
                        $selected = (($userDetails['year'] ?? '') === $y) ? 'selected' : '';
                        echo "<option value=\"$y\" $selected>$y Year</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea> <!-- Added null coalescing for safety -->
            </div>
            <?php endif; ?>

            <button type="submit">Update User</button>
        </form>
    </div>
</body>
</html>
