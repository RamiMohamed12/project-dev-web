<?php
// Location: /home/demy/project-dev-web/src/View/editUserView.php
// Included by editUser.php controller
// Assumes variables: $userDetails, $targetUserType, $pageTitle, $errorMessage, $successMessage, $isSelfEdit, $loggedInUserRole

if (!isset($userDetails) || !isset($targetUserType) || !isset($isSelfEdit) || !isset($loggedInUserRole)) {
    die("Direct access not permitted or required variables missing.");
}

// Helper for profile picture
function generateProfilePicDataUri($mime, $data) {
    if (!empty($mime) && !empty($data)) {
        $picData = is_resource($data) ? stream_get_contents($data) : $data;
        if ($picData) {
            return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData);
        }
    }
    return null;
}

// Generate profile picture source
$profilePicSrc = generateProfilePicDataUri($userDetails['profile_picture_mime'] ?? null, $userDetails['profile_picture'] ?? null);
$defaultPic = '../View/images/default_avatar.png'; // Adjust path as needed

// Function to validate password strength
function validatePasswordStrength($password) {
    $minLength = 8;
    $hasUpperCase = preg_match('/[A-Z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);
    $hasSpecialChar = preg_match('/[^A-Za-z0-9]/', $password);

    if (strlen($password) < $minLength || !$hasUpperCase || !$hasNumber || !$hasSpecialChar) {
        return false;
    }
    return true;
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Modern Profile Page Styling */
        .main-content {
            padding: 140px 30px 30px;
            margin-left: 250px;
            min-height: calc(100vh - 70px);
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2C3E50;
            margin: 0 0 30px 0;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1;
            position: relative;
        }

        .profile-section {
            display: flex !important;
            align-items: center !important;
            gap: 30px !important;
            margin-bottom: 40px !important;
            padding: 20px !important;
        }

        .profile-pic-preview {
            width: 120px !important;
            height: 120px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 3px solid #4E73DF !important;
            flex-shrink: 0 !important;  /* Prevent image from shrinking */
        }

        .profile-info {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
        }

        .profile-info h2 {
            margin: 0 0 10px 0 !important;
            font-size: 24px !important;
            color: #2C3E50 !important;
            font-weight: 600 !important;
        }

        .profile-info p {
            margin: 5px 0 !important;
            color: #6B7280 !important;
            font-size: 14px !important;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #2C3E50;
            margin-bottom: 8px;
        }

        .form-group label i {
            color: #6f94f6;
            width: 20px;
            font-size: 16px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            border: 1px solid #E5E9F2;
            border-radius: 12px;
            background-color: #F8F9FA;
            color: #2C3E50;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: #6f94f6;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(111, 148, 246, 0.1);
        }

        .form-group input[type="text"]::placeholder,
        .form-group input[type="email"]::placeholder,
        .form-group input[type="password"]::placeholder {
            color: #A0AEC0;
        }

        .form-group small {
            display: block;
            color: #6B7280;
            font-size: 12px;
            margin-top: 6px;
        }

        .message {
            margin-bottom: 25px;
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message {
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .error-message {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* Modern File Upload Styling */
        .file-upload-wrapper {
            margin-top: 20px !important;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #F8F9FA;
            border: 2px dashed #E5E9F2;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background-color: #EFF6FF;
            border-color: #6f94f6;
        }

        .file-upload-label i {
            font-size: 24px;
            color: #6f94f6;
            margin-bottom: 8px;
        }

        .upload-text {
            font-size: 16px;
            font-weight: 500;
            color: #2C3E50;
            margin-bottom: 4px;
        }

        .selected-file {
            font-size: 14px;
            color: #6B7280;
        }

        .file-formats {
            display: block;
            text-align: center;
            color: #6B7280;
            font-size: 12px;
            margin-top: 8px;
        }

        /* Modern Checkbox Styling */
        .checkbox-wrapper {
            margin-top: 15px;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
            position: relative;
            padding-left: 35px;
            cursor: pointer;
            font-size: 14px;
            user-select: none;
            color: #DC3545;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            left: 0;
            height: 22px;
            width: 22px;
            background-color: #F8F9FA;
            border: 2px solid #DC3545;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .custom-checkbox:hover input ~ .checkmark {
            background-color: #FEE2E2;
        }

        .custom-checkbox input:checked ~ .checkmark {
            background-color: #DC3545;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .custom-checkbox .checkmark:after {
            left: 7px;
            top: 3px;
            width: 4px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .checkbox-text {
            margin-left: 5px;
        }

        /* Button styling to match the image exactly */
        button[type="submit"],
        .btn-primary {
            background-color: #3B82F6 !important;  /* Bright blue color */
            color: white !important;
            border: none !important;
            border-radius: 25px !important;
            padding: 12px 24px !important;
            font-size: 16px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.3s ease !important;
            box-shadow: none !important;
            height: 45px !important;
        }

        /* Icon in button */
        button[type="submit"] i,
        .btn-primary i {
            font-size: 20px !important;
            margin-right: 4px !important;
        }

        /* Hover effect */
        button[type="submit"]:hover,
        .btn-primary:hover {
            background-color: #2563EB !important;  /* Slightly darker blue on hover */
            transform: translateY(-1px) !important;
        }

        /* Active/Click state */
        button[type="submit"]:active,
        .btn-primary:active {
            transform: translateY(0) !important;
        }

        /* Disabled State */
        .form-group input[readonly],
        .form-group input[disabled] {
            background-color: #F1F5F9;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Navbar Icons Styling */
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

        /* Form field styling with blue focus border */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100% !important;
            height: 50px !important;
            padding: 12px 20px !important;
            border: 2px solid transparent !important;  /* Transparent border by default */
            border-radius: 30px !important;
            background-color: #FFF5E6 !important;
            font-size: 16px !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            color: #333 !important;
            transition: all 0.3s ease !important;
            box-sizing: border-box !important;
            box-shadow: none !important;
            margin-top: 5px !important;
        }

        /* Label styling */
        .form-group label {
            display: block !important;
            font-size: 16px !important;
            font-weight: 500 !important;
            color: #333 !important;
            margin-bottom: 8px !important;
            margin-left: 5px !important;
        }

        .form-group {
            margin-bottom: 20px !important;
        }

        /* Focus state with blue border */
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none !important;
            border-color: #3B82F6 !important;  /* Same blue as the button */
            background-color: #FFF5E6 !important;
            box-shadow: none !important;
        }

        /* Select element specific styling */
        .form-group select {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 15px center !important;
            background-size: 15px !important;
            padding-right: 40px !important;
        }

        /* Textarea specific styling */
        .form-group textarea {
            height: auto !important;
            min-height: 120px !important;
            border-radius: 20px !important;
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
                <a href="../Controller/companyController.php">
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
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="active">
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

    <!-- Main Content -->
    <div class="main-content">
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message">
                <i class="fa-solid fa-check-circle"></i> 
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message">
                <i class="fa-solid fa-circle-exclamation"></i> 
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <h1 class="page-title">
            <i class="fa-solid fa-user-pen"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </h1>

        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">

            <div class="profile-section">
                <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-preview" id="picPreview">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($userDetails['name'] ?? '') ?></h2>
                    <p><strong>Email:</strong> <?= htmlspecialchars($userDetails['email'] ?? '') ?></p>
                    <p><strong>Role:</strong> <?= ucfirst($targetUserType) ?></p>
                </div>
            </div>

            <?php if ($isSelfEdit): ?>
                <div class="form-group">
                    <div class="file-upload-wrapper">
                        <input type="file" id="profile_pic" name="profile_pic" accept=".jpg,.jpeg,.png,.svg,.webp" onchange="previewFile()" class="file-upload-input">
                        <label for="profile_pic" class="file-upload-label">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span class="upload-text">Choose a file</span>
                            <span class="selected-file" id="selected-filename">No file selected</span>
                        </label>
                        <small class="file-formats">Accepted formats: JPG, PNG, SVG, WebP (Max 2MB)</small>
                </div>
                    
                 <?php if ($profilePicSrc): ?>
                        <div class="checkbox-wrapper">
                            <label class="custom-checkbox">
                                <input type="checkbox" id="remove_profile_pic" name="remove_profile_pic" value="1">
                                <span class="checkmark"></span>
                                <span class="checkbox-text">Remove current picture</span>
                            </label>
                    </div>
                 <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="name">
                    <i class="fa-solid fa-user"></i>
                    Name:
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fa-solid fa-envelope"></i>
                    Email:
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" 
                       required>
            </div>

            <?php if ($isSelfEdit): ?>
                <!-- Password Field with Strength Indicator - Only relevant for self-edit -->
                <div class="form-group">
                    <label for="password">
                        <i class="fa-solid fa-key"></i> New Password:
                    </label>
                    <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                    <span id="password-strength" class="password-strength-indicator"></span>
                    <small>Leave blank to keep current. If changing: Min. 8 chars, 1 uppercase, 1 number.</small>
                </div>
            <?php elseif ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?>
                <!-- Password Field for Admin/Pilote editing someone else -->
                <div class="form-group">
                    <label for="password">
                        <i class="fa-solid fa-key"></i> New Password (Optional):
                    </label>
                    <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                    <small>Setting a password here will change the user's password.</small>
                </div>
            <?php endif; ?>

            <?php if ($targetUserType === 'pilote' || $targetUserType === 'student'): ?>
                <div class="form-group">
                    <label for="location">
                        <i class="fa-solid fa-location-dot"></i>
                        Location:
                    </label>
                    <input type="text" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="phone">
                        <i class="fa-solid fa-phone"></i>
                        Phone:
                    </label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>">
                </div>
            <?php endif; ?>

            <?php if ($targetUserType === 'student'):
                 $isSchoolReadOnly = $isSelfEdit; // Student cannot edit school
            ?>
                 <div class="form-group">
                    <label for="dob">
                        <i class="fa-solid fa-calendar-days"></i>
                        Date of Birth:
                    </label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required>
                </div>
                 <div class="form-group">
                    <label for="year">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Year:
                    </label>
                    <select id="year" name="year" required>
                        <option value="" disabled <?= empty($userDetails['year']) ? 'selected' : ''?>>-- Select --</option>
                        <?php 
                        $years = ['1st', '2nd', '3rd', '4th', '5th'];
                        foreach ($years as $y) {
                            $sel = (($userDetails['year'] ?? '') === $y) ? 'selected' : '';
                            echo "<option value=\"$y\" $sel>$y Year</option>";
                        }
                        ?>
                    </select>
                </div>
                 <div class="form-group">
                    <label for="school">
                        <i class="fa-solid fa-school"></i>
                        School:
                    </label>
                    <input type="text" id="school" name="school" 
                           value="<?= htmlspecialchars($userDetails['school'] ?? '') ?>"
                           <?= $isSchoolReadOnly ? 'readonly title="Cannot be changed"' : '' ?>>
                    <?php if ($isSchoolReadOnly): ?>
                        <small>School is set by Admin/Pilote</small>
                    <?php endif; ?>
                </div>
                 <div class="form-group">
                    <label for="description">
                        <i class="fa-solid fa-align-left"></i>
                        Description:
                    </label>
                    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea>
                </div>
                 <?php if (isset($userDetails['created_by_pilote_id']) && $loggedInUserRole === 'admin'): ?><p><small>Managed by Pilote ID: <?= htmlspecialchars($userDetails['created_by_pilote_id']) ?></small></p><?php endif; ?>
            <?php endif; ?>

            <button type="submit">
                <i class="fa-solid fa-save"></i>
                Update <?= $isSelfEdit ? 'My Profile' : ucfirst($targetUserType) ?>
            </button>
        </form>
    </div>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>

    <script>
        // Profile Picture Preview Script
        function previewFile() {
            const preview = document.getElementById('picPreview');
            const fileInput = document.getElementById('profile_pic');
            const file = fileInput.files[0];
            const filenameElement = document.getElementById('selected-filename');

            // Update filename display
            if (file) {
                filenameElement.textContent = file.name;
            } else {
                filenameElement.textContent = 'No file selected';
            }

            // Preview image
            const reader = new FileReader();
            reader.addEventListener("load", function () {
                preview.src = reader.result;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // Password Strength Check Script
        function checkPasswordStrength(password) {
            let strength = 0;
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[^A-Za-z0-9]/.test(password);

            if (password.length >= minLength) strength++;
            if (hasUpperCase) strength++;
            if (hasNumber) strength++;
            if (hasSpecialChar) strength++;

            if (strength === 4) {
                return { level: 'strong', message: 'Password strength: Strong' };
            } else if (strength === 3) {
                return { level: 'medium', message: 'Password strength: Medium' };
            } else {
                return { level: 'weak', message: 'Password strength: Weak' };
            }
        }

        function updateStrengthIndicator(fieldId, strengthData) {
            const indicator = document.getElementById(fieldId + '-strength');
            if (indicator) {
                indicator.textContent = strengthData.message;
                indicator.className = 'password-strength-indicator ' + strengthData.level;
            }
        }

        // Attach listener to password field
        const passwordField = document.getElementById('password');
        if (passwordField) {
            passwordField.addEventListener('input', function () {
                const password = this.value;
                const strengthData = checkPasswordStrength(password);
                updateStrengthIndicator('password', strengthData);
            });
        }
    </script>
</body>
</html>
