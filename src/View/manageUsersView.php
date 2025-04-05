<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// Included by userController.php
// Assumes variables: $students, $pilotes, $admins, $loggedInUserRole, $loggedInUserId,
// $canManageAdmins, $canManagePilotes, $pageTitle, $errorMessage, $successMessage,
// $studentPagination, $pilotePagination, $adminPagination

// Prevent direct access & check necessary variables
if (!isset($loggedInUserRole) || !isset($loggedInUserId) || !isset($studentPagination)) {
     die("Direct access not permitted or required data missing.");
}

// Get itemsPerPage from pagination data (passed by controller)
$itemsPerPage = $studentPagination['itemsPerPage'] ?? 4;

// *** Helper function to prepare string data within an array for safe JSON encoding for JavaScript ***
function prepare_data_for_js($data_array, $fields_to_escape = []) {
    if (!is_array($data_array)) {
        return []; // Return empty if not an array
    }
    $processed_array = [];
    foreach ($data_array as $item) {
        if (!is_array($item)) continue; // Skip if item is not an array

        $processed_item = $item; // Copy the item
        foreach ($fields_to_escape as $field) {
            // Check if the field exists and is a string
            if (isset($processed_item[$field]) && is_string($processed_item[$field])) {
                // Replace various newline types with escaped newline for JS
                $processed_item[$field] = str_replace(["\r\n", "\r", "\n"], '\\n', $processed_item[$field]);
                // You could add other replacements here if needed, e.g., for problematic quotes
                // $processed_item[$field] = str_replace("'", "\\'", $processed_item[$field]);
                // $processed_item[$field] = str_replace('"', '\\"', $processed_item[$field]); // json_encode handles quotes well usually
            }
        }
        $processed_array[] = $processed_item;
    }
    return $processed_array;
}

// Prepare the initial data *before* json_encode using the helper function
// Specify fields that might contain problematic characters (especially newlines)
$students_js_safe = prepare_data_for_js($students, ['description', 'location', 'school', 'name', 'email']);
$pilotes_js_safe = prepare_data_for_js($pilotes, ['location', 'name', 'email']); // Adjust fields if pilotes have descriptions etc.
$admins_js_safe = prepare_data_for_js($admins, ['name', 'email']); // Adjust fields if admins have descriptions etc.

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
    <link href="css/manageUsersView.css" rel="stylesheet">

    <script src="JavaScript/Users.js"> </script> 
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
                    <p>User Management</p>
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
                    <a href="userController.php" class="nav-link active">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <?php if ($loggedInUserRole === 'admin'): ?>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
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
                    <span><i class="fas fa-users-gear me-2"></i>User Management</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fa-solid fa-users-gear me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                        <li>User Management</li>
                    </ul>
                </div>
            </div>
            
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Add User Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-plus"></i> Add New User</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="addUserForm">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_user_type">User Type:</label>
                                    <select class="form-control" id="add_user_type" name="type" required onchange="toggleUserFields()">
                                        <option value="" disabled selected>-- Select --</option>
                                        <?php if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?><option value="student">Student</option><?php endif; ?>
                                        <?php if ($canManagePilotes): ?><option value="pilote">Pilote</option><?php endif; ?>
                                        <?php if ($canManageAdmins): ?><option value="admin">Admin</option><?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_name">Name:</label>
                                    <input type="text" class="form-control" id="add_name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_email">Email:</label>
                                    <input type="email" class="form-control" id="add_email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_password">Password:</label>
                                    <input type="password" class="form-control" id="add_password" name="password" required autocomplete="new-password">
                                    <span id="add_password-strength" class="password-strength-indicator"></span>
                                    <small class="text-muted">Min. 8 chars, 1 uppercase, 1 number for Medium strength.</small>
                                </div>
                            </div>
                        </div>

                        <div id="pilote_specific_fields" style="display: none;" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_location">Location:</label>
                                    <input type="text" class="form-control" id="add_location" name="location">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_phone">Phone:</label>
                                    <input type="text" class="form-control" id="add_phone" name="phone">
                                </div>
                            </div>
                        </div>

                        <div id="student_specific_fields" style="display: none;" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_dob">Date of Birth:</label>
                                    <input type="date" class="form-control" id="add_dob" name="dob">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_year">Year:</label>
                                    <select class="form-control" id="add_year" name="year">
                                        <option value="" disabled selected>-- Select --</option>
                                        <option value="1st">1st</option>
                                        <option value="2nd">2nd</option>
                                        <option value="3rd">3rd</option>
                                        <option value="4th">4th</option>
                                        <option value="5th">5th</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_school">School:</label>
                                    <input type="text" class="form-control" id="add_school" name="school">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_description">Description:</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add User</button>
                    </form>
                </div>
            </div>

            <!-- Student List Section -->
            <div class="card fade-in delay-1">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-graduate"></i> Students</h2>
                </div>
                <div class="card-body">
                    <div id="students-loading" class="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <p>Loading students...</p>
                    </div>
                    <div id="students-error" class="table-error" style="display: none;"></div>
                    <div id="students-list" class="user-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="card-footer">
                    <div id="students-pagination" class="pagination-controls"></div>
                </div>
            </div>

            <!-- Pilote List Section (Admin Only) -->
            <?php if ($canManagePilotes): ?>
            <div class="card fade-in delay-2">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
                </div>
                <div class="card-body">
                    <div id="pilotes-loading" class="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <p>Loading pilotes...</p>
                    </div>
                    <div id="pilotes-error" class="table-error" style="display: none;"></div>
                    <div id="pilotes-list" class="user-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="card-footer">
                    <div id="pilotes-pagination" class="pagination-controls"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Admin List Section (Admin Only) -->
            <?php if ($canManageAdmins): ?>
            <div class="card fade-in delay-3">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
                </div>
                <div class="card-body">
                    <div id="admins-loading" class="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <p>Loading admins...</p>
                    </div>
                    <div id="admins-error" class="table-error" style="display: none;"></div>
                    <div id="admins-list" class="user-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="card-footer">
                    <div id="admins-pagination" class="pagination-controls"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ****** AJAX, Pagination, and Password Strength Script ****** -->
  <script src="JavaScript/manageUsersView.js"> </script>  
</body>
</html>
