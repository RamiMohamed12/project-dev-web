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
         .nav-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .nav-icon {
            color: white;
            font-size: 18px;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-icon:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .nav-icon i {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Footer styling */
        footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 25px;
            text-align: center;
            margin-left: 250px;
            margin-top: auto;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        footer p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
            font-weight: 500;
        }

        /* Ensure proper layout */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
        }
    </style>
</head>
<body class="admin-layout">
    <!-- Navbar -->
    <nav class="top-navbar">
        <div class="nav-left">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
        </div>
        <div class="nav-right">
            <span class="nav-email"><?= htmlspecialchars($displayEmail ?? '') ?></span>
            <div class="nav-icons">
                <a href="#" class="nav-icon">
                    <i class="fa-solid fa-gear"></i>
                </a>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-icon">
                    <i class="fa-solid fa-user"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="<?= isset($profilePicSrc) ? $profilePicSrc : '../View/images/default_avatar.png' ?>" alt="Profile Picture">
            <h2><?= ucfirst($loggedInUserRole) ?> Panel</h2>
        </div>
        <div class="sidebar-menu">
            <?php if ($loggedInUserRole === 'admin'): ?>
                <a href="../View/admin.php">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
                <a href="../Controller/userController.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="../Controller/companyController.php" class="active">
                    <i class="fa-solid fa-building"></i>
                    <span>Manage Companies</span>
                </a>
                <a href="../Controller/internshipController.php">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Manage Offers</span>
                </a>
            <?php elseif ($loggedInUserRole === 'pilote'): ?>
                <a href="../View/pilote.php">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
                <a href="../Controller/userController.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Students</span>
                </a>
                <a href="../Controller/internshipController.php">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Manage Offers</span>
                </a>
            <?php endif; ?>
            
            <?php if ($loggedInUserId): ?>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>My Profile</span>
                </a>
            <?php endif; ?>
            
            <a href="../Controller/logoutController.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main content - directly, without containers -->
    <main>
        <h1 class="page-title">
            <span class="circle-plus-icon"></span>
            Add New Company
        </h1>
        
        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message">
                <i class="fa-solid fa-circle-exclamation"></i> 
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message">
                <i class="fa-solid fa-check-circle"></i> 
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>
        
        <!-- Form directly in main, without container -->
        <form method="post" action="../Controller/companyController.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>
                    <i class="fa-solid fa-building"></i>
                    Company Name:
                </label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-location-dot"></i>
                    Location:
                </label>
                <input type="text" name="location" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-align-left"></i>
                    Description:
                </label>
                <textarea name="description" required></textarea>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-regular fa-envelope"></i>
                    Email:
                </label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-phone"></i>
                    Phone:
                </label>
                <input type="text" name="phone" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number">
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-link"></i>
                    Website URL (Optional):
                </label>
                <input type="url" name="url" placeholder="https://www.example.com">
            </div>

            <button type="submit"><i class="fa-solid fa-plus"></i> Add Company</button>
        </form>
        
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
                        <th>Website</th>
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
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 8 : 7 ?>">
                                No companies found<?= ($loggedInUserRole === 'pilote') ? ' created by you' : '' ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
    
    <script src="../View/script.js"></script>
</body>
</html>
