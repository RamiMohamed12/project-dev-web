<?php
// Location: /home/demy/project-dev-web/src/View/editUserView.php
// Included by editUser.php controller
// Assumes variables: $userDetails, $targetUserType, $pageTitle, $errorMessage, $successMessage, $isSelfEdit, $loggedInUserRole

if (!isset($userDetails) || !isset($targetUserType) || !isset($isSelfEdit) || !isset($loggedInUserRole)) { die("Direct access not permitted or required variables missing."); }

// Determine Back link
$backUrl = 'userController.php'; $backText = 'Back to User List';
if ($isSelfEdit) {
    $dashboardFile = '';
    if ($loggedInUserRole === 'admin') $dashboardFile = 'admin.php';
    elseif ($loggedInUserRole === 'pilote') $dashboardFile = 'pilote.php';
    elseif ($loggedInUserRole === 'student') $dashboardFile = 'student.php';
    if ($dashboardFile) { $backUrl = '../View/' . $dashboardFile; $backText = 'Back to My Dashboard'; }
}

// Helper for profile pic
function generateProfilePicDataUri($mime, $data) { if (!empty($mime) && !empty($data)) { $picData = is_resource($data) ? stream_get_contents($data) : $data; if ($picData) { return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData); } } return null; }
$profilePicSrc = generateProfilePicDataUri($userDetails['profile_picture_mime'] ?? null, $userDetails['profile_picture'] ?? null);
$defaultPic = '../View/images/default_avatar.png'; // ** Ensure this path is correct relative to the View folder **

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
    <title><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Ensure path is correct -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Password strength indicator styles (should be in style.css) */
        .password-strength-indicator { display: block; margin-top: 5px; font-size: 0.9em; height: 1.2em; font-weight: bold; }
        .password-strength-indicator.weak { color: #dc3545; }
        .password-strength-indicator.medium { color: #ffc107; }
        .password-strength-indicator.strong { color: #28a745; }

        /* Styles for readonly/disabled fields (should be in style.css) */
        input[readonly], textarea[readonly], select[disabled] {
            background-color: #e9ecef; /* Light grey background */
            cursor: not-allowed; /* Indicate non-interactive */
            opacity: 0.7; /* Slightly faded */
        }
        /* Optional: Add a specific class if more control is needed */
        .field-restricted {
             background-color: #e9ecef;
             cursor: not-allowed;
             opacity: 0.7;
        }

         /* Profile pic preview styles (should be in style.css) */
         .profile-pic-preview { display: block; max-width: 150px; max-height: 150px; border: 1px solid #ccc; margin-top: 5px; margin-bottom: 10px; object-fit: cover; }
         .remove-pic-label { font-weight: normal; font-size: 0.9em; }

        /* Base styles (should ideally be in style.css) */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 800px; margin: auto; background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 0; margin-bottom: 25px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555;}
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"], .form-group input[type="date"], .form-group select, .form-group textarea, .form-group input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        .form-group input:focus:not([readonly]), .form-group select:focus:not([disabled]), .form-group textarea:focus:not([readonly]) { border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25); }
        .form-group textarea { min-height: 120px; }
        .form-group small { font-size: 0.85em; color: #6c757d; display: block; margin-top: 5px; }
        button[type="submit"] { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; }
        button[type="submit"]:hover { background-color: #218838; }
        button[type="submit"] i { margin-right: 8px; }
        .back-link { display: inline-block; margin-bottom: 25px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>
        <h2><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></h2>

        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data" id="editUserForm">
            <input type="hidden" name="action" value="update">

            <!-- Profile Picture Section - Only interactive for self-edit -->
            <?php if ($isSelfEdit): ?>
                <div class="form-group">
                    <label><i class="fa-solid fa-image-portrait"></i> Current Profile Picture:</label>
                    <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-preview" id="picPreview">
                     <label for="profile_pic">Upload New Picture (JPG, PNG, SVG, WebP - Max 2MB):</label>
                     <input type="file" id="profile_pic" name="profile_pic" accept=".jpg,.jpeg,.png,.svg,.webp" onchange="previewFile()">
                     <?php if ($profilePicSrc): ?>
                        <br>
                        <input type="checkbox" id="remove_profile_pic" name="remove_profile_pic" value="1" style="vertical-align: middle;">
                        <label for="remove_profile_pic" class="remove-pic-label" style="vertical-align: middle;">Remove current picture</label>
                     <?php endif; ?>
                </div>
            <?php elseif ($profilePicSrc): // Admin/Pilote viewing existing picture ?>
                 <div class="form-group">
                    <label><i class="fa-solid fa-image-portrait"></i> Current Profile Picture:</label>
                    <img src="<?= $profilePicSrc ?>" alt="Profile Picture" class="profile-pic-preview">
                </div>
            <?php endif; ?>
            <!-- End Profile Picture Section -->

            <!-- Name & Email - Readonly for student self-edit -->
            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" required <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
            </div>
            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" required <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                 <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
            </div>

            <!-- Password Field with Strength Indicator - Only relevant for self-edit -->
            <?php if ($isSelfEdit): ?>
            <div class="form-group">
                 <label for="password"><i class="fa-solid fa-key"></i> New Password:</label>
                 <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                 <span id="password-strength" class="password-strength-indicator"></span>
                 <small>Leave blank to keep current. If changing: Min. 8 chars, 1 uppercase, 1 number.</small>
             </div>
            <?php elseif($canEdit): // Admin/Pilote editing someone else ?>
                 <div class="form-group">
                     <label for="password"><i class="fa-solid fa-key"></i> New Password (Optional):</label>
                     <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                     <small>Setting a password here will change the user's password.</small>
                     <!-- No strength indicator shown to admin/pilote editing others -->
                 </div>
            <?php endif; ?>
            <!-- End Password Field -->

            <?php // Pilote/Student common fields - Readonly for student self-edit ?>
            <?php if ($targetUserType === 'pilote' || $targetUserType === 'student'): ?>
                <div class="form-group">
                    <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                    <input type="text" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>" <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                    <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>" <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                    <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
                </div>
            <?php endif; ?>

            <?php // Student specific fields - Readonly/disabled for student self-edit ?>
            <?php if ($targetUserType === 'student'): ?>
                 <div class="form-group">
                    <label for="dob"><i class="fa-solid fa-calendar-days"></i> Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>>
                    <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
                 </div>
                 <div class="form-group">
                    <label for="year"><i class="fa-solid fa-graduation-cap"></i> Year:</label>
                    <select id="year" name="year" required <?= $disabledAttribute ?> <?= $readOnlyTitle /* Use same title */ ?>>
                        <option value="" disabled <?= empty($userDetails['year']) ? 'selected' : ''?>>-- Select --</option>
                        <?php $years=['1st', '2nd', '3rd', '4th', '5th']; foreach ($years as $y){ $sel=(($userDetails['year']??'')===$y)?'selected':''; echo "<option value=\"$y\" $sel>$y Year</option>"; } ?>
                    </select>
                     <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
                 </div>
                 <div class="form-group">
                    <label for="school"><i class="fa-solid fa-school"></i> School:</label>
                     <!-- School is ALWAYS readonly for student, even if admin/pilote is editing them -->
                    <input type="text" id="school" name="school" value="<?= htmlspecialchars($userDetails['school'] ?? '') ?>" readonly title="School is set by Admin/Pilote.">
                    <small>School is set by Admin/Pilote.</small>
                 </div>
                 <div class="form-group">
                    <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label>
                    <textarea id="description" name="description" rows="4" <?= $readOnlyAttribute ?> <?= $readOnlyTitle ?>><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea>
                    <?php if($isStudentSelfEditRestricted): ?><small>Cannot be changed.</small><?php endif; ?>
                 </div>
                 <?php // Display Managed By info only if Admin is viewing
                 if (isset($userDetails['created_by_pilote_id']) && $loggedInUserRole === 'admin'): ?>
                    <p><small>Managed by Pilote ID: <?= htmlspecialchars($userDetails['created_by_pilote_id']) ?></small></p>
                 <?php endif; ?>
            <?php endif; ?>

            <button type="submit"><i class="fa-solid fa-save"></i> Update <?= $isSelfEdit ? 'My Profile' : ucfirst($targetUserType) ?></button>
        </form>
    </div>

    <!-- Profile Picture Preview Script (existing) -->
    <script>
        function previewFile() { const p = document.getElementById('picPreview'); const f = document.getElementById('profile_pic').files[0]; const r = new FileReader(); r.addEventListener("load", function(){ p.src=r.result; }, false); if(f){ r.readAsDataURL(f); } }
    </script>

    <!-- Password Strength Check Script -->
     <script>
         // Function to check password strength
         function checkPasswordStrength(password) {
            let strength = 0;
            let requirements = [];
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[^A-Za-z0-9]/.test(password);
            if (password.length >= minLength) strength++; else requirements.push(`${minLength}+ characters`);
            if (hasUpperCase) strength++; else requirements.push("1 uppercase letter");
            if (hasNumber) strength++; else requirements.push("1 number");
            if (hasSymbol) strength++;
            if (password.length >= minLength && hasUpperCase && hasNumber) {
                if (hasSymbol && strength >= 4) return { level: 'strong', message: 'Password strength: Strong' };
                else return { level: 'medium', message: 'Password strength: Medium' };
            } else {
                let message = 'Weak. Requires: ' + requirements.join(', ');
                if (password.length === 0) message = ''; // Clear if empty
                else if (requirements.length === 0 && password.length < minLength) message = `Weak. Requires: ${minLength}+ characters`;
                else if (requirements.length === 0) message = 'Weak. (Error checking)';
                return { level: 'weak', message: message };
            }
         }

         // Function to update the UI indicator
         function updateStrengthIndicator(fieldId, strengthData) {
             const indicator = document.getElementById(fieldId + '-strength');
             if (indicator) {
                 indicator.textContent = strengthData.message;
                 indicator.className = 'password-strength-indicator ' + strengthData.level;
             }
         }

         // Attach listener to Edit User password field (Only if it exists and is for self-edit)
         const editPasswordField = document.getElementById('password');
         const editUserForm = document.getElementById('editUserForm');
         const isSelfEditing = <?= $isSelfEdit ? 'true' : 'false' ?>; // Pass PHP flag to JS
         const canEditPassword = <?= ($isSelfEdit || $loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote') ? 'true' : 'false' ?>; // Check if password field should even exist/be active

         if (canEditPassword && editPasswordField && editUserForm) {
             // Activate indicator only for self-edit scenario where strength matters
             if (isSelfEditing) {
                 editPasswordField.addEventListener('input', function() {
                     const password = this.value;
                     if (password.trim() !== '') {
                         const strengthData = checkPasswordStrength(password);
                         updateStrengthIndicator('password', strengthData);
                     } else {
                         updateStrengthIndicator('password', { level: '', message: '' });
                     }
                 });
             }

             // Submit validation applies if password field is present
             editUserForm.addEventListener('submit', function(event) {
                 const password = editPasswordField.value;
                 if (password.trim() !== '') { // Only validate if new password entered
                     const strengthData = checkPasswordStrength(password);
                     if (strengthData.level === 'weak') {
                         event.preventDefault();
                         alert('New password is too weak. Please meet the requirements: Minimum 8 characters, 1 uppercase letter, and 1 number, or leave blank to keep the current password.');
                         if (isSelfEditing) { // Show indicator only if self-editing
                             updateStrengthIndicator('password', strengthData);
                         }
                         editPasswordField.focus();
                     }
                 }
             });
         }
     </script>
     <!-- End Password Script -->
</body>
</html>