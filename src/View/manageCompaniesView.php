<?php
// Location: /home/demy/project-dev-web/src/View/manageCompaniesView.php
// Included by companyController.php
// Assumes variables: $companies, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Ensure path is correct relative to controller or use absolute path -->
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Using styles from style.css - inline styles removed for clarity */
        /* Add specific overrides here if necessary */
         table a i.fa-link { /* Style for URL link icon */
            margin-right: 4px;
            color: #4e73df; /* Link color */
         }
    </style>
</head>
<body>
    <div class="container">
         <!-- Correct Back Link -->
        <a href="<?= ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php' ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

         <!-- Add Company Form -->
         <div class="form-container">
            <h2><i class="fa-solid fa-building-circle-arrow-right"></i> Add New Company</h2>
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                 <input type="hidden" name="action" value="add">
                 <div class="form-group"> <label for="add_name"><i class="fa-regular fa-building"></i> Company Name:</label> <input type="text" id="add_name" name="name" required> </div>
                 <div class="form-group"> <label for="add_location"><i class="fa-solid fa-location-dot"></i> Location:</label> <input type="text" id="add_location" name="location" required> </div>
                 <div class="form-group"> <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label> <textarea id="add_description" name="description" rows="3"></textarea> </div>
                 <div class="form-group"> <label for="add_email"><i class="fa-regular fa-envelope"></i> Email:</label> <input type="email" id="add_email" name="email" required> </div>
                 <div class="form-group"> <label for="add_phone"><i class="fa-solid fa-phone"></i> Phone:</label> <input type="text" id="add_phone" name="phone" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number"> </div>
                 <!-- ***** ADDED URL FIELD ***** -->
                 <div class="form-group">
                     <label for="add_url"><i class="fa-solid fa-link"></i> Website URL (Optional):</label>
                     <input type="url" id="add_url" name="url" placeholder="https://www.example.com">
                 </div>
                 <button type="submit"><i class="fa-solid fa-plus"></i> Add Company</button>
             </form>
         </div>

        <!-- Company List Section -->
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
                        <th>Website</th> <!-- ***** ADDED URL HEADER ***** -->
                        <?php if ($loggedInUserRole === 'admin'): ?>
                            <th>Created By</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($companies) && !empty($companies)): ?>
                        <?php foreach ($companies as $company):
                            // Determine if current user can modify this company
                            $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($company['created_by_pilote_id']) && $company['created_by_pilote_id'] == $loggedInUserId));
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($company['id_company']) ?></td>
                                <td><?= htmlspecialchars($company['name_company']) ?></td>
                                <td><?= htmlspecialchars($company['location']) ?></td>
                                <td><?= htmlspecialchars($company['email']) ?></td>
                                <td><?= htmlspecialchars($company['phone_number']) ?></td>
                                <!-- ***** DISPLAY URL LINK ***** -->
                                <td>
                                    <?php if (!empty($company['company_url'])): ?>
                                        <a href="<?= htmlspecialchars($company['company_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($company['company_url']) ?>">
                                            <i class="fa-solid fa-link"></i> Visit Site
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #888;">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($loggedInUserRole === 'admin'): ?>
                                    <td><?= htmlspecialchars($company['created_by_pilote_id'] ?? 'Admin/Old') ?></td>
                                <?php endif; ?>
                                <td class="actions">
                                     <?php if ($canModify): ?>
                                        <a href="editCompany.php?id=<?= $company['id_company'] ?>" class="edit-btn" title="Edit Company"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete company <?= htmlspecialchars(addslashes($company['name_company'])) ?>? Check related internships.');">
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
                            <!-- Adjust colspan based on whether 'Created By' is shown -->
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 8 : 7 ?>">
                                No companies found<?= ($loggedInUserRole === 'pilote') ? ' created by you' : '' ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </div> <!-- /.container -->
</body>
</html>
