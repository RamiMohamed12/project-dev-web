<?php
// Location: /home/demy/project-dev-web/src/View/manageCompaniesView.php
// Included by companyController.php
// Assumes variables: $companies, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted.");
}

// URL for the appropriate dashboard
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
    <link href="css/manageCompaniesView.css" rel="stylesheet">    
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
                    <p>Company Management</p>
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
                    <a href="userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="companyController.php" class="nav-link active">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <?php if ($loggedInUserRole === 'admin'): ?>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <?php endif; ?>
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
                    <span><i class="fas fa-building me-2"></i>Company Management</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fas fa-building me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                        <li>Company Management</li>
                    </ul>
                </div>
            </div>
            
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Add Company Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-building-circle-arrow-right"></i> Add New Company</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_name"><i class="fa-regular fa-building"></i> Company Name:</label>
                                    <input type="text" class="form-control" id="add_name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                                    <input type="text" class="form-control" id="add_location" name="location" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_email"><i class="fa-regular fa-envelope"></i> Email:</label>
                                    <input type="email" class="form-control" id="add_email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                                    <input type="text" class="form-control" id="add_phone" name="phone" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_url"><i class="fa-solid fa-link"></i> Website URL (Optional):</label>
                                    <input type="url" class="form-control" id="add_url" name="url" placeholder="https://www.example.com">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Company</button>
                    </form>
                </div>
            </div>

            <!-- Company List Card -->
            <div class="card fade-in delay-1">
                <div class="card-header">
                    <h2><i class="fa-solid fa-list-ul"></i> Company List</h2>
                </div>
                <div class="card-body">
                    <?php if (is_array($companies) && !empty($companies)): ?>
                        <div class="company-list">
                            <?php foreach ($companies as $company):
                                // Determine if current user can modify this company
                                $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($company['created_by_pilote_id']) && $company['created_by_pilote_id'] == $loggedInUserId));
                                // Get first letter of company name for logo
                                $logoLetter = strtoupper(substr($company['name_company'], 0, 1));
                            ?>
                                <div class="company-card fade-in">
                                    <div class="company-card-header">
                                        <div class="company-logo">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="company-info">
                                            <h3 title="<?= htmlspecialchars($company['name_company']) ?>"><?= htmlspecialchars($company['name_company']) ?></h3>
                                            <p><?= htmlspecialchars($company['location']) ?></p>
                                        </div>
                                    </div>
                                    <div class="company-card-body">
                                        <div class="company-detail">
                                            <i class="fas fa-envelope"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Email</div>
                                                <div class="company-detail-value"><?= htmlspecialchars($company['email']) ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="company-detail">
                                            <i class="fas fa-phone"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Phone</div>
                                                <div class="company-detail-value"><?= htmlspecialchars($company['phone_number']) ?></div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($company['company_url'])): ?>
                                        <div class="company-detail">
                                            <i class="fas fa-globe"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Website</div>
                                                <div class="company-detail-value">
                                                    <a href="<?= htmlspecialchars($company['company_url']) ?>" target="_blank" rel="noopener noreferrer" class="website-link">
                                                        <i class="fas fa-external-link-alt"></i>Visit Website
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($loggedInUserRole === 'admin'): ?>
                                        <div class="company-detail">
                                            <i class="fas fa-user-plus"></i>
                                            <div class="company-detail-content">
                                                <div class="company-detail-label">Created By</div>
                                                <div class="company-detail-value"><?= htmlspecialchars($company['created_by_pilote_id'] ?? 'Admin/Old') ?></div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="company-card-footer">
                                        <div class="company-id">
                                            <i class="fas fa-id-card"></i> ID: <?= htmlspecialchars($company['id_company']) ?>
                                        </div>
                                        <div class="company-actions">
                                            <?php if ($canModify): ?>
                                                <a href="editCompany.php?id=<?= $company['id_company'] ?>" class="btn btn-warning btn-sm" title="Edit Company">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete company <?= htmlspecialchars(addslashes($company['name_company'])) ?>? Check related internships.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $company['id_company'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Company">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="company-id">
                                                    <i class="fas fa-eye"></i> View Only
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-building-circle-xmark"></i>
                            <h3>No companies found</h3>
                            <p>
                                <?= ($loggedInUserRole === 'pilote') ? 'You haven\'t created any companies yet.' : 'No companies have been added to the system yet.' ?>
                                Add your first company using the form above.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="JavaScript/manageCompaniesView.js> </script>

</body>
</html>
