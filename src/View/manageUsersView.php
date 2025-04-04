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
    <!-- Path relative to the CONTROLLER URL, or use absolute path -->
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="manageUsersView.css">
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
            // if (schoolInput) schoolInput.required = (type === 'student'); // Optional: Make school required
        }
        document.addEventListener('DOMContentLoaded', toggleUserFields);
    </script>
</head>
<body>
    <div class="container">
        <!-- Corrected Back Link Path -->
        <a href="<?= ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php' ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <!-- Display Messages -->
        <?php if (!empty($errorMessage)): ?><div class="message error-message"><?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <!-- Add User Form -->
        <div class="form-container">
            <h2><i class="fa-solid fa-user-plus"></i> Add New User</h2>
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="addUserForm">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label for="add_user_type">User Type:</label><select id="add_user_type" name="type" required onchange="toggleUserFields()"><option value="" disabled selected>-- Select --</option><?php if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?><option value="student">Student</option><?php endif; ?><?php if ($canManagePilotes): ?><option value="pilote">Pilote</option><?php endif; ?><?php if ($canManageAdmins): ?><option value="admin">Admin</option><?php endif; ?></select></div>
                <div class="form-group"> <label for="add_name">Name:</label> <input type="text" id="add_name" name="name" required> </div>
                <div class="form-group"> <label for="add_email">Email:</label> <input type="email" id="add_email" name="email" required> </div>
                <div class="form-group"> <label for="add_password">Password:</label> <input type="password" id="add_password" name="password" required autocomplete="new-password"> </div>
                <div id="pilote_specific_fields" style="display: none;"><div class="form-group"> <label for="add_location">Location:</label> <input type="text" id="add_location" name="location"> </div><div class="form-group"> <label for="add_phone">Phone:</label> <input type="text" id="add_phone" name="phone"> </div></div>
                <div id="student_specific_fields" style="display: none;"><div class="form-group"> <label for="add_dob">Date of Birth:</label> <input type="date" id="add_dob" name="dob"> </div><div class="form-group"> <label for="add_year">Year:</label> <select id="add_year" name="year"><option value="" disabled selected>-- Select --</option><option value="1st">1st</option><option value="2nd">2nd</option><option value="3rd">3rd</option><option value="4th">4th</option><option value="5th">5th</option></select> </div><div class="form-group"> <label for="add_school">School:</label> <input type="text" id="add_school" name="school"> </div><div class="form-group"> <label for="add_description">Description:</label> <textarea id="add_description" name="description"></textarea> </div></div>
                <button type="submit"><i class="fa-solid fa-plus"></i> Add User</button>
            </form>
        </div>

        <!-- Student List Section -->
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

        <!-- Pilote List Section (Admin Only) -->
        <?php if ($canManagePilotes): ?>
        <section id="pilotes"><h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
             <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Location</th><th>Actions</th></tr></thead><tbody><?php if (!empty($pilotes)): foreach ($pilotes as $pilote): ?><tr><td><?= htmlspecialchars($pilote['id_pilote']) ?></td><td><?= htmlspecialchars($pilote['name']) ?></td><td><?= htmlspecialchars($pilote['email']) ?></td><td><?= htmlspecialchars($pilote['location'] ?? 'N/A') ?></td><td class="actions"><a href="editUser.php?id=<?= $pilote['id_pilote'] ?>&type=pilote" class="edit-btn" title="Edit Pilote"><i class="fa-solid fa-pen-to-square"></i> Edit</a><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('DELETE Pilote <?= htmlspecialchars(addslashes($pilote['name'])) ?>? Check DB constraints.');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="pilote"><input type="hidden" name="id" value="<?= $pilote['id_pilote'] ?>"><button type="submit" class="delete-btn" title="Delete Pilote"><i class="fa-solid fa-trash"></i> Delete</button></form></td></tr><?php endforeach; else: ?><tr><td colspan="5">No pilotes found.</td></tr><?php endif; ?></tbody></table>
        </section>
        <?php endif; ?>

        <!-- Admin List Section (Admin Only) -->
         <?php if ($canManageAdmins): ?>
        <section id="admins"><h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
            <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead><tbody><?php if (!empty($admins)): foreach ($admins as $admin): ?><tr><td><?= htmlspecialchars($admin['id_admin']) ?></td><td><?= htmlspecialchars($admin['name']) ?></td><td><?= htmlspecialchars($admin['email']) ?></td><td class="actions"><a href="editUser.php?id=<?= $admin['id_admin'] ?>&type=admin" class="edit-btn" title="Edit Admin"><i class="fa-solid fa-pen-to-square"></i> Edit</a><?php if ($admin['id_admin'] != $loggedInUserId): ?><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete admin <?= htmlspecialchars(addslashes($admin['name'])) ?>?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="admin"><input type="hidden" name="id" value="<?= $admin['id_admin'] ?>"><button type="submit" class="delete-btn" title="Delete Admin"><i class="fa-solid fa-trash"></i> Delete</button></form><?php else: ?><span>(Current User)</span><?php endif; ?></td></tr><?php endforeach; else: ?><tr><td colspan="4">No admins found.</td></tr><?php endif; ?></tbody></table>
        </section>
        <?php endif; ?>
    </div><!-- /.container -->
</body>
</html>
