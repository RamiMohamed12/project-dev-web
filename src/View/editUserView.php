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
$defaultPic = '../View/images/default_avatar.png'; // ** ADJUST PATH **

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Correct path -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style> /* ... Keep styles from previous version ... */ </style>
</head>
<body>
    <div class="container">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>
        <h2><?= htmlspecialchars($pageTitle) ?> <?= $isSelfEdit ? '(My Profile)' : '' ?></h2>

        <?php if (!empty($errorMessage)): ?><div class="message error-message"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">

            <!-- ***** MODIFICATION START: Conditional Profile Picture Section ***** -->
            <?php if ($isSelfEdit): ?>
                <div class="form-group">
                    <label><i class="fa-solid fa-image-portrait"></i> Current Profile Picture:</label>
                    <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-pic-preview" id="picPreview">
                     <label for="profile_pic">Upload New Picture (JPG, PNG, SVG, WebP - Max 2MB):</label>
                     <input type="file" id="profile_pic" name="profile_pic" accept=".jpg,.jpeg,.png,.svg,.webp" onchange="previewFile()">
                     <?php if ($profilePicSrc): // Show remove option only if picture exists ?>
                        <br> <!-- Add some spacing -->
                        <input type="checkbox" id="remove_profile_pic" name="remove_profile_pic" value="1" style="vertical-align: middle;">
                        <label for="remove_profile_pic" class="remove-pic-label" style="vertical-align: middle;">Remove current picture</label>
                     <?php endif; ?>
                </div>
            <?php else: // If admin/pilote editing someone else, just show current picture if available ?>
                 <?php if ($profilePicSrc): ?>
                     <div class="form-group">
                        <label><i class="fa-solid fa-image-portrait"></i> Current Profile Picture:</label>
                        <img src="<?= $profilePicSrc ?>" alt="Profile Picture" class="profile-pic-preview">
                        <small>(Only the user can change their profile picture)</small>
                    </div>
                 <?php endif; ?>
            <?php endif; ?>
            <!-- ***** MODIFICATION END ***** -->


            <div class="form-group"> <label for="name"><i class="fa-solid fa-user"></i> Name:</label> <input type="text" id="name" name="name" value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" required> </div>
            <div class="form-group"> <label for="email"><i class="fa-solid fa-envelope"></i> Email:</label> <input type="email" id="email" name="email" value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" required> </div>
            <div class="form-group"> <label for="password"><i class="fa-solid fa-key"></i> New Password:</label> <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password"> <small>Setting a new password logs the user out on next action elsewhere.</small> </div>

            <?php // Pilote/Student common fields ?>
            <?php if ($targetUserType === 'pilote' || $targetUserType === 'student'): ?>
                <div class="form-group"> <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label> <input type="text" id="location" name="location" value="<?= htmlspecialchars($userDetails['location'] ?? '') ?>"> </div>
                <div class="form-group"> <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label> <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($userDetails['phone_number'] ?? '') ?>"> </div>
            <?php endif; ?>

            <?php // Student specific fields ?>
            <?php if ($targetUserType === 'student'):
                 $isSchoolReadOnly = $isSelfEdit; // Student cannot edit school
            ?>
                 <div class="form-group"> <label for="dob"><i class="fa-solid fa-calendar-days"></i> Date of Birth:</label> <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($userDetails['date_of_birth'] ?? '') ?>" required> </div>
                 <div class="form-group"> <label for="year"><i class="fa-solid fa-graduation-cap"></i> Year:</label> <select id="year" name="year" required> <option value="" disabled <?= empty($userDetails['year']) ? 'selected' : ''?>>-- Select --</option> <?php $years=['1st', '2nd', '3rd', '4th', '5th']; foreach ($years as $y){ $sel=(($userDetails['year']??'')===$y)?'selected':''; echo "<option value=\"$y\" $sel>$y Year</option>"; } ?> </select> </div>
                 <div class="form-group"> <label for="school"><i class="fa-solid fa-school"></i> School:</label> <input type="text" id="school" name="school" value="<?= htmlspecialchars($userDetails['school'] ?? '') ?>" <?= $isSchoolReadOnly ? 'readonly title="Cannot be changed"' : '' ?>> <?php if ($isSchoolReadOnly): ?> <small>School is set by Admin/Pilote.</small> <?php endif; ?> </div>
                 <div class="form-group"> <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label> <textarea id="description" name="description" rows="4"><?= htmlspecialchars($userDetails['description'] ?? '') ?></textarea> </div>
                 <?php if (isset($userDetails['created_by_pilote_id']) && $loggedInUserRole === 'admin'): ?><p><small>Managed by Pilote ID: <?= htmlspecialchars($userDetails['created_by_pilote_id']) ?></small></p><?php endif; ?>
            <?php endif; ?>

            <button type="submit"><i class="fa-solid fa-save"></i> Update <?= $isSelfEdit ? 'My Profile' : ucfirst($targetUserType) ?></button>
        </form>
    </div>

    <script> /* Keep existing previewFile() JS */
        function previewFile() { const p = document.getElementById('picPreview'); const f = document.getElementById('profile_pic').files[0]; const r = new FileReader(); r.addEventListener("load", function(){ p.src=r.result; }, false); if(f){ r.readAsDataURL(f); } }
    </script>
</body>
</html>
