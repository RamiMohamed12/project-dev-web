<?php
// Location: /home/demy/project-dev-web/src/View/editCompanyView.php
// Included by editCompany.php controller
// Assumes variables: $companyDetails (array), $pageTitle, $errorMessage, $successMessage
// Assumes $loggedInUserRole is available if needed for conditional logic (passed from controller)

// Prevent direct access
if (!isset($companyDetails)) {
    die("Direct access not permitted.");
}

// For companies, editing usually happens from the list, so back link is simpler
$backUrl = 'companyController.php';
$backText = 'Back to Company List';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
     <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Correct relative path from Controller -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Basic Styles for Edit View - Copy from editUserView or use common CSS */
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
        .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;
        }
         .form-group input:focus,
        .form-group textarea:focus {
             border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .form-group textarea { min-height: 80px; }
        .form-group small { font-size: 0.85em; color: #6c757d; display: block; margin-top: 5px; }
        button[type="submit"] { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; transition: background-color 0.2s ease;}
        button[type="submit"]:hover { background-color: #218838; }
        button[type="submit"] i { margin-right: 8px; }
        .back-link { display: inline-block; margin-bottom: 25px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }
    </style>
</head>
<body>
     <div class="container">
        <!-- ***** UPDATED BACK LINK (points to controller) ***** -->
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>

        <h2><?= htmlspecialchars($pageTitle) ?></h2>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
         <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>


        <form method="post" action=""> <!-- Submit to editCompany.php?id=X -->
            <input type="hidden" name="action" value="update">

            <div class="form-group">
                <label for="name"><i class="fa-solid fa-building"></i> Company Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($companyDetails['name_company'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                <input type="text" id="location" name="location" value="<?= htmlspecialchars($companyDetails['location'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($companyDetails['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($companyDetails['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($companyDetails['phone_number'] ?? '') ?>" required pattern="^\+?[0-9\s\-]+$" title="Enter a valid phone number">
            </div>
             <?php if (isset($companyDetails['created_by_pilote_id']) && AuthSession::getUserData('user_role') === 'admin'): // Show creator only to admin ?>
                 <p><small>Managed by Pilote ID: <?= htmlspecialchars($companyDetails['created_by_pilote_id']) ?></small></p>
             <?php endif; ?>

            <button type="submit"><i class="fa-solid fa-save"></i> Update Company</button>
        </form>
    </div>
</body>
</html>
