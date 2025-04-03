<?php
// Location: /home/demy/project-dev-web/src/View/manageCompaniesView.php
// Included by companyController.php
// Assumes variables: $companies, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole)) {
     // You might want to redirect or show a more specific error
    die("Direct access not permitted.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Adjust path relative to CONTROLLER or use absolute -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Use same styles as manageUsersView.php or link a common CSS file -->
     <style>
        /* Basic Styles for Manage Views - Copy from manageUsersView or use common CSS */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 1200px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 30px; margin-bottom: 20px; }
        h1:first-child, h2:first-child { margin-top: 0; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .actions a, .actions button {
            display: inline-block; padding: 5px 10px; margin: 2px 5px 2px 0;
            text-decoration: none; color: #fff; border-radius: 3px; border: none; cursor: pointer; font-size: 0.9em; vertical-align: middle;
        }
        .actions .edit-btn { background-color: #ffc107; color: #333; }
        .actions .delete-btn { background-color: #dc3545; }
        .actions .delete-btn:hover { background-color: #c82333; }
        .actions .edit-btn:hover { background-color: #e0a800; }
        .actions span { font-style: italic; color: #6c757d; font-size: 0.9em; }
        .form-container { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #dee2e6; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;
        }
        button[type="submit"] { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button[type="submit"]:hover { background-color: #0056b3; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link i { margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
         <!-- ***** CORRECTED BACK LINK ***** -->
        <a href="<?= ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php' ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

         <!-- Add Company Form (Visible to Admin and Pilote) -->
         <div class="form-container">
            <h2><i class="fa-solid fa-building-circle-arrow-right"></i> Add New Company</h2>
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                 <input type="hidden" name="action" value="add">
                 <div class="form-group">
                     <label for="add_name">Company Name:</label>
                     <input type="text" id="add_name" name="name" required>
                 </div>
                 <div class="form-group">
                     <label for="add_location">Location:</label>
                     <input type="text" id="add_location" name="location" required>
                 </div>
                 <div class="form-group">
                     <label for="add_description">Description:</label>
                     <textarea id="add_description" name="description" rows="3"></textarea>
                 </div>
                 <div class="form-group">
                     <label for="add_email">Email:</label>
                     <input type="email" id="add_email" name="email" required>
                 </div>
                 <div class="form-group">
                     <label for="add_phone">Phone:</label>
                     <input type="text" id="add_phone" name="phone" required pattern="^\+?[0-9\s\-]+$" title="Enter a valid phone number">
                 </div>
                 <button type="submit"><i class="fa-solid fa-plus"></i> Add Company</button>
             </form>
         </div>

        <!-- Company List -->
        <section id="companies">
            <h2><i class="fa-solid fa-list-ul"></i> Company List</h2>
             <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <?php if ($loggedInUserRole === 'admin'): ?>
                            <th>Created By (Pilote ID)</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($companies)): ?>
                        <?php foreach ($companies as $company):
                            $canModify = false;
                            if ($loggedInUserRole === 'admin') {
                                $canModify = true;
                            } elseif ($loggedInUserRole === 'pilote' && isset($company['created_by_pilote_id']) && $company['created_by_pilote_id'] == $loggedInUserId) {
                                $canModify = true;
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($company['id_company']) ?></td>
                                <td><?= htmlspecialchars($company['name_company']) ?></td>
                                <td><?= htmlspecialchars($company['location']) ?></td>
                                <td><?= htmlspecialchars($company['email']) ?></td>
                                <td><?= htmlspecialchars($company['phone_number']) ?></td>
                                <?php if ($loggedInUserRole === 'admin'): ?>
                                    <td><?= htmlspecialchars($company['created_by_pilote_id'] ?? 'Admin/Old') ?></td>
                                <?php endif; ?>
                                <td class="actions">
                                     <?php if ($canModify): ?>
                                        <a href="editCompany.php?id=<?= $company['id_company'] ?>" class="edit-btn" title="Edit Company"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete company <?= htmlspecialchars(addslashes($company['name_company'])) ?>? This might affect related internships.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $company['id_company'] ?>">
                                            <button type="submit" class="delete-btn" title="Delete Company"><i class="fa-solid fa-trash"></i> Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span>(View Only)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 7 : 6 ?>">No companies found<?= ($loggedInUserRole === 'pilote') ? ' created by you' : '' ?>.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </div>
</body>
</html>
