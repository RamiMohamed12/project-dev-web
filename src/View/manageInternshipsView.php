<?php
// Location: src/View/manageInternshipsView.php
// Included by internshipController.php
// Assumes variables: $internships, $companies, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage

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
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        <!-- Add Internship Form -->
        <div class="form-container">
            <h2><i class="fa-solid fa-briefcase"></i> Add New Internship Offer</h2>
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="add_title"><i class="fa-solid fa-heading"></i> Title:</label>
                    <input type="text" id="add_title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="add_company"><i class="fa-solid fa-building"></i> Company:</label>
                    <select id="add_company" name="company_id" required>
                        <option value="">-- Select Company --</option>
                        <?php if (is_array($companies) && !empty($companies)): ?>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= htmlspecialchars($company['id_company']) ?>">
                                    <?= htmlspecialchars($company['name_company']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                    <textarea id="add_description" name="description" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="add_remuneration"><i class="fa-solid fa-money-bill"></i> Remuneration (optional):</label>
                    <input type="number" id="add_remuneration" name="remuneration" step="0.01" min="0">
                </div>
                
                <div class="form-group">
                    <label for="add_date"><i class="fa-solid fa-calendar"></i> Date:</label>
                    <input type="date" id="add_date" name="offre_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <button type="submit"><i class="fa-solid fa-plus"></i> Add Internship Offer</button>
            </form>
        </div>

        <!-- Internship List Section -->
        <section id="internships">
            <h2><i class="fa-solid fa-list-ul"></i> Internship Offers List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Date</th>
                        <th>Remuneration</th>
                        <?php if ($loggedInUserRole === 'admin'): ?>
                            <th>Created By</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($internships) && !empty($internships)): ?>
                        <?php foreach ($internships as $internship):
                            // Allow all admins and pilotes to modify internships
                            $canModify = ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote');
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($internship['id_internship']) ?></td>
                                <td><?= htmlspecialchars($internship['title']) ?></td>
                                <td><?= htmlspecialchars($internship['name_company']) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($internship['offre_date']))) ?></td>
                                <td>
                                    <?php if (!empty($internship['remuneration'])): ?>
                                        <?= htmlspecialchars(number_format($internship['remuneration'], 2)) ?>
                                    <?php else: ?>
                                        <span style="color: #888;">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($loggedInUserRole === 'admin'): ?>
                                    <td>All Users</td>
                                <?php endif; ?>
                                <td class="actions">
                                    <?php if ($canModify): ?>
                                        <a href="editInternship.php?id=<?= $internship['id_internship'] ?>" class="edit-btn" title="Edit Internship">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" 
                                              onsubmit="return confirm('Delete internship offer: <?= htmlspecialchars(addslashes($internship['title'])) ?>? This may affect student applications.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $internship['id_internship'] ?>">
                                            <button type="submit" class="delete-btn" title="Delete Internship">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                        <a href="../View/viewInternship.php?id=<?= $internship['id_internship'] ?>" class="view-btn" title="View Details">
                                            <i class="fa-solid fa-eye"></i> Details
                                        </a>
                                    <?php else: ?>
                                        <span>(View Only)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 7 : 6 ?>">
                                No internship offers found<?= ($loggedInUserRole === 'pilote') ? ' created by you' : '' ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </div> <!-- /.container -->
</body>
</html> 