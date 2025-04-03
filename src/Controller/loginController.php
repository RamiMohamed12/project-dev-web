<?php

// Ensure errors are displayed for debugging (remove or adjust for production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/user.php'; // Corrected path assuming Model directory is one level up

// Check if the $conn variable exists after including config.php
if (!isset($conn) || !$conn) {
    die("Database connection failed. Please check your configuration.");
}

try {
    $user = new User($conn);
} catch (Exception $e) {
    die("Error initializing User class: " . $e->getMessage());
}

$error_message = ''; // Variable to hold error messages for display

// Check if user ID and type are provided
if (!isset($_GET['id']) || !isset($_GET['type']) || !ctype_digit($_GET['id']) || empty($_GET['type'])) {
    header("Location: userController.php?error=invalid_params"); // Redirect to a listing page, maybe userController.php handles listing?
    exit();
}

$id = (int) $_GET['id'];
$type = $_GET['type'];
$allowed_types = ['student', 'pilote', 'admin'];

if (!in_array($type, $allowed_types)) {
    header("Location: userController.php?error=invalid_type");
    exit();
}

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
    // Default case is already handled by allowed_types check
}

// Check if user was found
if (!$userDetails) {
    // If read failed due to DB error, getError might have info
    $db_error = $user->getError();
    $error_message = $db_error ?: "Error: User not found or could not be retrieved.";
    // Display the error within the HTML structure or die
    // For simplicity here, we'll set the error message and let the HTML display it if the form part is skipped.
    // Or, more drastically:
    // die("Error: User not found or could not be retrieved." . ($db_error ? " Database error: ".$db_error : ""));
}

// Handle form submission for updating the user
if ($userDetails && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {

    // Common fields
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null; // Optional new password

    // Type-specific fields
    $location = ($type !== 'admin') ? ($_POST['location'] ?? null) : null;
    $phone = ($type !== 'admin') ? ($_POST['phone'] ?? null) : null;
    $dob = ($type === 'student') ? ($_POST['dob'] ?? null) : null;
    $year = ($type === 'student') ? ($_POST['year'] ?? null) : null;
    $description = ($type === 'student') ? ($_POST['description'] ?? null) : null;

    // Basic Validation
    $validation_passed = true;
    if (empty($name)) {
        $error_message = "Error: Name is required.";
        $validation_passed = false;
    } elseif (empty($email)) {
        $error_message = "Error: Email is required.";
        $validation_passed = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $error_message = "Error: Invalid email format.";
         $validation_passed = false;
    } elseif ($type === 'student' && empty($dob)) {
        $error_message = "Error: Date of Birth is required for students.";
        $validation_passed = false;
    } elseif ($type === 'student' && (empty($year) || !in_array($year, [1, 2, 3, 4, 5]))) {
        $error_message = "Error: Valid Year (1-5) is required for students.";
        $validation_passed = false;
    } elseif (!empty($password) && strlen($password) < 6) { // Example minimum password length
         $error_message = "Error: New password must be at least 6 characters long.";
         $validation_passed = false;
    }

    if ($validation_passed) {
        $result = false;
        switch($type) {
            case 'student':
                // Note: Pass password only if it's not empty
                $result = $user->updateStudent(
                    $id,
                    $name,
                    $email,
                    $location,
                    $phone,
                    $dob,
                    (int)$year, // Ensure year is integer
                    $description,
                    !empty($password) ? $password : null // Pass password if provided
                );
                break;
            case 'pilote':
                $result = $user->updatePilote(
                    $id,
                    $name,
                    $email,
                    $location,
                    $phone,
                    !empty($password) ? $password : null // Pass password if provided
                );
                break;
            case 'admin':
                $result = $user->updateAdmin(
                    $id,
                    $name,
                    $email,
                    !empty($password) ? $password : null // Pass password if provided
                );
                break;
        }

        if ($result) {
            // Refresh user details after successful update
             switch($type) {
                case 'student': $userDetails = $user->readStudent($id); break;
                case 'pilote': $userDetails = $user->readPilote($id); break;
                case 'admin': $userDetails = $user->readAdmin($id); break;
             }
            // Optionally redirect or set a success message
             header("Location: userController.php?update=success&type=".$type); // Redirect back to listing
             exit();
           // $success_message = ucfirst($type) . " updated successfully!"; // Use if not redirecting
        } else {
            $error_message = "Error updating " . $type . ": " . $user->getError();
        }
    }
    // If validation failed, $error_message is already set.
    // If update failed, $error_message is set from $user->getError().
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit <?= $userDetails ? htmlspecialchars(ucfirst($type)) : 'User' ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Adjust path if needed -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Basic styling for error messages */
        .error-message {
            color: #D8000C;
            background-color: #FFD2D2;
            border: 1px solid #D8000C;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success-message {
             color: #4F8A10;
             background-color: #DFF2BF;
             border: 1px solid #4F8A10;
             padding: 10px;
             margin-bottom: 15px;
             border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <a href="userController.php" style="text-decoration: none; font-size: 1em; display: inline-block; margin-bottom: 15px;">
            <i class="fa-solid fa-arrow-left"></i> Back to User List
        </a>

        <h2>Edit <?= $userDetails ? htmlspecialchars(ucfirst($type)) : 'User' ?></h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['update']) && $_GET['update'] == 'success' && empty($error_message)): ?>
             <!-- This message might not be seen if redirect happens immediately -->
            <div class="success-message"><?= htmlspecialchars(ucfirst($type)) ?> updated successfully!</div>
        <?php endif; ?>


        <?php if ($userDetails): ?>
            <form method="post" action="editUserController.php?id=<?= $id ?>&type=<?= htmlspecialchars($type) ?>">
                <input type="hidden" name="action" value="update">

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" required>
                </div>

                <?php if ($type !== 'admin'): ?>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>">
                </div>
                <?php endif; ?>

                <?php if ($type === 'student'): ?>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="year">Year:</label>
                    <select id="year" name="year" required>
                        <option value="">-- Select Year --</option>
                        <?php
                        $current_year = $userDetails['year'] ?? null; // Get the current year value
                        // Ensure we compare integers if the DB stores integers
                        $current_year_int = is_numeric($current_year) ? (int)$current_year : null;

                        for ($y = 1; $y <= 5; $y++) {
                            $selected = ($current_year_int === $y) ? 'selected' : '';
                            $suffix = match($y) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                            echo "<option value=\"$y\" $selected>$y{$suffix} Year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>

                 <div class="form-group">
                    <label for="password">New Password (optional):</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                     <small>Minimum 6 characters if changing.</small>
                </div>
                 <div class="form-group">
                    <label for="password_confirm">Confirm New Password:</label>
                    <input type="password" id="password_confirm" name="password_confirm">
                </div>

                <button type="submit" class="submit-btn">Update <?= htmlspecialchars(ucfirst($type)) ?></button>
            </form>
        <?php elseif (empty($error_message)) : ?>
            <p>User data could not be loaded.</p>
        <?php endif; ?>
    </div>
</body>
</html>
