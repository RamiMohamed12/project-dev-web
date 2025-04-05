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
    <style>
        :root {
            /* Light Theme Colors */
            --bg-primary-light: #f8f9fc;
            --bg-secondary-light: #ffffff;
            --text-primary-light: #1a1e2c;
            --text-secondary-light: #4a5568;
            --card-border-light: #e2e8f0;
            --card-shadow-light: 0 4px 20px rgba(0, 0, 0, 0.05);
            /* --navbar-bg-light: rgba(255, 255, 255, 0.8); */ /* Removed */
            --gradient-primary-light: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-light: linear-gradient(135deg, #3b82f6, #2dd4bf);
            --gradient-success-light: linear-gradient(135deg, #10b981, #34d399);
            --gradient-danger-light: linear-gradient(135deg, #ef4444, #f87171);
            --input-bg-light: #f1f5f9;
            --input-border-light: #e2e8f0;
            --button-hover-light: #f1f5f9;
            --bg-gradient-spot1-light: rgba(99, 102, 241, 0.15);
            --bg-gradient-spot2-light: rgba(139, 92, 246, 0.15);
            --bg-dots-light: rgba(99, 102, 241, 0.15);
            --glass-bg-light: rgba(255, 255, 255, 0.7);
            --glass-border-light: rgba(255, 255, 255, 0.5);

            /* Dark Theme Colors */
            --bg-primary-dark: #13151e;
            --bg-secondary-dark: #1a1e2c;
            --text-primary-dark: #f1f5f9;
            --text-secondary-dark: #a0aec0;
            --card-border-dark: #2d3748;
            --card-shadow-dark: 0 4px 20px rgba(0, 0, 0, 0.2);
            /* --navbar-bg-dark: rgba(26, 30, 44, 0.8); */ /* Removed */
            --gradient-primary-dark: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-dark: linear-gradient(135deg, #3b82f6, #2dd4bf);
            --gradient-success-dark: linear-gradient(135deg, #10b981, #34d399);
            --gradient-danger-dark: linear-gradient(135deg, #ef4444, #f87171);
            --input-bg-dark: #2d3748;
            --input-border-dark: #4a5568;
            --button-hover-dark: #2d3748;
            --bg-gradient-spot1-dark: rgba(99, 102, 241, 0.2);
            --bg-gradient-spot2-dark: rgba(139, 92, 246, 0.2);
            --bg-dots-dark: rgba(139, 92, 246, 0.15);
            --glass-bg-dark: rgba(26, 30, 44, 0.7);
            --glass-border-dark: rgba(45, 55, 72, 0.5);

            /* Active theme (default to light) */
            --bg-primary: var(--bg-primary-light);
            --bg-secondary: var(--bg-secondary-light);
            --text-primary: var(--text-primary-light);
            --text-secondary: var(--text-secondary-light);
            --card-border: var(--card-border-light);
            --card-shadow: var(--card-shadow-light);
            --gradient-primary: var(--gradient-primary-light);
            --gradient-accent: var(--gradient-accent-light);
            --gradient-success: var(--gradient-success-light);
            --gradient-danger: var(--gradient-danger-light);
            --input-bg: var(--input-bg-light);
            --input-border: var(--input-border-light);
            --button-hover: var(--button-hover-light);
            --bg-gradient-spot1: var(--bg-gradient-spot1-light);
            --bg-gradient-spot2: var(--bg-gradient-spot2-light);
            --glass-bg: var(--glass-bg-light);
            --glass-border: var(--glass-border-light);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
             /* Add padding top for fixed button */
            padding-top: 80px;
        }

        /* Main Layout - Removed flex */
        .main-wrapper {
            display: block; /* Changed from flex */
            min-height: 100vh;
        }

        /* Background elements */
        .bg-gradient-spot {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }

        .bg-gradient-spot-1 {
            width: 40vw;
            height: 40vw;
            background: var(--bg-gradient-spot1);
            top: -10%;
            left: -10%;
        }

        .bg-gradient-spot-2 {
            width: 30vw;
            height: 30vw;
            background: var(--bg-gradient-spot2);
            bottom: -5%;
            right: -5%;
        }

        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 40px 40px;
            background-image:
                radial-gradient(circle, var(--bg-dots-light) 1px, transparent 1px);
            z-index: -1;
            opacity: 0.4;
        }

        /* --- REMOVED SIDEBAR STYLES --- */

        /* Main content - Now centered */
        .main-container {
            /* Removed flex: 1; */
            /* Removed margin-left: 280px; */
            padding: 2rem;
            /* Removed min-height: 100vh; */
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--text-secondary) transparent;
            /* Add centering styles */
            max-width: 960px; /* Adjusted for edit form */
            margin-left: auto;
            margin-right: auto;
        }

        .main-container::-webkit-scrollbar {
            width: 6px;
        }

        .main-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-container::-webkit-scrollbar-thumb {
            background-color: var(--text-secondary);
            border-radius: 10px;
        }

        /* Header styles */
        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between; /* Only aligns title block now */
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping */
            gap: 1rem;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }

        .page-header .breadcrumb {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .page-header .breadcrumb li {
            display: flex;
            align-items: center;
        }

        .page-header .breadcrumb li:not(:last-child)::after {
            content: '/';
            margin: 0 0.5rem;
            color: var(--text-secondary);
        }

        .page-header .breadcrumb a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .page-header .breadcrumb a:hover {
            color: var(--text-primary);
        }

        /* Alert/message styling */
        .message {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            animation: fadeUp 0.6s ease forwards;
            border: 1px solid transparent;
        }

        .message i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }

        .success-message {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.2);
        }

        /* Card styling */
        .card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background: var(--input-bg);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header h2 i {
            margin-right: 0.75rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 141, 0.25); /* Adjusted focus color */
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-primary:hover, .btn-success:hover, .btn-danger:hover {
            transform: translateY(-3px);
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-success:hover {
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-danger:hover {
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping */
            gap: 1rem;
            margin-top: 2rem;
        }

        .form-info {
            background-color: var(--input-bg);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid var(--card-border);
            margin-top: 1.5rem;
        }

        .form-info p {
            margin: 0.5rem 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-info i {
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }

        /* Back link - Styling removed, handled by .btn */
        /* .back-link { ... } */
        /* .back-link-container { ... } */ /* Removed */

        /* Theme toggle button */
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            z-index: 100;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        /* --- REMOVED MOBILE NAVBAR STYLES --- */

        /* Dark mode */
        .dark-mode {
            --bg-primary: var(--bg-primary-dark);
            --bg-secondary: var(--bg-secondary-dark);
            --text-primary: var(--text-primary-dark);
            --text-secondary: var(--text-secondary-dark);
            --card-border: var(--card-border-dark);
            --card-shadow: var(--card-shadow-dark);
            --input-bg: var(--input-bg-dark);
            --input-border: var(--input-border-dark);
            --button-hover: var(--button-hover-dark);
            --bg-gradient-spot1: var(--bg-gradient-spot1-dark);
            --bg-gradient-spot2: var(--bg-gradient-spot2-dark);
            --glass-bg: var(--glass-bg-dark);
            --glass-border: var(--glass-border-dark);
        }

        /* NEW: Styles for the fixed top-left button */
        .dashboard-button-top-left {
            position: fixed; /* Keep it fixed */
            top: 15px;       /* Adjust spacing from top */
            left: 15px;      /* Adjust spacing from left */
            z-index: 1010;  /* Ensure it's above theme toggle and content */
        }


        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            opacity: 0;
            animation: fadeUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Media queries */
        @media (max-width: 992px) {
             /* REMOVED: Sidebar specific mobile styles */
             /* REMOVED: .main-container { margin-left: 0; width: 100%; } */
             /* REMOVED: .navbar { display: flex; } */
             .main-container {
                 max-width: 95%; /* Use more width */
             }
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
             /* Adjust body padding */
             body {
                 padding-top: 70px;
             }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .main-container {
                padding: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            /* Further adjust body padding */
             body {
                 padding-top: 65px;
             }
            /* Optionally make the top-left button smaller */
             .dashboard-button-top-left .btn { /* Target the button inside */
                 padding: 0.5rem 1rem;
                 font-size: 0.85rem;
             }
        }
    </style>
</head>
<body>
    <!-- ADDED: Back Button -->
    <div class="dashboard-button-top-left fade-in">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-primary"> <!-- Using btn and btn-primary -->
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>
    </div>

    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>

    <!-- Theme toggle button -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="main-wrapper">
        <!-- REMOVED: Sidebar -->

        <!-- Main content -->
        <div class="main-container">
            <!-- REMOVED: Top navbar for mobile -->

            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fas fa-edit me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= htmlspecialchars($dashboardUrl) ?>">Dashboard</a></li>
                        <li><a href="<?= htmlspecialchars($backUrl) ?>">Internship List</a></li>
                        <li>Edit Internship</li>
                    </ul>
                </div>
                 <!-- Logout button removed -->
            </div>

            <!-- REMOVED: Original Back Link Container -->

            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>

            <!-- Edit Internship Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-pen-to-square"></i> Edit Internship Details</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="internshipController.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id_internship" value="<?= htmlspecialchars($internshipDetails['id_internship']) ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="edit_id_company"><i class="fa-regular fa-building"></i> Company:</label>
                                    <select class="form-control" id="edit_id_company" name="id_company" required>
                                        <option value="" disabled>-- Select Company --</option>
                                        <?php if (is_array($companiesList) && !empty($companiesList)): ?>
                                            <?php foreach ($companiesList as $company):
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
                                        <small class="text-muted">You can only select companies you manage.</small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="edit_title"><i class="fa-solid fa-heading"></i> Offer Title:</label>
                                    <input type="text" class="form-control" id="edit_title" name="title" value="<?= htmlspecialchars($internshipDetails['title'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="edit_description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="6" required><?= htmlspecialchars($internshipDetails['description'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="edit_remuneration"><i class="fa-solid fa-euro-sign"></i> Remuneration (€/month, optional):</label>
                                    <input type="number" class="form-control" step="0.01" min="0" id="edit_remuneration" name="remuneration" value="<?= htmlspecialchars($internshipDetails['remuneration'] ?? '') ?>" placeholder="Leave blank if unpaid">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="edit_offre_date"><i class="fa-regular fa-calendar-check"></i> Offer Available Date:</label>
                                    <input type="date" class="form-control" id="edit_offre_date" name="offre_date" value="<?= htmlspecialchars($internshipDetails['offre_date'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <?php
                            // Display creator IDs if admin is editing
                            if ($loggedInUserRole === 'admin') {
                                $offerCreatorText = 'Admin';
                                if (isset($internshipDetails['created_by_pilote_id']) && $internshipDetails['created_by_pilote_id']) {
                                    $offerCreatorText = 'Pilote ID: ' . htmlspecialchars($internshipDetails['created_by_pilote_id']);
                                }

                                $companyManagerText = 'Admin/Old'; // Default if not set
                                if (isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] !== null) {
                                    $companyManagerText = 'Pilote ID: ' . htmlspecialchars($internshipDetails['company_creator_id']);
                                } elseif (isset($internshipDetails['company_creator_id']) && $internshipDetails['company_creator_id'] === null) {
                                    $companyManagerText = 'Admin'; // Explicitly Admin
                                }

                                echo '<div class="form-info">';
                                echo '<p><i class="fas fa-info-circle"></i> Offer created by: ' . $offerCreatorText . '</p>';
                                echo '<p><i class="fas fa-user-tie"></i> Company managed by: ' . $companyManagerText . '</p>';
                                echo '</div>';
                            }
                        ?>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success"><i class="fa-solid fa-save"></i> Update Internship Offer</button>

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
                                <a href="#" onclick="if(confirm('Are you sure you want to delete this internship offer?')) { document.getElementById('delete-form-<?= htmlspecialchars($internshipDetails['id_internship']) ?>').submit(); } return false;" class="btn btn-danger">
                                    <i class="fa-solid fa-trash-alt"></i> Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Hidden Delete Form -->
                    <?php if ($showDeleteButton): // Only render the form if the button is shown ?>
                        <form id="delete-form-<?= htmlspecialchars($internshipDetails['id_internship']) ?>" method="post" action="internshipController.php" style="display:none;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($internshipDetails['id_internship']) ?>">
                        </form>
                    <?php endif; ?>
                    <!-- End Hidden Delete Form -->
                </div>
            </div>
        </div> <!-- End main-container -->
    </div> <!-- End main-wrapper -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');

            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }

            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');

                if (body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('theme', 'dark');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('theme', 'light');
                }
            });

            // REMOVED: Sidebar toggle for mobile script

        });
    </script>
</body>
</html>
