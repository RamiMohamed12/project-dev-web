<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// Included by userController.php
// Assumes variables: $students, $pilotes, $admins, $loggedInUserRole, $loggedInUserId, $canManageAdmins, $canManagePilotes, $pageTitle, $errorMessage, $successMessage

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
    <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Ensure path is correct -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Add styles for password strength indicator (or move to style.css) */
        .password-strength-indicator {
            display: block;
            margin-top: 5px;
            font-size: 0.9em;
            height: 1.2em; /* Prevent layout jumps */
            font-weight: bold;
        }
        .password-strength-indicator.weak {
            color: #dc3545; /* Red */
        }
        .password-strength-indicator.medium {
            color: #ffc107; /* Orange */
        }
        .password-strength-indicator.strong {
            color: #28a745; /* Green */
        }

        /* Basic Styles for Manage Views (Inherit from style.css or define here) */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 1200px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 30px; margin-bottom: 20px; }
        h1:first-child, h2:first-child { margin-top: 0; }
        section { margin-bottom: 40px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        td { word-break: break-word; }
        .actions a, .actions button { display: inline-block; padding: 5px 10px; margin: 2px 5px 2px 0; text-decoration: none; color: #fff; border-radius: 3px; border: none; cursor: pointer; font-size: 0.9em; vertical-align: middle; }
        .actions .edit-btn { background-color: #ffc107; color: #333; }
        .actions .delete-btn { background-color: #dc3545; }
        .actions .delete-btn:hover { background-color: #c82333; }
        .actions .edit-btn:hover { background-color: #e0a800; }
        .actions span { font-style: italic; color: #6c757d; font-size: 0.9em; }
        .form-container { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #dee2e6; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"], .form-group input[type="date"], .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        button[type="submit"] { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button[type="submit"]:hover { background-color: #0056b3; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }
    </style>
    <!-- Keep existing toggleUserFields script -->
    <script>
        function toggleUserFields() {
            const typeElement = document.getElementById('add_user_type'); if (!typeElement) return;
            const type = typeElement.value;
            const studentFields = document.getElementById('student_specific_fields');
            const piloteCommonFields = document.getElementById('pilote_specific_fields');
            if (studentFields) studentFields.style.display = 'none';
            if (piloteCommonFields) piloteCommonFields.style.display = 'none';
            if (type === 'student' || type === 'pilote') { if (piloteCommonFields) piloteCommonFields.style.display = 'block'; }
            if (type === 'student') { if (studentFields) studentFields.style.display = 'block'; }
            const dobInput = document.getElementById('add_dob'); const yearSelect = document.getElementById('add_year'); const schoolInput = document.getElementById('add_school');
            if (dobInput) dobInput.required = (type === 'student');
            if (yearSelect) yearSelect.required = (type === 'student');
            // if (schoolInput) schoolInput.required = (type === 'student'); // Optional
        }
        // Note: Listener moved to end of body
    </script>
</head>
<body>
    <div class="container">
        <a href="<?= ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php' ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <!-- Add User Form -->
        <div class="form-container">
            <h2><i class="fa-solid fa-user-plus"></i> Add New User</h2>
            <!-- Added ID here -->
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="addUserForm">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="add_user_type">User Type:</label>
                    <select id="add_user_type" name="type" required onchange="toggleUserFields()">
                        <option value="" disabled selected>-- Select --</option>
                        <?php if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?><option value="student">Student</option><?php endif; ?>
                        <?php if ($canManagePilotes): ?><option value="pilote">Pilote</option><?php endif; ?>
                        <?php if ($canManageAdmins): ?><option value="admin">Admin</option><?php endif; ?>
                    </select>
                </div>
                <div class="form-group"> <label for="add_name">Name:</label> <input type="text" id="add_name" name="name" required> </div>
                <div class="form-group"> <label for="add_email">Email:</label> <input type="email" id="add_email" name="email" required> </div>
                <!-- Password Field with Strength Indicator -->
                <div class="form-group">
                    <label for="add_password">Password:</label>
                    <input type="password" id="add_password" name="password" required autocomplete="new-password">
                    <!-- Strength Indicator Span -->
                    <span id="add_password-strength" class="password-strength-indicator"></span>
                    <small>Min. 8 chars, 1 uppercase, 1 number for Medium strength.</small>
                </div>
                <!-- End Password Field -->

                <!-- Conditional Fields (existing logic) -->
                <div id="pilote_specific_fields" style="display: none;">
                    <div class="form-group"> <label for="add_location">Location:</label> <input type="text" id="add_location" name="location"> </div>
                    <div class="form-group"> <label for="add_phone">Phone:</label> <input type="text" id="add_phone" name="phone"> </div>
                </div>
                <div id="student_specific_fields" style="display: none;">
                    <div class="form-group"> <label for="add_dob">Date of Birth:</label> <input type="date" id="add_dob" name="dob"> </div>
                    <div class="form-group"> <label for="add_year">Year:</label> <select id="add_year" name="year"><option value="" disabled selected>-- Select --</option><option value="1st">1st</option><option value="2nd">2nd</option><option value="3rd">3rd</option><option value="4th">4th</option><option value="5th">5th</option></select> </div>
                    <div class="form-group"> <label for="add_school">School:</label> <input type="text" id="add_school" name="school"> </div>
                    <div class="form-group"> <label for="add_description">Description:</label> <textarea id="add_description" name="description"></textarea> </div>
                </div>
                <!-- End Conditional Fields -->

                <button type="submit"><i class="fa-solid fa-plus"></i> Add User</button>
            </form>
        </div>

        <!-- Student List Section (existing logic) -->
        <section id="students">
             <h2><i class="fa-solid fa-user-graduate"></i> Students</h2>
             <table>
                 <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Year</th><th>School</th><th>Location</th><?php if ($loggedInUserRole === 'admin'): ?><th>Created By</th><?php endif; ?><th>Actions</th></tr></thead>
                 <tbody>
                     <?php if (!empty($students)): ?>
                         <?php foreach ($students as $student):
                             $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($student['created_by_pilote_id']) && $student['created_by_pilote_id'] == $loggedInUserId));
                         ?>
                             <tr><td><?= htmlspecialchars($student['id_student']) ?></td><td><?= htmlspecialchars($student['name']) ?></td><td><?= htmlspecialchars($student['email']) ?></td><td><?= htmlspecialchars($student['year'] ?? 'N/A') ?></td><td><?= htmlspecialchars($student['school'] ?? 'N/A') ?></td><td><?= htmlspecialchars($student['location'] ?? 'N/A') ?></td><?php if ($loggedInUserRole === 'admin'): ?><td><?= htmlspecialchars($student['created_by_pilote_id'] ?? 'Admin/Old') ?></td><?php endif; ?><td class="actions"><?php if ($canModify): ?><a href="editUser.php?id=<?= $student['id_student'] ?>&type=student" class="edit-btn" title="Edit Student"><i class="fa-solid fa-pen-to-square"></i> Edit</a><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete student <?= htmlspecialchars(addslashes($student['name'])) ?>?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="student"><input type="hidden" name="id" value="<?= $student['id_student'] ?>"><button type="submit" class="delete-btn" title="Delete Student"><i class="fa-solid fa-trash"></i> Delete</button></form><?php else: ?><span>(View Only)</span><?php endif; ?></td></tr>
                         <?php endforeach; ?>
                     <?php else: ?>
                         <tr><td colspan="<?= ($loggedInUserRole === 'admin') ? 8 : 7 ?>">No students found<?= ($loggedInUserRole === 'pilote') ? ' assigned to you' : '' ?>.</td></tr>
                     <?php endif; ?>
                 </tbody>
             </table>
        </section>

        <!-- Pilote List Section (Admin Only - existing logic) -->
        <?php if ($canManagePilotes): ?>
        <section id="pilotes"><h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
             <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Location</th><th>Actions</th></tr></thead><tbody><?php if (!empty($pilotes)): foreach ($pilotes as $pilote): ?><tr><td><?= htmlspecialchars($pilote['id_pilote']) ?></td><td><?= htmlspecialchars($pilote['name']) ?></td><td><?= htmlspecialchars($pilote['email']) ?></td><td><?= htmlspecialchars($pilote['location'] ?? 'N/A') ?></td><td class="actions"><a href="editUser.php?id=<?= $pilote['id_pilote'] ?>&type=pilote" class="edit-btn" title="Edit Pilote"><i class="fa-solid fa-pen-to-square"></i> Edit</a><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('DELETE Pilote <?= htmlspecialchars(addslashes($pilote['name'])) ?>? Check DB constraints.');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="pilote"><input type="hidden" name="id" value="<?= $pilote['id_pilote'] ?>"><button type="submit" class="delete-btn" title="Delete Pilote"><i class="fa-solid fa-trash"></i> Delete</button></form></td></tr><?php endforeach; else: ?><tr><td colspan="5">No pilotes found.</td></tr><?php endif; ?></tbody></table>
        </section>
        <?php endif; ?>

        <!-- Admin List Section (Admin Only - existing logic) -->
         <?php if ($canManageAdmins): ?>
        <section id="admins"><h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
            <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead><tbody><?php if (!empty($admins)): foreach ($admins as $admin): ?><tr><td><?= htmlspecialchars($admin['id_admin']) ?></td><td><?= htmlspecialchars($admin['name']) ?></td><td><?= htmlspecialchars($admin['email']) ?></td><td class="actions"><a href="editUser.php?id=<?= $admin['id_admin'] ?>&type=admin" class="edit-btn" title="Edit Admin"><i class="fa-solid fa-pen-to-square"></i> Edit</a><?php if ($admin['id_admin'] != $loggedInUserId): ?><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete admin <?= htmlspecialchars(addslashes($admin['name'])) ?>?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="admin"><input type="hidden" name="id" value="<?= $admin['id_admin'] ?>"><button type="submit" class="delete-btn" title="Delete Admin"><i class="fa-solid fa-trash"></i> Delete</button></form><?php else: ?><span>(Current User)</span><?php endif; ?></td></tr><?php endforeach; else: ?><tr><td colspan="4">No admins found.</td></tr><?php endif; ?></tbody></table>
        </section>
        <?php endif; ?>

    </div><!-- /.container -->

    <!-- Password Strength Check Script -->
    <script>
        // Function to check password strength
        function checkPasswordStrength(password) {
            let strength = 0;
            let requirements = [];
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[^A-Za-z0-9]/.test(password); // Any non-alphanumeric

            if (password.length >= minLength) strength++; else requirements.push(`${minLength}+ characters`);
            if (hasUpperCase) strength++; else requirements.push("1 uppercase letter");
            if (hasNumber) strength++; else requirements.push("1 number");
            // Symbol adds to strong but isn't required for medium
            if (hasSymbol) strength++;

            // Determine level based on meeting base requirements
            if (password.length >= minLength && hasUpperCase && hasNumber) {
                if (hasSymbol && strength >= 4) { // Met medium + symbol
                     return { level: 'strong', message: 'Password strength: Strong' };
                } else { // Met medium
                     return { level: 'medium', message: 'Password strength: Medium' };
                }
            } else {
                // Didn't meet medium
                let message = 'Weak. Requires: ' + requirements.join(', ');
                if (password.length === 0) message = ''; // Clear message if empty
                else if (requirements.length === 0 && password.length < minLength) message = `Weak. Requires: ${minLength}+ characters`; // Only length missing
                else if (requirements.length === 0) message = 'Weak. (Error checking)'; // Fallback

                return { level: 'weak', message: message };
            }
        }

        // Function to update the UI indicator
        function updateStrengthIndicator(fieldId, strengthData) {
            const indicator = document.getElementById(fieldId + '-strength');
            if (indicator) {
                indicator.textContent = strengthData.message;
                indicator.className = 'password-strength-indicator ' + strengthData.level; // Set class
            }
        }

        // Attach listener to Add User password field
        const addPasswordField = document.getElementById('add_password');
        const addUserForm = document.getElementById('addUserForm');

        if (addPasswordField && addUserForm) {
            addPasswordField.addEventListener('input', function() {
                const password = this.value;
                const strengthData = checkPasswordStrength(password);
                updateStrengthIndicator('add_password', strengthData);
            });

            addUserForm.addEventListener('submit', function(event) {
                const password = addPasswordField.value;
                const strengthData = checkPasswordStrength(password);
                // **Block submission only if weak**
                if (strengthData.level === 'weak') {
                    event.preventDefault(); // Stop form submission
                    alert('Password is too weak. Please meet the requirements: Minimum 8 characters, 1 uppercase letter, and 1 number.');
                    // Ensure indicator shows weak state
                    updateStrengthIndicator('add_password', strengthData);
                    addPasswordField.focus(); // Focus back on the field
                }
                // Allow submission for medium or strong
            });
        }

         // Moved toggleUserFields listener here to ensure it runs after DOM is ready
         document.addEventListener('DOMContentLoaded', toggleUserFields);
    </script>
    <!-- End Password Script -->

</body>
</html>