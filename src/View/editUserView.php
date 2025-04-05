<?php
// Location: /home/demy/project-dev-web/src/View/editUserView.php
// Included by editUser.php controller
// Assumes variables: $userDetails, $targetUserType, $pageTitle, $errorMessage, $successMessage, $isSelfEdit, $loggedInUserRole

if (!isset($userDetails) || !isset($targetUserType) || !isset($isSelfEdit) || !isset($loggedInUserRole)) { die("Direct access not permitted or required variables missing."); }

// Determine Back link URL and Text (Logic remains the same)
$backUrl = '../Controller/userController.php'; // Default back to user list
$backText = 'Back to User List';
$dashboardFile = '';

// Determine dashboard based on logged-in role
if ($loggedInUserRole === 'admin') $dashboardFile = 'admin.php';
elseif ($loggedInUserRole === 'pilote') $dashboardFile = 'pilote.php';
elseif ($loggedInUserRole === 'student') $dashboardFile = 'student.php'; // Assuming student dashboard exists

if ($dashboardFile) {
    $dashboardUrl = '../View/' . $dashboardFile; // URL for breadcrumbs etc.
    if ($isSelfEdit) {
        // If editing self, always link back to their own dashboard
        $backUrl = $dashboardUrl;
        $backText = 'Back to My Dashboard';
    } else {
        // If Admin/Pilote editing someone else, default is User List, but keep dashboardUrl for breadcrumbs
        // The default backUrl ('../Controller/userController.php') is already set
    }
} else {
    // Fallback if no dashboard file determined (e.g., unknown role)
    $dashboardUrl = '#'; // Placeholder
}


// Helper for profile pic
function generateProfilePicDataUri($mime, $data) { if (!empty($mime) && !empty($data)) { $picData = is_resource($data) ? stream_get_contents($data) : $data; if ($picData) { return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData); } } return null; }
$profilePicSrc = generateProfilePicDataUri($userDetails['profile_picture_mime'] ?? null, $userDetails['profile_picture'] ?? null);
$defaultPic = '../View/images/default_avatar.png'; // ** Ensure this path is correct **

// *** ADDED: Flag and attributes for student self-edit restriction ***
$isStudentSelfEditRestricted = ($isSelfEdit && $loggedInUserRole === 'student');
$readOnlyAttribute = $isStudentSelfEditRestricted ? 'readonly' : '';
$disabledAttribute = $isStudentSelfEditRestricted ? 'disabled' : '';
$readOnlyTitle = $isStudentSelfEditRestricted ? 'title="This field cannot be changed."' : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></title>
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
            overflow-x: hidden; /* Prevent horizontal scroll */
            /* Add padding top to prevent content from hiding under the fixed button */
            padding-top: 80px; /* Adjust value based on button height + desired spacing */
        }

        /* Main Layout - Removed sidebar flex */
        .main-wrapper {
            display: block; /* Changed from flex */
            min-height: 100vh;
        }

        /* Background elements (Keep) */
        .bg-gradient-spot { position: fixed; border-radius: 50%; filter: blur(80px); z-index: -1; }
        .bg-gradient-spot-1 { width: 40vw; height: 40vw; background: var(--bg-gradient-spot1); top: -10%; left: -10%; }
        .bg-gradient-spot-2 { width: 30vw; height: 30vw; background: var(--bg-gradient-spot2); bottom: -5%; right: -5%; }
        .bg-grid { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-size: 40px 40px; background-image: radial-gradient(circle, var(--bg-dots-light) 1px, transparent 1px); z-index: -1; opacity: 0.4; }

        /* --- REMOVED SIDEBAR STYLES --- */

        /* Main content - Centered */
        .main-container {
            /* Removed margin-left */
            padding: 2rem;
            /* Removed min-height: 100vh as body now handles it */
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--text-secondary) transparent;
            max-width: 960px; /* Adjusted max width for edit form */
            margin-left: auto; /* Center the container */
            margin-right: auto; /* Center the container */
        }

        /* Main container scrollbar styling (keep if desired) */
        .main-container::-webkit-scrollbar { width: 6px; }
        .main-container::-webkit-scrollbar-track { background: transparent; }
        .main-container::-webkit-scrollbar-thumb { background-color: var(--text-secondary); border-radius: 10px; }

        /* Center the main content blocks */
        .page-header,
        .message,
        .card
        /* REMOVED: .back-link-container */
        {
            width: 100%; /* Take full width of the centered container */
        }

        /* Header styles (Keep) */
        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between; /* Will just align title block */
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping */
            gap: 1rem;
        }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; color: var(--text-primary); }
        .page-header .breadcrumb { display: flex; list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: var(--text-secondary); }
        .page-header .breadcrumb li { display: flex; align-items: center; }
        .page-header .breadcrumb li:not(:last-child)::after { content: '/'; margin: 0 0.5rem; color: var(--text-secondary); }
        .page-header .breadcrumb a { color: var(--text-secondary); text-decoration: none; transition: color 0.3s ease; }
        .page-header .breadcrumb a:hover { color: var(--text-primary); }

        /* Alert/message styling (Keep) */
        .message { padding: 1rem 1.25rem; margin-bottom: 1.5rem; border-radius: 12px; display: flex; align-items: center; animation: fadeUp 0.6s ease forwards; border: 1px solid transparent; }
        .message i { margin-right: 0.75rem; font-size: 1.25rem; }
        .error-message { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
        .success-message { background-color: rgba(34, 197, 94, 0.1); color: #22c55e; border-color: rgba(34, 197, 94, 0.2); }

        /* Card styling (Keep) */
        .card { background: var(--bg-secondary); border-radius: 16px; border: 1px solid var(--card-border); box-shadow: var(--card-shadow); overflow: hidden; margin-bottom: 1.5rem; }
        .card-header { background: var(--input-bg); padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--card-border); display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; }
        .card-header h2 i { margin-right: 0.75rem; }
        .card-body { padding: 1.5rem; }

        /* Form styling (Keep, adjusted readonly/disabled) */
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary); }
        .form-group label i { margin-right: 0.5rem; width: 16px; text-align: center; }
        .form-control { width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; background: var(--input-bg); border: 1px solid var(--input-border); border-radius: 8px; color: var(--text-primary); transition: all 0.3s ease; }
        .form-control:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25); }
        .form-control[readonly], .form-control[disabled], select[disabled] { background-color: var(--input-bg); opacity: 0.7; cursor: not-allowed; } /* Target select too */
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; font-size: 0.95rem; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn i { margin-right: 0.5rem; }
        .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); }
        .btn-success { background: var(--gradient-success); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
        .btn-success:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); color: white; }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); color: white; } /* Added hover for primary button */


        /* Profile picture section (Keep) */
        .profile-pic-container { display: flex; flex-direction: column; align-items: center; margin-bottom: 1.5rem; padding: 2rem; background-color: var(--input-bg); border-radius: 16px; border: 1px dashed var(--card-border); }
        .profile-pic-preview { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 1.25rem; padding: 5px; background-color: var(--bg-secondary); border: 3px solid var(--card-border); box-shadow: var(--card-shadow); transition: all 0.3s ease; }
        .file-upload-container { width: 100%; max-width: 300px; position: relative; margin-bottom: 1rem; }
        .file-upload-label { display: flex; align-items: center; justify-content: center; padding: 0.75rem 1rem; background: var(--gradient-primary); color: white; border-radius: 8px; cursor: pointer; font-weight: 600; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); transition: all 0.3s ease; }
        .file-upload-label:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); }
        .file-upload-label i { margin-right: 0.5rem; }
        .file-upload-input { position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .file-name-display { margin-top: 0.75rem; font-size: 0.9rem; color: var(--text-secondary); text-align: center; }
        .remove-pic-container { display: flex; align-items: center; margin-top: 0.5rem; }
        .remove-pic-label { display: flex; align-items: center; color: #ef4444; font-size: 0.9rem; cursor: pointer; }
        .remove-pic-label:hover { text-decoration: underline; }
        .remove-pic-checkbox { margin-right: 0.5rem; width: 16px; height: 16px; }

        /* Password strength indicator (Keep) */
        .password-strength-indicator { display: block; margin-top: 0.5rem; font-size: 0.875rem; font-weight: 600; height: 1.2em; }
        .password-strength-indicator.weak { color: #ef4444; }
        .password-strength-indicator.medium { color: #f59e0b; }
        .password-strength-indicator.strong { color: #22c55e; }

        /* Back link styling (Kept for button style, positioning handled by new class) */
        /* REMOVED: .back-link-container { margin-bottom: 1.5rem; text-align: left; } */
        .back-link {
            display: inline-flex; /* Keep button inline */
            align-items: center;
            padding: 0.6rem 1.1rem; /* Slightly smaller padding */
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        .back-link i { margin-right: 0.5rem; }
        .back-link:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); color: white; }

        /* Form info & field notes (Keep) */
        .form-info { font-size: 0.9rem; color: var(--text-secondary); margin-top: 1rem; padding: 0.75rem; background-color: var(--input-bg); border-radius: 8px; border: 1px solid var(--card-border); }
        .form-info p { margin: 0; display: flex; align-items: center; }
        .form-info i { width: 20px; margin-right: 0.5rem; text-align: center; }
        .field-note { display: block; font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.5rem; }

        /* Theme toggle button (Keep) */
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: var(--gradient-primary); color: white; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); z-index: 100; transition: all 0.3s ease; }
        .theme-toggle:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); }

        /* --- REMOVED MOBILE NAVBAR STYLES --- */

        /* Dark mode (Keep) */
        .dark-mode { /* Keep all dark mode variable overrides */
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

        /* NEW: Styles for the fixed top-left button (Copied from previous file) */
        .dashboard-button-top-left {
            position: fixed; /* Keep it fixed */
            top: 15px;       /* Adjust spacing from top */
            left: 15px;      /* Adjust spacing from left */
            z-index: 1010;  /* Ensure it's above theme toggle and content */
        }

        /* Animations (Keep) */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { opacity: 0; animation: fadeUp 0.6s ease forwards; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Media queries - Adjust for centered layout */
        @media (max-width: 1000px) {
            .main-container {
                 max-width: 95%; /* Use more width on slightly smaller screens */
            }
        }

        @media (max-width: 768px) {
             .page-header {
                 flex-direction: column;
                 align-items: flex-start;
             }
             /* Adjust body padding for smaller screens */
             body {
                 padding-top: 70px;
             }
        }

        @media (max-width: 576px) {
            .main-container { padding: 1rem; } /* Less padding */
            .card-body { padding: 1rem; }
            .profile-pic-preview { width: 120px; height: 120px; }
             /* Further adjust body padding */
             body {
                 padding-top: 65px;
             }
             /* Optionally make the top-left button smaller */
             .dashboard-button-top-left .back-link { /* Target the button *inside* the container */
                 padding: 0.5rem 1rem;
                 font-size: 0.85rem;
             }
        }
    </style>
</head>
<body>
    <!-- MOVED & MODIFIED: Back Button Container -->
    <div class="dashboard-button-top-left fade-in"> <!-- Applied new class -->
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link"> <!-- Kept back-link class for button styling -->
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>
    </div>

    <!-- Background elements (Keep) -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>

    <!-- Theme toggle button (Keep) -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="main-wrapper">
        <!-- --- SIDEBAR REMOVED --- -->

        <!-- Main content -->
        <div class="main-container">
            <!-- --- MOBILE NAVBAR REMOVED --- -->

            <!-- Original Back Button Container location REMOVED -->

            <!-- Page header -->
            <div class="page-header fade-in delay-1">
                 <div>
                     <h1><i class="fas fa-user-pen me-2"></i><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></h1>
                     <ul class="breadcrumb">
                         <li><a href="<?= htmlspecialchars($dashboardUrl) ?>">Dashboard</a></li>
                         <?php if (!$isSelfEdit && ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote')): // Only show User List link if appropriate ?>
                         <li><a href="../Controller/userController.php">User Management</a></li>
                         <?php endif; ?>
                         <li><?= $isSelfEdit ? 'My Profile' : 'Edit User' ?></li>
                     </ul>
                 </div>
                 <!-- REMOVED: Optional Logout Button -->
            </div>


            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in delay-2"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in delay-2"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Edit User Card -->
            <div class="card fade-in delay-3">
                <div class="card-header">
                    <h2>
                        <?php // Icon based on target user type
                            $iconClass = 'fa-solid fa-user-pen'; // Default
                            if ($targetUserType === 'student') $iconClass = 'fa-solid fa-user-graduate';
                            elseif ($targetUserType === 'pilote') $iconClass = 'fa-solid fa-user-tie';
                            elseif ($targetUserType === 'admin') $iconClass = 'fa-solid fa-user-shield';
                        ?>
                        <i class="<?= $iconClass ?>"></i>
                        Edit <?= $isSelfEdit ? 'My Profile' : ucfirst($targetUserType) ?>
                    </h2>
                </div>
                <div class="card-body">
                    <form method="post" action="" enctype="multipart/form-data" id="editUserForm">
                        <input type="hidden" name="action" value="update">

                        <!-- Profile Picture Section (No changes needed here) -->
                        <?php if ($isSelfEdit): ?>
                            <div class="profile-pic-container text-center">
                                <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-preview" id="picPreview">
                                <div class="file-upload-container">
                                    <label for="profile_pic" class="file-upload-label"><i class="fas fa-cloud-upload-alt"></i> Upload New Picture</label>
                                    <input type="file" id="profile_pic" name="profile_pic" accept=".jpg,.jpeg,.png,.svg,.webp" class="file-upload-input" onchange="previewFile()">
                                    <div class="file-name-display" id="fileName">No file selected</div>
                                </div>
                                <?php if ($profilePicSrc): ?>
                                <div class="remove-pic-container">
                                    <label class="remove-pic-label"><input type="checkbox" id="remove_profile_pic" name="remove_profile_pic" value="1" class="remove-pic-checkbox"> <span>Remove current picture</span></label>
                                </div>
                                <?php endif; ?>
                                <small class="text-muted d-block mt-2">Acceptable: JPG, PNG, SVG, WebP. Max: 2MB.</small>
                            </div>
                        <?php elseif ($profilePicSrc): // Admin/Pilote viewing existing picture ?>
                            <div class="profile-pic-container text-center">
                                <img src="<?= $profilePicSrc ?>" alt="Profile Picture" class="profile-pic-preview">
                                <small class="text-muted d-block mt-2">User's profile picture is only changeable by the user themselves.</small>
                            </div>
                        <?php else: // No picture and not self-edit ?>
                             <div class="profile-pic-container text-center">
                                 <img src="<?= $defaultPic ?>" alt="Default Profile Picture" class="profile-pic-preview">
                                 <small class="text-muted d-block mt-2">No profile picture uploaded. Only the user can add one.</small>
                            </div>
                        <?php endif; ?>


                        <!-- User form fields (No changes needed here) -->
                        <div class="row">
                            <!-- Name & Email -->
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name"><i class="fa-solid fa-user"></i> Name:</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" required <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                                    <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="email"><i class="fa-solid fa-envelope"></i> Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" required <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                                    <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <?php if ($isSelfEdit || $loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): // Only show password field if self-edit or admin/pilote ?>
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="password"><i class="fa-solid fa-key"></i> New Password:</label>
                                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                                    <?php if ($isSelfEdit): ?>
                                        <span id="password-strength" class="password-strength-indicator"></span>
                                        <span class="field-note">If changing: Min. 8 chars, 1 uppercase, 1 number.</span>
                                    <?php else: ?>
                                        <span class="field-note">Setting a password here will change the user's password.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php // Pilote/Student common fields ?>
                            <?php if ($targetUserType === 'pilote' || $targetUserType === 'student'): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                                        <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>" <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                                        <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>" <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                                        <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php // Student specific fields ?>
                            <?php if ($targetUserType === 'student'): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="dob"><i class="fa-solid fa-calendar-days"></i> Date of Birth:</label>
                                        <input type="date" class="form-control" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                                        <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="year"><i class="fa-solid fa-graduation-cap"></i> Year:</label>
                                        <select class="form-control" id="year" name="year" required <?= $disabledAttribute ?> <?= $readOnlyTitle ?>>
                                            <option value="" disabled <?= empty($userDetails['year']) ? 'selected' : ''?>>-- Select --</option>
                                            <?php $years=['1st', '2nd', '3rd', '4th', '5th']; foreach ($years as $y){ $sel=(($userDetails['year']??'')===$y) ? 'selected' : ''; echo "<option value=\"$y\" $sel>$y Year</option>"; } ?>
                                        </select>
                                        <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="school"><i class="fa-solid fa-school"></i> School:</label>
                                        <input type="text" class="form-control" id="school" name="school" value="<?= htmlspecialchars($userDetails['school'] ?? '') ?>" readonly title="School is set by Admin/Pilote.">
                                        <span class="field-note">School is managed by Admin/Pilote.</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea>
                                        <?php if($isStudentSelfEditRestricted): ?><span class="field-note">Cannot be changed.</span><?php endif; ?>
                                    </div>
                                </div>
                                <?php // Display Managed By info only if Admin/Pilote is viewing someone else's student profile
                                if (isset($userDetails['created_by_pilote_id']) && !$isSelfEdit && ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote')): ?>
                                    <div class="col-12">
                                        <div class="form-info">
                                            <p><i class="fas fa-user-tie"></i> Managed by Pilote ID: <?= htmlspecialchars($userDetails['created_by_pilote_id']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg"><i class="fa-solid fa-save"></i> Update <?= $isSelfEdit ? 'My Profile' : ucfirst($targetUserType) ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- End main-container -->
    </div> <!-- End main-wrapper -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Profile Picture Preview Script (Keep as is)
        function previewFile() {
            const preview = document.getElementById('picPreview');
            const fileInput = document.getElementById('profile_pic');
            const fileNameDisplay = document.getElementById('fileName');
            if (!preview || !fileInput || !fileInput.files || fileInput.files.length === 0) { if(fileNameDisplay) fileNameDisplay.textContent = "No file selected"; return; }
            const file = fileInput.files[0];
            if (fileNameDisplay) fileNameDisplay.textContent = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { preview.src = e.target.result; };
            reader.readAsDataURL(file);
            const removeCheckbox = document.getElementById('remove_profile_pic');
            if (removeCheckbox) removeCheckbox.checked = false;
        }

        // Password Strength Functions (Keep as is)
        function checkPasswordStrength(password) {
            let strength = 0; let requirements = []; const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password); const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[^A-Za-z0-9]/.test(password);
            if (password.length >= minLength) strength++; else requirements.push(`${minLength}+ chars`);
            if (hasUpperCase) strength++; else requirements.push("1 uppercase letter");
            if (hasNumber) strength++; else requirements.push("1 number");
            if (hasSymbol) strength++;
            if (password.length >= minLength && hasUpperCase && hasNumber) {
                if (hasSymbol && strength >= 4) return { level: 'strong', message: 'Password strength: Strong' };
                else return { level: 'medium', message: 'Password strength: Medium' };
            } else {
                let message = 'Weak. Requires: ' + requirements.join(', ');
                if (password.length === 0) message = '';
                else if (requirements.length === 0 && password.length < minLength) message = `Weak. Requires: ${minLength}+ characters`;
                else if (requirements.length === 0) message = 'Weak.'; // Simplified
                return { level: 'weak', message: message };
            }
        }
        function updateStrengthIndicator(fieldId, strengthData) {
            const indicator = document.getElementById(fieldId + '-strength');
            if (indicator) { indicator.textContent = strengthData.message; indicator.className = 'password-strength-indicator ' + strengthData.level; }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality (Keep as is)
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) { body.classList.add('dark-mode'); icon.classList.replace('fa-moon', 'fa-sun'); }
            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                if (body.classList.contains('dark-mode')) { icon.classList.replace('fa-moon', 'fa-sun'); localStorage.setItem('theme', 'dark'); }
                else { icon.classList.replace('fa-sun', 'fa-moon'); localStorage.setItem('theme', 'light'); }
            });

            // --- REMOVED SIDEBAR TOGGLE JS ---

            // Password Strength Listener (Keep as is)
            const editPasswordField = document.getElementById('password');
            const editUserForm = document.getElementById('editUserForm');
            const isSelfEditing = <?= $isSelfEdit ? 'true' : 'false' ?>;
            const passwordFieldExists = !!editPasswordField; // Check if the field actually exists in the DOM

             if (passwordFieldExists && editUserForm) {
                 // Activate indicator only for self-edit scenario where strength matters
                 if (isSelfEditing) {
                     editPasswordField.addEventListener('input', function() {
                         const password = this.value;
                         if (password.trim() !== '') {
                             const strengthData = checkPasswordStrength(password);
                             updateStrengthIndicator('password', strengthData);
                         } else {
                             updateStrengthIndicator('password', { level: '', message: '' }); // Clear indicator if blank
                         }
                     });
                 }

                 // Submit validation applies if password field is present and has value
                 editUserForm.addEventListener('submit', function(event) {
                     const password = editPasswordField.value;
                     if (password.trim() !== '') { // Only validate if new password entered
                         const strengthData = checkPasswordStrength(password);
                         if (strengthData.level === 'weak') {
                             event.preventDefault(); // Stop submission
                             alert('New password is too weak. Please meet the requirements: Minimum 8 characters, 1 uppercase letter, and 1 number, or leave blank to keep the current password.');
                             if (isSelfEditing) { // Show indicator only if self-editing
                                 updateStrengthIndicator('password', strengthData);
                             }
                             editPasswordField.focus(); // Focus the field
                         }
                     }
                 });
             }

        });
    </script>
</body>
</html>
