<?php
// Location: /home/demy/project-dev-web/src/View/editUserView.php
// Included by editUser.php controller
// Assumes variables:
// $userDetails (array),
// $targetUserType (string),
// $pageTitle, $errorMessage, $successMessage
// $isSelfEdit (bool - TRUE if the logged-in user is editing their own profile)
// $loggedInUserRole (string - role of the person VIEWING the page)

// Prevent direct access
if (!isset($userDetails) || !isset($targetUserType) || !isset($isSelfEdit) || !isset($loggedInUserRole)) {
    die("Direct access not permitted or required variables missing.");
}

// Determine the correct "Back" URL and Text based on context
$backUrl = 'userController.php'; // Default: back to the user list
$backText = 'Back to User List';

if ($isSelfEdit) {
    // If editing own profile, link back to the appropriate dashboard
    if ($loggedInUserRole === 'admin') {
        $backUrl = '../View/admin.php'; // Go up from Controller dir, down to View
    } elseif ($loggedInUserRole === 'pilote') {
        $backUrl = '../View/pilote.php'; // Go up from Controller dir, down to View
    } elseif ($loggedInUserRole === 'student') {
         $backUrl = '../View/student.php'; // Go up from Controller dir, down to View
    }
    // Override text only if a valid dashboard was found
    if ($backUrl !== 'userController.php') { // Check if URL was changed
       $backText = 'Back to My Dashboard';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Correct relative path from Controller -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Basic Styles for Edit View */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 700px; margin: auto; background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 0; margin-bottom: 25px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555;}
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
             border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .form-group textarea { min-height: 80px; }
        .form-group small { font-size: 0.85em; color: #6c757d; display: block; margin-top: 5px; }
        button[type="submit"] { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; transition: background-color 0.2s ease; }
        button[type="submit"]:hover { background-color: #218838; }
        button[type="submit"] i { margin-right: 8px; }
        .back-link { display: inline-block; margin-bottom: 25px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- ***** UPDATED CONDITIONAL BACK LINK ***** -->
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>

        <h2><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
         <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>


        <form method="post" action=""> <!-- Submit to the same controller (editUser.php?id=X&type=Y) -->
            <input type="hidden" name="action" value="update">

            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-key"></i> New Password:</label>
                <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                 <small>If you set a new password, the user will need to use it to log in.</small>
            </div>

            <?php // Fields for Pilotes and Students ?>
            <?php if ($targetUserType === 'pilote' || $targetUserType === 'student'): ?>
                <div class="form-group">
                    <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                    <input type="text" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>">
                </div>
            <?php endif; ?>

             <?php // Fields specific to Students ?>
            <?php if ($targetUserType === 'student'): ?>
                 <div class="form-group">
                    <label for="dob"><i class="fa-solid fa-calendar-days"></i> Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="year"><i class="fa-solid fa-graduation-cap"></i> Year:</label>
                    <select id="year" name="year" required>
                        <option value="" disabled <?= empty($userDetails['year']) ? 'selected' : ''?>>-- Select Year --</option>
                        <?php
                        $years = ['1st', '2nd', '3rd', '4th', '5th'];
                        foreach ($years as $y) {
                            $selected = (($userDetails['year'] ?? '') === $y) ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y Year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label>
                    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea>
                </div>
                 <?php if (isset($userDetails['created_by_pilote_id']) && $loggedInUserRole === 'admin'): // Show creator only to admin ?>
                     <p><small>Managed by Pilote ID: <?= htmlspecialchars($userDetails['created_by_pilote_id']) ?></small></p>
                 <?php endif; ?>
            <?php endif; ?>


            <button type="submit"><i class="fa-solid fa-save"></i> Update <?= ucfirst($targetUserType) ?></button>
        </form>
    </div>
</body>
</html>
