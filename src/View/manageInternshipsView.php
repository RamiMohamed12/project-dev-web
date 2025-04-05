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
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/manageInternshipsView.css" rel="stylesheet">
</head>
<body>
    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>
    
    <!-- Theme toggle button -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../View/images/default_avatar.png" alt="Profile Picture">
                <div>
                    <h3>Admin Panel</h3>
                    <p>Internship Management</p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= $dashboardUrl ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link active">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/settingsController.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="../Controller/logoutController.php" class="logout-btn">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="main-container">
            <!-- Top navbar for mobile -->
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none">
                <a class="navbar-brand" href="#">
                    <img src="../View/images/default_avatar.png" alt="Profile">
                    <span><i class="fas fa-file-alt me-2"></i>Internship Management</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                        <li>Internship Management</li>
                    </ul>
                </div>
            </div>
            
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Add Internship Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-plus-circle"></i> Add New Internship Offer</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="internshipController.php">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_id_company"><i class="fa-regular fa-building"></i> Company:</label>
                                    <select class="form-control" id="add_id_company" name="id_company" required>
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
                                        <small class="text-muted">You can only select companies you have created. <a href="companyController.php">Manage Companies</a></small>
                                    <?php elseif(empty($companiesList) && $loggedInUserRole === 'admin'): ?>
                                        <small class="text-muted">No companies found. <a href="companyController.php">Add Companies</a></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_title"><i class="fa-solid fa-heading"></i> Offer Title:</label>
                                    <input type="text" class="form-control" id="add_title" name="title" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="4" required></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_remuneration"><i class="fa-solid fa-euro-sign"></i> Remuneration (€/month, optional):</label>
                                    <input type="number" class="form-control" step="0.01" min="0" id="add_remuneration" name="remuneration" placeholder="e.g., 550.50">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="add_offre_date"><i class="fa-regular fa-calendar-check"></i> Offer Available Date:</label>
                                    <input type="date" class="form-control" id="add_offre_date" name="offre_date" required value="<?= date('Y-m-d'); // Default to today ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Internship Offer</button>
                    </form>
                </div>
            </div>

            <!-- Internship List Card -->
            <div class="card fade-in delay-1">
                <div class="card-header">
                    <h2><i class="fa-solid fa-list-ul"></i> Current Internship Offers</h2>
                </div>
                <div class="card-body">
                    <?php if (is_array($internships) && !empty($internships)): ?>
                        <div class="internship-list">
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
                                    // Format remuneration for display
                                    $formattedRemuneration = $offer['remuneration'] !== null ? number_format((float)$offer['remuneration'], 2, ',', ' ') . ' €' : 'Not specified';
                                ?>
                                <div class="internship-card fade-in">
                                    <div class="internship-card-header">
                                        <h3><?= htmlspecialchars($offer['title']) ?></h3>
                                        <div class="internship-card-company">
                                            <div class="company-logo">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div class="company-info">
                                                <p class="company-name"><?= htmlspecialchars($offer['name_company'] ?? 'N/A') ?></p>
                                                <p class="company-location"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($offer['company_location'] ?? 'Location not specified') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="internship-card-body">
                                        <div class="internship-description">
                                            <?= nl2br(htmlspecialchars($offer['description'])) ?>
                                        </div>
                                        
                                        <div class="internship-detail">
                                            <i class="fas fa-euro-sign"></i>
                                            <span class="internship-detail-label">Remuneration:</span>
                                            <span class="internship-detail-value"><?= $formattedRemuneration ?></span>
                                        </div>
                                        
                                        <div class="internship-detail">
                                            <i class="fas fa-calendar"></i>
                                            <span class="internship-detail-label">Available since:</span>
                                            <span class="internship-detail-value"><?= htmlspecialchars($offer['offre_date']) ?></span>
                                        </div>
                                        
                                        <?php if ($loggedInUserRole === 'admin'): ?>
                                            <div class="internship-detail">
                                                <i class="fas fa-user"></i>
                                                <span class="internship-detail-label">Offer Creator:</span>
                                                <span class="internship-detail-value">
                                                    <?php
                                                        $offerCreatorText = 'Admin';
                                                        if (isset($offer['created_by_pilote_id']) && $offer['created_by_pilote_id']) {
                                                            $offerCreatorText = 'Pilote: ' . htmlspecialchars($offer['created_by_pilote_id']);
                                                        }
                                                        echo $offerCreatorText;
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="internship-detail">
                                                <i class="fas fa-user-tie"></i>
                                                <span class="internship-detail-label">Company Manager:</span>
                                                <span class="internship-detail-value">
                                                    <?php
                                                        $companyManagerText = 'Admin/Old';
                                                        if (isset($offer['company_creator_id']) && $offer['company_creator_id'] !== null) {
                                                            $companyManagerText = 'Pilote: ' . htmlspecialchars($offer['company_creator_id']);
                                                        } elseif (isset($offer['company_creator_id']) && $offer['company_creator_id'] === null) {
                                                            $companyManagerText = 'Admin';
                                                        }
                                                        echo $companyManagerText;
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="internship-card-footer">
                                        <div class="internship-id">
                                            <i class="fas fa-id-card"></i> ID: <?= htmlspecialchars($offer['id_internship']) ?>
                                        </div>
                                        <div class="internship-actions">
                                            <?php if ($canModify): ?>
                                                <a href="internshipController.php?action=edit&id=<?= $offer['id_internship'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <form method="post" action="internshipController.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $offer['id_internship'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this internship offer?');">
                                                        <i class="fa-solid fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="no-permission">
                                                    <i class="fa-solid fa-lock"></i> No permission
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-circle-xmark"></i>
                            <h3>No internship offers found</h3>
                            <p>
                                <?= ($loggedInUserRole === 'pilote') ? 'No offers match your criteria.' : 'No offers have been added to the system yet.' ?>
                                Add your first internship offer using the form above.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src"JavaScript/manageInternshipsView.js"></script> 

</body>
</html>
