<?php
// Location: /home/demy/project-dev-web/src/View/manageInternshipsView.php
// Included by internshipController.php (action=list for Admin/Pilote)
// Assumes variables:
// $internships (array of offers), $companiesList (array for dropdown)
// $loggedInUserRole ('admin' or 'pilote'), $loggedInUserId (int)
// $pageTitle, $errorMessage, $successMessage

// Prevent direct access / access by students
if (!isset($loggedInUserRole) || !in_array($loggedInUserRole, ['admin', 'pilote']) || !isset($loggedInUserId)) {
    die("Access Denied or required data missing.");
}

$defaultCompanyPic = 'images/default_company.png'; // ** Relative path from THIS file's location in View folder **

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Link to main CSS in View folder -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Optional: Add minor style adjustments specific to this page */
        .company-logo-list { width: 30px; height: 30px; object-fit: contain; vertical-align: middle; margin-right: 8px; background-color: #eee; border-radius: 3px; }
        td, th { vertical-align: middle !important; }
        .description-preview { max-height: 60px; overflow: hidden; text-overflow: ellipsis; white-space: normal; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; font-size: 0.9em; color: #555; }
        .remuneration-col { text-align: right; white-space: nowrap;}
        .date-col { white-space: nowrap; text-align: center;}
         /* Ensure other styles from style.css apply correctly */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 1300px; margin: auto; background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); } /* Wider container */
        h1, h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 30px; margin-bottom: 20px; }
        h1:first-child, h2:first-child { margin-top: 0; }
        section { margin-bottom: 40px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        td { word-break: break-word; }
        .actions a, .actions button { display: inline-block; padding: 5px 10px; margin: 2px 5px 2px 0; text-decoration: none; color: #fff; border-radius: 3px; border: none; cursor: pointer; font-size: 0.9em; vertical-align: middle; }
        .actions .edit-btn { background-color: #ffc107; color: #333; }
        .actions .delete-btn { background-color: #dc3545; }
        .actions .delete-btn:hover { background-color: #c82333; }
        .actions .edit-btn:hover { background-color: #e0a800; }
        .actions span.no-permission { font-style: italic; color: #6c757d; font-size: 0.9em; } /* Adjusted class name */
        .form-container { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #dee2e6; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="number"], .form-group input[type="date"], .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        button[type="submit"] { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button[type="submit"]:hover { background-color: #0056b3; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }

    </style>
</head>
<body class="<?= $loggedInUserRole === 'admin' ? 'admin-theme' : 'pilote-theme' ?>">

    <div class="container">
         <!-- Back Link -->
        <a href="<?= ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php' ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

         <!-- Add Internship Form -->
         <div class="form-container">
            <h2><i class="fa-solid fa-plus-circle"></i> Add New Internship Offer</h2>
            <form method="post" action="internshipController.php">
                 <input type="hidden" name="action" value="add">

                 <div class="form-group">
                     <label for="add_id_company"><i class="fa-regular fa-building"></i> Company:</label>
                     <select id="add_id_company" name="id_company" required>
                         <option value="" disabled selected>-- Select Company --</option>
                         <?php if (is_array($companiesList) && !empty($companiesList)): ?>
                             <?php foreach ($companiesList as $company): ?>
                                 <option value="<?= htmlspecialchars($company['id_company']) ?>">
                                     <?= htmlspecialchars($company['name_company']) ?> (ID: <?= htmlspecialchars($company['id_company']) ?>)
                                 </option>
                             <?php endforeach; ?>
                         <?php else: ?>
                            <option value="" disabled>No companies available</option>
                            <?php if($loggedInUserRole === 'pilote'): ?>
                                <option value="" disabled>(You must create companies first)</option>
                            <?php endif; ?>
                         <?php endif; ?>
                     </select>
                     <?php if ($loggedInUserRole === 'pilote' && empty($companiesList)): ?>
                        <small>You can only select companies you have created. <a href="companyController.php">Manage Companies</a></small>
                     <?php elseif(empty($companiesList) && $loggedInUserRole === 'admin'): ?>
                        <small>No companies found. <a href="companyController.php">Add Companies</a></small>
                     <?php endif; ?>
                 </div>

                 <div class="form-group"> <label for="add_title"><i class="fa-solid fa-heading"></i> Offer Title:</label> <input type="text" id="add_title" name="title" required> </div>
                 <div class="form-group"> <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label> <textarea id="add_description" name="description" rows="4" required></textarea> </div>
                 <div class="form-group"> <label for="add_remuneration"><i class="fa-solid fa-euro-sign"></i> Remuneration (€/month, optional):</label> <input type="number" step="0.01" min="0" id="add_remuneration" name="remuneration" placeholder="e.g., 550.50"> </div>
                 <div class="form-group"> <label for="add_offre_date"><i class="fa-regular fa-calendar-check"></i> Offer Available Date:</label> <input type="date" id="add_offre_date" name="offre_date" required value="<?= date('Y-m-d'); // Default to today ?>"> </div>

                 <button type="submit"><i class="fa-solid fa-plus"></i> Add Internship Offer</button>
             </form>
         </div>

        <!-- Internship List Section -->
        <section id="internships">
            <h2><i class="fa-solid fa-list-ul"></i> Current Internship Offers</h2>
             <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Description</th>
                        <th class="remuneration-col">Remun. (€)</th>
                        <th class="date-col">Offer Date</th>
                        <?php if ($loggedInUserRole === 'admin'): ?>
                            <th>Offer Creator</th>
                            <th>Company Manager</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($internships) && !empty($internships)): ?>
                        <?php foreach ($internships as $offer): ?>
                            <?php
                                // Determine if current user can modify this offer
                                $canModify = false;
                                if ($loggedInUserRole === 'admin') {
                                    $canModify = true;
                                } elseif ($loggedInUserRole === 'pilote') {
                                    // Allow if pilote created the COMPANY or the INTERNSHIP
                                    if ((isset($offer['company_creator_id']) && $offer['company_creator_id'] == $loggedInUserId) ||
                                        (isset($offer['created_by_pilote_id']) && $offer['created_by_pilote_id'] == $loggedInUserId)) {
                                        $canModify = true;
                                    }
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($offer['id_internship']) ?></td>
                                <td><?= htmlspecialchars($offer['title']) ?></td>
                                <td>
                                    <?php /* Display company logo placeholder - assuming $defaultCompanyPic is set */ ?>
                                    <img src="<?= $defaultCompanyPic ?>" alt="Logo" class="company-logo-list" title="<?= !empty($offer['company_picture_mime']) ? 'Logo available' : 'No logo' ?>">
                                    <?= htmlspecialchars($offer['name_company'] ?? 'N/A') ?>
                                    <br><small style="color: #6c757d;"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($offer['company_location'] ?? '') /* Assuming company_location exists */ ?></small>
                                </td>
                                <td><div class="description-preview" title="<?= htmlspecialchars($offer['description']) ?>"><?= nl2br(htmlspecialchars($offer['description'])) ?></div></td>
                                <td class="remuneration-col"><?= $offer['remuneration'] !== null ? htmlspecialchars(number_format($offer['remuneration'], 2)) : '-' ?></td>
                                <td class="date-col"><?= htmlspecialchars($offer['offre_date']) ?></td>

                                <?php if ($loggedInUserRole === 'admin'): ?>
                                    <?php // Display creator info for Admin view
                                        $offerCreatorText = 'Admin';
                                        if (isset($offer['created_by_pilote_id']) && $offer['created_by_pilote_id']) {
                                            $offerCreatorText = 'Pilote: ' . htmlspecialchars($offer['created_by_pilote_id']);
                                        }
                                        $companyManagerText = 'Admin/Old';
                                         if (isset($offer['company_creator_id']) && $offer['company_creator_id'] !== null) {
                                             $companyManagerText = 'Pilote: ' . htmlspecialchars($offer['company_creator_id']);
                                         } elseif (isset($offer['company_creator_id']) && $offer['company_creator_id'] === null) {
                                              $companyManagerText = 'Admin';
                                         }
                                    ?>
                                    <td><?= $offerCreatorText ?></td>
                                    <td><?= $companyManagerText ?></td>
                                <?php endif; ?>

                                <td class="actions">
                                     <?php if ($canModify): ?>
                                        <a href="internshipController.php?action=edit&id=<?= $offer['id_internship'] ?>" class="edit-btn">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>

                                        <form method="post" action="internshipController.php" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $offer['id_internship'] ?>">
                                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this internship offer?');">
                                                <i class="fa-solid fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                     <?php else: ?>
                                        <span class="no-permission"><i class="fa-solid fa-lock"></i> No permission</span>
                                     <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 9 : 7 ?>">
                                No internship offers found<?= ($loggedInUserRole === 'pilote') ? ' matching your criteria' : '' ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </div> <!-- /.container -->


     <script src="../View/script.js"></script>
</body>
</html>