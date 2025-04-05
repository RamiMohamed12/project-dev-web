<?php
// Location: /home/demy/project-dev-web/src/View/editInternshipView.php
// Included by internshipController.php (edit/update actions)
// Assumes variables:
// $internshipDetails (array of the specific internship being edited)
// $companiesList (array of companies for the dropdown - filtered for pilote)
// $pageTitle (string)
// $errorMessage (string)
// $successMessage (string) - Although typically not shown directly on edit page
// $loggedInUserRole (string 'admin' or 'pilote')
// $loggedInUserId (int) - Needed for permission check

// Prevent direct access & ensure required data exists
if (!isset($internshipDetails) || !isset($companiesList) || !isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted or required data missing.");
}

// Back link - typically goes back to the list view controller
$backUrl = 'internshipController.php?action=list';
$backText = 'Back to Internship List';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Correct path relative to controller -->
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Using styles from style.css - Add specific overrides here if needed */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 800px; margin: auto; background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 0; margin-bottom: 25px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555;}
        .form-group input[type="text"], .form-group input[type="number"], .form-group input[type="date"], .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25); }
        .form-group textarea { min-height: 120px; }
        .form-group small { font-size: 0.85em; color: #6c757d; display: block; margin-top: 5px; }
        button[type="submit"] { background-color: #28a745; /* Green for update */ color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; }
        button[type="submit"]:hover { background-color: #218838; }
        button[type="submit"] i { margin-right: 8px; }
        .back-link { display: inline-block; margin-bottom: 25px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }
        /* Style for delete button */
        .delete-action a { background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; text-decoration: none; display: inline-block; margin-left: 10px; }
        .delete-action a:hover { background-color: #c82333; }
        .delete-action i { margin-right: 8px; }
    </style>
</head>
<body class="<?= $loggedInUserRole === 'admin' ? 'admin-theme' : 'pilote-theme' ?>"> {/* Apply theme */}

    <div class="container">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>
        <h2><?= htmlspecialchars($pageTitle) ?></h2>

        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php /* Success messages usually shown on redirect */ ?>

        <form method="post" action="internshipController.php"> {/* Always submit to controller */}
            <input type="hidden" name="action" value="update">
            {/* Pass the ID of the internship being edited */}
            <input type="hidden" name="id_internship" value="<?= htmlspecialchars($internshipDetails['id_internship']) ?>">

             <div class="form-group">
                 <label for="edit_id_company"><i class="fa-regular fa-building"></i> Company:</label>
                 <select id="edit_id_company" name="id_company" required>
                     <option value="" disabled>-- Select Company --</option>
                     <?php if (is_array($companiesList) && !empty($companiesList)): ?>
                         <?php foreach ($companiesList as $company):
                            // Check if this company is the currently selected one for the internship
                            $selected = ($company['id_company'] == $internshipDetails['id_company']) ? 'selected' : '';
                         ?>
                             <option value="<?= htmlspecialchars($company['id_company']) ?>" <?= $selected ?>>
                                 <?= htmlspecialchars($company['name_company']) ?> (ID: <?= htmlspecialchars($company['id_company']) ?>)
                             </option>
                         <?php endforeach; ?>
                     <?php else: ?>
                        <option value="" disabled>No companies available to assign</option>
                     <?php endif; ?>
                 </select>
                 <?php if ($loggedInUserRole === 'pilote'): ?>
                    <small>You can only select companies you manage.</small>
                 <?php endif; ?>
             </div>

             <div class="form-group">
                 <label for="edit_title"><i class="fa-solid fa-heading"></i> Offer Title:</label>
                 <input type="text" id="edit_title" name="title" value="<?= htmlspecialchars($internshipDetails['title'] ?? '') ?>" required>
             </div>

             <div class="form-group">
                 <label for="edit_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                 <textarea id="edit_description" name="description" rows="6" required><?= htmlspecialchars($internshipDetails['description'] ?? '') ?></textarea>
             </div>

             <div class="form-group">
                 <label for="edit_remuneration"><i class="fa-solid fa-euro-sign"></i> Remuneration (€/month, optional):</label>
                 {/* Format number for display, but submit raw value */}
                 <input type="number" step="0.01" min="0" id="edit_remuneration" name="remuneration" value="<?= htmlspecialchars($internshipDetails['remuneration'] ?? '') ?>" placeholder="Leave blank if unpaid">
             </div>

             <div class="form-group">
                 <label for="edit_offre_date"><i class="fa-regular fa-calendar-check"></i> Offer Available Date:</label>
                 <input type="date" id="edit_offre_date" name="offre_date" value="<?= htmlspecialchars($internshipDetails['offre_date'] ?? '') ?>" required>
             </div>

             <?php
                 // Display creator IDs if admin is editing
                 if ($loggedInUserRole === 'admin') {
                     $offerCreatorText = 'Admin';
                     if (isset($internshipDetails['created_by_pilote_id']) && $internshipDetails['created_by_pilote_id']) {
                         $offerCreatorText = 'Pilote ID: ' . htmlspecialchars($internshipDetails['created_by_pilote_id']);
                     }
                     echo '<p><small>Offer created by: ' . $offerCreatorText . '</small></p>';

                     $companyManagerText = 'Admin/Old'; // Default if not set
                     if (isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] !== null) {
                         $companyManagerText = 'Pilote ID: ' . htmlspecialchars($internshipDetails['company_creator_id']);
                     } elseif (isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] === null) {
                          $companyManagerText = 'Admin'; // Explicitly Admin
                     }
                     echo '<p><small>Company managed by: ' . $companyManagerText . '</small></p>';
                 }
             ?>

            <button type="submit"><i class="fa-solid fa-save"></i> Update Internship Offer</button>

            <!-- Delete Button Area -->
            <span class="delete-action">
                <?php
                    // Determine if the delete button should be shown based on controller logic
                    $showDeleteButton = false;
                    if ($loggedInUserRole === 'admin') {
                        $showDeleteButton = true;
                    } elseif ($loggedInUserRole === 'pilote') {
                        // Show if pilote created the company OR the internship
                        if ((isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] == $loggedInUserId) ||
                            (isset($internshipDetails['created_by_pilote_id']) && $internshipDetails['created_by_pilote_id'] == $loggedInUserId)) {
                            $showDeleteButton = true;
                        }
                    }
                ?>
                <?php if ($showDeleteButton): ?>
                    <a href="#" onclick="if(confirm('Are you sure you want to delete this internship offer?')) { document.getElementById('delete-form-<?= htmlspecialchars($internshipDetails['id_internship']) ?>').submit(); } return false;">
                        <i class="fa-solid fa-trash-alt"></i> Delete
                    </a>
                <?php endif; ?>
            </span>
            <!-- End Delete Button Area -->

        </form> <!-- End Main Update Form -->

        <!-- Hidden Delete Form -->
        <?php if ($showDeleteButton): // Only render the form if the button is shown ?>
            <form id="delete-form-<?= htmlspecialchars($internshipDetails['id_internship']) ?>" method="post" action="internshipController.php" style="display:none;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= htmlspecialchars($internshipDetails['id_internship']) ?>">
            </form>
        <?php endif; ?>
        <!-- End Hidden Delete Form -->

    </div>

    {/* <script src="../View/script.js"></script> */}
</body>
</html>