<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// Included by userController.php
// Assumes variables: $students, $pilotes, $admins, $loggedInUserRole, $loggedInUserId, $canManageAdmins, $canManagePilotes, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted.");
}

// Helper for profile pic
function generateProfilePicDataUri($mime, $data) { 
    if (!empty($mime) && !empty($data)) { 
        $picData = is_resource($data) ? stream_get_contents($data) : $data; 
        if ($picData) { 
            return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData); 
        } 
    } 
    return null; 
}

// Get the logged-in user's profile picture
$profilePicSrc = null;
if (isset($loggedInUserDetails)) {
    $profilePicSrc = generateProfilePicDataUri(
        $loggedInUserDetails['profile_picture_mime'] ?? null,
        $loggedInUserDetails['profile_picture'] ?? null
    );
}
$defaultPic = '../View/images/default_avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../View/js/darkMode.js"></script>
    <style>
        /* Additional styles specific to manage users */
        .container {
            max-width: 1200px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .user-table th,
        .user-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .user-table th {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        .actions a,
        .actions button {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px 5px;
            text-decoration: none;
            color: #fff;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            font-size: 0.9em;
            font-family: 'Poppins', sans-serif;
        }

        .edit-btn,
        .btn-edit {
            background-color: #ffc107;
            color: #333;
        }

        .delete-btn,
        .btn-delete {
            background-color: #dc3545;
            color: #fff;
        }

        section {
            margin-bottom: 40px;
        }

        section h2 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid transparent;
            font-family: 'Poppins', sans-serif;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
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
    <div class="sidebar">
        <h1>
            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="PFP" class="profile-pic-header">
            <span><?= htmlspecialchars($loggedInUserDetails['name'] ?? 'User') ?></span>
        </h1>
        <nav>
            <ul class="main-menu">
                <li><a href="../View/admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="../Controller/userController.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../Controller/companyController.php"><i class="fa-solid fa-building"></i> Manage Companies</a></li>
                <li><a href="../Controller/offerController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a></li>
                <?php if ($loggedInUserId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin"><i class="fa-solid fa-user-pen"></i> My Profile</a></li>
                <?php endif; ?>
            </ul>
            <ul class="bottom-menu">
                <li><a href="../Controller/logoutController.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <li>
                    <button id="theme-toggle" class="theme-switch">
                        <i class="fa-solid fa-sun sun-icon"></i>
                        <i class="fa-solid fa-moon moon-icon"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="header-flex">
                <h2><i class="fa-solid fa-users"></i> Manage Users</h2>
            </div>
            
            <!-- Display Messages -->
            <?php if (!empty($errorMessage)): ?>
                <div class="message error-message">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="message success-message">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Add User Form -->
            <div class="form-container">
                <h2><i class="fa-solid fa-user-plus"></i> Add New User</h2>
                <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="add_type"><i class="fa-solid fa-user-tag"></i> User Type:</label>
                        <select id="add_type" name="type" required onchange="toggleUserFields()">
                            <option value="" disabled selected>-- Select --</option>
                            <?php if ($canManagePilotes): ?>
                                <option value="pilote">Pilote</option>
                            <?php endif; ?>
                            <?php if ($canManageAdmins): ?>
                                <option value="admin">Admin</option>
                            <?php endif; ?>
                            <option value="student">Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_name"><i class="fa-solid fa-user"></i> Name:</label>
                        <input type="text" id="add_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="add_email"><i class="fa-solid fa-envelope"></i> Email:</label>
                        <input type="email" id="add_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="add_password"><i class="fa-solid fa-lock"></i> Password:</label>
                        <input type="password" id="add_password" name="password" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="add-user-btn">
                        <i class="fa-solid fa-user-plus"></i> Add User
                    </button>
                    <div id="pilote_specific_fields" style="display: none;">
                        <div class="form-group">
                            <label for="add_location"><i class="fa-solid fa-location-dot"></i> Location:</label>
                            <input type="text" id="add_location" name="location">
                        </div>
                        <div class="form-group">
                            <label for="add_phone"><i class="fa-solid fa-phone"></i> Phone:</label>
                            <input type="text" id="add_phone" name="phone">
                        </div>
                    </div>
                    <div id="student_specific_fields" style="display: none;">
                        <div class="form-group">
                            <label for="add_dob"><i class="fa-solid fa-calendar"></i> Date of Birth:</label>
                            <input type="date" id="add_dob" name="dob">
                        </div>
                        <div class="form-group">
                            <label for="add_year"><i class="fa-solid fa-graduation-cap"></i> Year:</label>
                            <select id="add_year" name="year">
                                <option value="" disabled selected>-- Select --</option>
                                <option value="1st">1st</option>
                                <option value="2nd">2nd</option>
                                <option value="3rd">3rd</option>
                                <option value="4th">4th</option>
                                <option value="5th">5th</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add_school"><i class="fa-solid fa-school"></i> School:</label>
                            <input type="text" id="add_school" name="school">
                        </div>
                        <div class="form-group">
                            <label for="add_description"><i class="fa-solid fa-file-lines"></i> Description:</label>
                            <textarea id="add_description" name="description"></textarea>
                        </div>
                    </div>
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
                                <tr>
                                    <td><?= htmlspecialchars($student['id_student']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td><?= htmlspecialchars($student['year'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($student['school'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($student['location'] ?? 'N/A') ?></td>
                                    <?php if ($loggedInUserRole === 'admin'): ?>
                                        <td><?= htmlspecialchars($student['created_by_pilote_id'] ?? 'Admin/Old') ?></td>
                                    <?php endif; ?>
                                    <td class="actions">
                                        <?php if ($canModify): ?>
                                            <a href="editUser.php?id=<?= $student['id_student'] ?>&type=student" class="edit-btn" title="Edit Student">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete student <?= htmlspecialchars(addslashes($student['name'])) ?>?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="type" value="student">
                                                <input type="hidden" name="id" value="<?= $student['id_student'] ?>">
                                                <button type="submit" class="delete-btn" title="Delete Student">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span>(View Only)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="<?= ($loggedInUserRole === 'admin') ? 8 : 7 ?>">No students found<?= ($loggedInUserRole === 'pilote') ? ' assigned to you' : '' ?>.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <!-- Pilote List Section (Admin Only) -->
            <?php if ($canManagePilotes): ?>
            <section id="pilotes">
                <h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Location</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pilotes)): foreach ($pilotes as $pilote): ?>
                            <tr>
                                <td><?= htmlspecialchars($pilote['id_pilote']) ?></td>
                                <td><?= htmlspecialchars($pilote['name']) ?></td>
                                <td><?= htmlspecialchars($pilote['email']) ?></td>
                                <td><?= htmlspecialchars($pilote['location'] ?? 'N/A') ?></td>
                                <td class="actions">
                                    <a href="editUser.php?id=<?= $pilote['id_pilote'] ?>&type=pilote" class="edit-btn" title="Edit Pilote">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('DELETE Pilote <?= htmlspecialchars(addslashes($pilote['name'])) ?>? Check DB constraints.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="type" value="pilote">
                                        <input type="hidden" name="id" value="<?= $pilote['id_pilote'] ?>">
                                        <button type="submit" class="delete-btn" title="Delete Pilote">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5">No pilotes found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php endif; ?>

            <!-- Admin List Section (Admin Only) -->
             <?php if ($canManageAdmins): ?>
            <section id="admins">
                <h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admins)): foreach ($admins as $admin): ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['id_admin']) ?></td>
                                <td><?= htmlspecialchars($admin['name']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td class="actions">
                                    <a href="editUser.php?id=<?= $admin['id_admin'] ?>&type=admin" class="edit-btn" title="Edit Admin">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <?php if ($admin['id_admin'] != $loggedInUserId): ?>
                                        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete admin <?= htmlspecialchars(addslashes($admin['name'])) ?>?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="type" value="admin">
                                            <input type="hidden" name="id" value="<?= $admin['id_admin'] ?>">
                                            <button type="submit" class="delete-btn" title="Delete Admin">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span>(Current User)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="4">No admins found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
            <?php endif; ?>
        </div><!-- /.container -->
    </div>
</body>
</html>
