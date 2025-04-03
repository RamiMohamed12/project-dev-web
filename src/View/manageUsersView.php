<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// This file is included by userController.php
// Assumes the following variables are set by the controller:
// $students, $pilotes, $admins (arrays of users)
// $loggedInUserRole ('admin' or 'pilote')
// $loggedInUserId (int)
// $canManageAdmins (bool, true only if admin)
// $canManagePilotes (bool, true only if admin)
// $pageTitle (string)
// $errorMessage (string)
// $successMessage (string)

// Prevent direct access to this file
if (!isset($loggedInUserRole)) {
    // You might want to redirect or show a more specific error
    die("Direct access not permitted.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Adjust path relative to where CONTROLLER is, or use absolute path -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Basic Styles for Manage Views */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 1200px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 30px; margin-bottom: 20px; }
        h1:first-child, h2:first-child { margin-top: 0; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: middle; }
        th { background-color: #e9ecef; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .actions a, .actions button {
            display: inline-block; padding: 5px 10px; margin: 2px 5px 2px 0;
            text-decoration: none; color: #fff; border-radius: 3px; border: none; cursor: pointer; font-size: 0.9em; vertical-align: middle;
        }
        .actions .edit-btn { background-color: #ffc107; color: #333; }
        .actions .delete-btn { background-color: #dc3545; }
        .actions .delete-btn:hover { background-color: #c82333; }
        .actions .edit-btn:hover { background-color: #e0a800; }
        .actions span { font-style: italic; color: #6c757d; font-size: 0.9em; }
        .form-container { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #dee2e6; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold;}
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em;
        }
        button[type="submit"] { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        button[type="submit"]:hover { background-color: #0056b3; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link i { margin-right: 5px; }
    </style>
    <script>
        // Basic JS to show/hide fields based on selected type in Add form
        function toggleUserFields() {
            const typeElement = document.getElementById('add_user_type');
            if (!typeElement) return; // Exit if element doesn't exist
            const type = typeElement.value;

            const studentFields = document.getElementById('student_specific_fields');
            const piloteFields = document.getElementById('pilote_specific_fields'); // Contains location/phone

            // Hide all conditional blocks initially
            if (studentFields) studentFields.style.display = 'none';
            if (piloteFields) piloteFields.style.display = 'none';

            // Show relevant blocks
            if (type === 'student' || type === 'pilote') {
                if (piloteFields) piloteFields.style.display = 'block';
            }
            if (type === 'student') {
                 if (studentFields) studentFields.style.display = 'block';
            }

            // Make fields required client-side (server-side validation is crucial)
            const dobInput = document.getElementById('add_dob');
            const yearSelect = document.getElementById('add_year');
            if (dobInput) dobInput.required = (type === 'student');
            if (yearSelect) yearSelect.required = (type === 'student');

             // Optional: Make location/phone required for student/pilote if needed
            // const locationInput = document.getElementById('add_location');
            // const phoneInput = document.getElementById('add_phone');
            // if(locationInput) locationInput.required = (type === 'student' || type === 'pilote');
            // if(phoneInput) phoneInput.required = (type === 'student' || type === 'pilote');
        }

        // Call on page load in case of errors repopulating form or if form isn't present
        document.addEventListener('DOMContentLoaded', toggleUserFields);
    </script>
</head>
<body>
    <div class="container">
        <!-- ***** CORRECTED BACK LINK ***** -->
        <a href="<?= ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php' ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <!-- Add User Form -->
        <div class="form-container">
            <h2><i class="fa-solid fa-user-plus"></i> Add New User</h2>
            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="addUserForm">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="add_user_type">User Type:</label>
                    <select id="add_user_type" name="type" required onchange="toggleUserFields()">
                        <option value="" disabled selected>-- Select Type --</option>
                        <?php // Pilotes and Admins can add Students ?>
                        <?php if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?>
                            <option value="student">Student</option>
                        <?php endif; ?>
                        <?php // Only Admins can add Pilotes ?>
                        <?php if ($canManagePilotes): ?>
                            <option value="pilote">Pilote</option>
                        <?php endif; ?>
                         <?php // Only Admins can add Admins ?>
                         <?php if ($canManageAdmins): ?>
                            <option value="admin">Admin</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="add_name">Name:</label>
                    <input type="text" id="add_name" name="name" required>
                </div>
                 <div class="form-group">
                    <label for="add_email">Email:</label>
                    <input type="email" id="add_email" name="email" required>
                </div>
                 <div class="form-group">
                    <label for="add_password">Password:</label>
                    <input type="password" id="add_password" name="password" required autocomplete="new-password">
                </div>

                <!-- Common Fields for Pilote/Student -->
                 <div id="pilote_specific_fields" style="display: none;">
                    <div class="form-group">
                        <label for="add_location">Location:</label>
                        <input type="text" id="add_location" name="location">
                    </div>
                    <div class="form-group">
                        <label for="add_phone">Phone:</label>
                        <input type="text" id="add_phone" name="phone">
                    </div>
                </div>

                <!-- Student Specific Fields -->
                <div id="student_specific_fields" style="display: none;">
                     <div class="form-group">
                        <label for="add_dob">Date of Birth:</label>
                        <input type="date" id="add_dob" name="dob"> <!-- Required handled by JS/Server -->
                    </div>
                    <div class="form-group">
                        <label for="add_year">Year:</label>
                         <select id="add_year" name="year"> <!-- Required handled by JS/Server -->
                             <option value="" disabled selected>-- Select Year --</option>
                             <option value="1st">1st Year</option>
                             <option value="2nd">2nd Year</option>
                             <option value="3rd">3rd Year</option>
                             <option value="4th">4th Year</option>
                             <option value="5th">5th Year</option>
                         </select>
                    </div>
                    <div class="form-group">
                        <label for="add_description">Description:</label>
                        <textarea id="add_description" name="description"></textarea>
                    </div>
                </div>

                <button type="submit"><i class="fa-solid fa-plus"></i> Add User</button>
            </form>
        </div>


        <!-- Student List -->
        <section id="students">
            <h2><i class="fa-solid fa-user-graduate"></i> Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Year</th>
                        <th>Location</th>
                        <?php if ($loggedInUserRole === 'admin'): ?>
                            <th>Created By (Pilote ID)</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student):
                            $canModify = false;
                            if ($loggedInUserRole === 'admin') {
                                $canModify = true;
                            } elseif ($loggedInUserRole === 'pilote' && isset($student['created_by_pilote_id']) && $student['created_by_pilote_id'] == $loggedInUserId) {
                                $canModify = true;
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id_student']) ?></td>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['year'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($student['location'] ?? 'N/A') ?></td>
                                <?php if ($loggedInUserRole === 'admin'): ?>
                                    <td><?= htmlspecialchars($student['created_by_pilote_id'] ?? 'Admin/Old') ?></td>
                                <?php endif; ?>
                                <td class="actions">
                                    <?php if ($canModify): ?>
                                        <a href="editUser.php?id=<?= $student['id_student'] ?>&type=student" class="edit-btn" title="Edit Student"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete student <?= htmlspecialchars(addslashes($student['name'])) ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="type" value="student">
                                            <input type="hidden" name="id" value="<?= $student['id_student'] ?>">
                                            <button type="submit" class="delete-btn" title="Delete Student"><i class="fa-solid fa-trash"></i> Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span>(View Only)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 7 : 6 ?>">No students found<?= ($loggedInUserRole === 'pilote') ? ' assigned to you' : '' ?>.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Pilote List (Admin Only) -->
        <?php if ($canManagePilotes): // Only show if admin ?>
        <section id="pilotes">
            <h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
             <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pilotes)): ?>
                        <?php foreach ($pilotes as $pilote): ?>
                             <tr>
                                <td><?= htmlspecialchars($pilote['id_pilote']) ?></td>
                                <td><?= htmlspecialchars($pilote['name']) ?></td>
                                <td><?= htmlspecialchars($pilote['email']) ?></td>
                                <td><?= htmlspecialchars($pilote['location'] ?? 'N/A') ?></td>
                                <td class="actions">
                                    <a href="editUser.php?id=<?= $pilote['id_pilote'] ?>&type=pilote" class="edit-btn" title="Edit Pilote"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                     <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('DELETE Pilote <?= htmlspecialchars(addslashes($pilote['name'])) ?>?\nWARNING: This might orphan students/companies they created if not handled by DB constraints (ON DELETE SET NULL).');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="type" value="pilote">
                                        <input type="hidden" name="id" value="<?= $pilote['id_pilote'] ?>">
                                        <button type="submit" class="delete-btn" title="Delete Pilote"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No pilotes found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>


        <!-- Admin List (Admin Only) -->
         <?php if ($canManageAdmins): // Only show if admin ?>
        <section id="admins">
            <h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                     <?php if (!empty($admins)): ?>
                        <?php foreach ($admins as $admin): ?>
                             <tr>
                                <td><?= htmlspecialchars($admin['id_admin']) ?></td>
                                <td><?= htmlspecialchars($admin['name']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td class="actions">
                                    <a href="editUser.php?id=<?= $admin['id_admin'] ?>&type=admin" class="edit-btn" title="Edit Admin"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                     <?php if ($admin['id_admin'] != $loggedInUserId): // Prevent self-delete via button ?>
                                     <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete admin <?= htmlspecialchars(addslashes($admin['name'])) ?>?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="type" value="admin">
                                        <input type="hidden" name="id" value="<?= $admin['id_admin'] ?>">
                                        <button type="submit" class="delete-btn" title="Delete Admin"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                     <?php else: ?>
                                        <span>(Current User)</span>
                                     <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No admins found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

    </div>
</body>
</html>
