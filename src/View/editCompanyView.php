<?php
// Location: /home/demy/project-dev-web/src/View/editCompanyView.php
// Included by editCompany.php controller
// Assumes variables: $companyDetails (array), $pageTitle, $errorMessage, $successMessage
// Assumes $loggedInUserRole is available (implicitly via AuthSession::getUserData)

// Prevent direct access
if (!isset($companyDetails)) { die("Direct access not permitted."); }

// Back link logic
$loggedInUserRole = AuthSession::getUserData('user_role'); // Get role for dashboard URL
$backUrl = '../Controller/companyController.php'; // Default back to list
$backText = 'Back to Company List';
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php';

// Helper function for picture Data URI
function generateCompanyPicDataUri($mime, $data) { if (!empty($mime) && !empty($data)) { $picData = is_resource($data) ? stream_get_contents($data) : $data; if ($picData) { return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData); } } return null; }
$companyPicSrc = generateCompanyPicDataUri($companyDetails['company_picture_mime'] ?? null, $companyDetails['company_picture'] ?? null);
$defaultCompanyPic = '../View/images/default_company.png'; // ** ADJUST PATH AS NEEDED **

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
            max-width: 960px; /* Adjusted max width for edit form */
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
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary { /* Used for Back button */
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
         .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .btn-success {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        /* Company picture section */
        .company-pic-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background-color: var(--input-bg);
            border-radius: 16px;
            border: 1px dashed var(--card-border);
        }

        .company-pic-preview {
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            padding: 10px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .file-upload-container {
            width: 100%;
            max-width: 300px; /* Limit width of upload button area */
            position: relative;
            margin-bottom: 1rem;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1rem;
            background: var(--gradient-primary);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4); /* Adjusted focus color */
        }

        .file-upload-label i {
            margin-right: 0.5rem;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-name-display {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-align: center;
        }

        .remove-pic-container {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }

        .remove-pic-label {
            display: flex;
            align-items: center;
            color: #ef4444;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .remove-pic-label:hover {
            text-decoration: underline;
        }

        .remove-pic-checkbox {
            margin-right: 0.5rem;
            width: 16px;
            height: 16px;
        }

        /* Back link - Styling removed, handled by .btn now */
        /* .back-link { ... } */
        /* .back-link-container { ... } */ /* Removed */

        /* Form info */
        .form-info {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-top: 1rem;
            padding: 0.75rem;
            background-color: var(--input-bg);
            border-radius: 8px;
            border: 1px solid var(--card-border);
        }

        .form-info p {
            margin: 0;
            display: flex;
            align-items: center;
        }

        .form-info i {
            width: 20px;
            margin-right: 0.5rem;
            text-align: center;
        }

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
            box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4); /* Adjusted hover color */
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
             /* Adjust body padding */
             body {
                 padding-top: 70px;
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
                    <h1><i class="fas fa-building me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= htmlspecialchars($dashboardUrl) ?>">Dashboard</a></li>
                        <li><a href="<?= htmlspecialchars($backUrl) ?>">Company List</a></li>
                        <li>Edit Company</li>
                    </ul>
                </div>
                 <!-- Logout button was in sidebar, now removed -->
            </div>

            <!-- REMOVED: Original Back Link Container -->

            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Edit Company Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-pen-to-square"></i> Edit Company Details</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">

                        <!-- Company Logo Section -->
                        <div class="company-pic-container">
                            <img src="<?= $companyPicSrc ?? $defaultCompanyPic ?>" alt="Company Logo" class="company-pic-preview" id="picPreview">

                            <div class="file-upload-container">
                                <label for="company_picture" class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload New Logo
                                </label>
                                <input type="file" id="company_picture" name="company_picture" accept=".jpg,.jpeg,.png,.gif,.webp" class="file-upload-input" onchange="previewFile()">
                                <div class="file-name-display" id="fileName">No file selected</div>
                            </div>

                            <?php if ($companyPicSrc): ?>
                            <div class="remove-pic-container">
                                <label for="remove_company_pic" class="remove-pic-label">
                                    <input type="checkbox" id="remove_company_pic" name="remove_company_pic" value="1" class="remove-pic-checkbox">
                                    <span>Remove current logo</span>
                                </label>
                            </div>
                            <?php endif; ?>

                            <small class="text-muted">Acceptable formats: JPG, PNG, GIF, WebP. Maximum size: 2MB. Recommended size: 300x150 pixels.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name"><i class="fa-regular fa-building"></i> Company Name:</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($companyDetails['name_company'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                                    <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($companyDetails['location'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="email"><i class="fa-regular fa-envelope"></i> Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($companyDetails['email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($companyDetails['phone_number'] ?? '') ?>" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="url"><i class="fa-solid fa-link"></i> Website URL:</label>
                                    <input type="url" class="form-control" id="url" name="url" value="<?= htmlspecialchars($companyDetails['company_url'] ?? '') ?>" placeholder="https://www.example.com">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($companyDetails['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <?php
                            // Optionally display creator ID if admin is viewing
                            if (isset($companyDetails['created_by_pilote_id']) && $loggedInUserRole === 'admin') {
                                echo '<div class="form-info">';
                                echo '<p><i class="fas fa-user-tie"></i> Managed by Pilote ID: ' . htmlspecialchars($companyDetails['created_by_pilote_id']) . '</p>';
                                echo '</div>';
                            }
                        ?>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success"><i class="fa-solid fa-save"></i> Update Company</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- End main-container -->
    </div> <!-- End main-wrapper -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Preview function for company picture
        function previewFile() {
            const preview = document.getElementById('picPreview');
            const fileInput = document.getElementById('company_picture');
            const fileNameDisplay = document.getElementById('fileName');

            if (!preview || !fileInput || !fileInput.files || fileInput.files.length === 0) {
                 if(fileNameDisplay) fileNameDisplay.textContent = "No file selected"; // Clear filename if no file
                 return;
            }

            const file = fileInput.files[0];
            // Update file name display
            if (fileNameDisplay) {
                fileNameDisplay.textContent = file.name;
            }

            const reader = new FileReader();
            reader.addEventListener("load", () => { preview.src = reader.result; }, false);

            if (file) {
                reader.readAsDataURL(file);
            }

            // Optionally clear remove checkbox if new file selected
            const removeCheckbox = document.getElementById('remove_company_pic');
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
        }

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
