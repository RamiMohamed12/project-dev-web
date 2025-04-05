<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// Included by userController.php
// Assumes variables: $students, $pilotes, $admins, $loggedInUserRole, $loggedInUserId, $canManageAdmins, $canManagePilotes, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted.");
}

// Helper function to prepare data for JavaScript
function prepare_data_for_js($data_array, $fields_to_escape = []) {
    if (!is_array($data_array)) {
        return [];
    }
    $processed_array = [];
    foreach ($data_array as $item) {
        if (!is_array($item)) continue;

        $processed_item = $item;
        foreach ($fields_to_escape as $field) {
            if (isset($processed_item[$field]) && is_string($processed_item[$field])) {
                $processed_item[$field] = str_replace(["\r\n", "\r", "\n"], '\\n', $processed_item[$field]);
            }
        }
        $processed_array[] = $processed_item;
    }
    return $processed_array;
}

// Prepare data for JavaScript
$students_js_safe = prepare_data_for_js($students, ['description', 'location', 'school', 'name', 'email']);
$pilotes_js_safe = prepare_data_for_js($pilotes, ['location', 'name', 'email']);
$admins_js_safe = prepare_data_for_js($admins, ['name', 'email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Just update the main background color */
        body {
            background-color: #F0F2F5 !important;
        }

        .main-content {
            background-color: #F0F2F5 !important;
        }

        /* Override and force the new styles */
        .main-content {
            margin-left: 250px;
            padding: 140px 30px 30px 30px;
        }

        /* Remove all containers */
        .container, .form-container {
            all: unset !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* Form styling */
        form {
            margin-top: 30px;
            margin-bottom: 50px;
            width: 100%;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin-bottom: 12px !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        .form-group label i {
            color: #6B7280;
            font-size: 16px;
        }

        /* Input fields styling */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100% !important;
            padding: 15px 20px !important;
            border: 2px solid #E5E7EB !important;
            border-radius: 25px !important;
            background-color: #fff5e6 !important;
            font-size: 15px !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            color: #374151 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            box-sizing: border-box !important;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none !important;
            border-color: #3B82F6 !important;
            background-color: #fff !important;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1) !important;
        }

        /* Select specific styling */
        .form-group select {
            appearance: none !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 15px center !important;
            background-size: 15px !important;
            padding-right: 45px !important;
        }

        /* Textarea specific styling */
        .form-group textarea {
            min-height: 120px !important;
            resize: vertical !important;
        }

        /* Submit button styling */
        button[type="submit"] {
            background-color: #3B82F6 !important;
            color: white !important;
            padding: 12px 25px !important;
            border: none !important;
            border-radius: 25px !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.3s ease !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        button[type="submit"]:hover {
            background-color: #2563EB !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.1) !important;
        }

        /* Page title styling */
        .page-title {
            font-size: 32px;
            font-weight: 600;
            color: #2C3E50;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 15px;
            border: none;
        }

        /* Circle plus icon */
        .circle-plus-icon {
            width: 45px;
            height: 45px;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
        }

        .circle-plus-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid #4B9FFF;
            border-radius: 50%;
            box-sizing: border-box;
        }

        .circle-plus-icon::after {
            content: '+';
            color: #4B9FFF;
            font-size: 35px;
            font-weight: bold;
            line-height: 1;
            margin-top: -2px;
            margin-left: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Add this to your style section in manageUsersView.php */
        section h2 {
            font-size: 28px !important;
            font-weight: 600 !important;
            color: #2C3E50 !important;
            margin: 40px 0 25px !important;
            padding: 0 !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            position: relative !important;
        }

        section h2 i {
            font-size: 24px !important;
            color: #4B9FFF !important;
            background: linear-gradient(135deg, #4B9FFF 0%, #2C7BE5 100%) !important;
            -webkit-background-clip: text !important; /* For WebKit-based browsers */
            background-clip: text !important; /* Standard property for compatibility */
            -webkit-text-fill-color: transparent !important;
            padding: 8px !important;
            border-radius: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        section h2::after {
            content: '' !important;
            position: absolute !important;
            bottom: -8px !important;
            left: 0 !important;
            width: 50px !important;
            height: 4px !important;
            background: linear-gradient(135deg, #4B9FFF 0%, #2C7BE5 100%) !important;
            border-radius: 2px !important;
        }

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
    </style>
    <script src="../View/script.js"></script>
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

        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            const div = document.createElement('div');
            div.textContent = unsafe;
            return div.innerHTML;
        }

        // Function to render table rows
        function renderTableRows(tbody, userType, users) {
            tbody.innerHTML = '';
            let colspan = calculateColspan(userType, '<?= $loggedInUserRole ?>');

            if (!users || users.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${colspan}">No ${userType} found.</td></tr>`;
                return;
            }

            users.forEach(user => {
                const tr = document.createElement('tr');
                let rowHtml = '';

                if (userType === 'students') {
                    rowHtml = `
                        <td>${escapeHtml(user.id_student)}</td>
                        <td>${escapeHtml(user.name)}</td>
                        <td>${escapeHtml(user.email)}</td>
                        <td>${escapeHtml(user.year || 'N/A')}</td>
                        <td>${escapeHtml(user.school || 'N/A')}</td>
                        <td>${escapeHtml(user.location || 'N/A')}</td>
                        <?php if ($loggedInUserRole === 'admin'): ?>
                        <td>${escapeHtml(user.created_by_pilote_id || 'Admin/Old')}</td>
                        <?php endif; ?>
                        <td class="actions-col">Actions</td>
                    `;
                }
                // Add similar logic for pilotes and admins
                tr.innerHTML = rowHtml;
                tbody.appendChild(tr);
            });
        }

        // Function to render pagination controls
        function renderPagination(paginationDiv, userType, pagination) {
            paginationDiv.innerHTML = '';
            const { currentPage, totalPages } = pagination;

            if (totalPages <= 1) return;

            let paginationHtml = '';
            paginationHtml += `<button onclick="fetchUsersPage('${userType}', ${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>« Prev</button>`;

            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `<button onclick="fetchUsersPage('${userType}', ${i})" ${i === currentPage ? 'class="current-page"' : ''}>${i}</button>`;
            }

            paginationHtml += `<button onclick="fetchUsersPage('${userType}', ${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>Next »</button>`;
            paginationDiv.innerHTML = paginationHtml;
        }

        // Function to fetch user data via AJAX
        async function fetchUsersPage(userType, page = 1) {
            const tbody = document.getElementById(`${userType}-tbody`);
            const paginationDiv = document.getElementById(`${userType}-pagination`);

            tbody.innerHTML = '<tr><td colspan="8">Loading...</td></tr>';
            paginationDiv.innerHTML = '';

            try {
                const response = await fetch(`../Controller/ajax_get_users.php?type=${userType}&page=${page}`);
                const data = await response.json();

                if (data.success) {
                    renderTableRows(tbody, userType, data.users);
                    renderPagination(paginationDiv, userType, data.pagination);
                } else {
                    tbody.innerHTML = `<tr><td colspan="8">Error loading data.</td></tr>`;
                }
            } catch (error) {
                console.error('Error fetching user data:', error);
                tbody.innerHTML = `<tr><td colspan="8">Error loading data.</td></tr>`;
            }
        }

        // Initial data rendering
        document.addEventListener('DOMContentLoaded', () => {
            const initialStudents = <?= json_encode($students_js_safe) ?>;
            const initialPilotes = <?= json_encode($pilotes_js_safe) ?>;
            const initialAdmins = <?= json_encode($admins_js_safe) ?>;

            renderTableRows(document.getElementById('students-tbody'), 'students', initialStudents);
            renderTableRows(document.getElementById('pilotes-tbody'), 'pilotes', initialPilotes);
            renderTableRows(document.getElementById('admins-tbody'), 'admins', initialAdmins);
        });
    </script>
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
                <a href="../Controller/userController.php" class="active">
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
                <a href="../Controller/userController.php" class="active">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Students</span>
                </a>
                <a href="../Controller/internshipController.php">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Manage Offers</span>
                </a>
            <?php endif; ?>
            
            <?php if ($loggedInUserId): ?>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>">
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
        <!-- Messages -->
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
            <span class="circle-plus-icon"></span>
            Add New User
        </h1>

            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="addUserForm">
                <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>
                    <i class="fa-solid fa-user-tag"></i>
                    User Type:
                </label>
                <select id="add_user_type" name="type" required onchange="toggleUserFields()">
                    <option value="" disabled selected>-- Select --</option>
                    <?php if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?>
                        <option value="student">Student</option>
                    <?php endif; ?>
                    <?php if ($canManagePilotes): ?>
                        <option value="pilote">Pilote</option>
                    <?php endif; ?>
                    <?php if ($canManageAdmins): ?>
                        <option value="admin">Admin</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-user"></i>
                    Name:
                </label>
                <input type="text" id="add_name" name="name" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-envelope"></i>
                    Email:
                </label>
                <input type="email" id="add_email" name="email" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-lock"></i>
                    Password:
                </label>
                <input type="password" id="add_password" name="password" required>
            </div>

            <div id="pilote_specific_fields" style="display: none;">
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-location-dot"></i>
                        Location:
                    </label>
                    <input type="text" id="add_location" name="location">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-phone"></i>
                        Phone:
                    </label>
                    <input type="text" id="add_phone" name="phone">
                </div>
            </div>

            <div id="student_specific_fields" style="display: none;">
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-calendar-days"></i>
                        Date of Birth:
                    </label>
                    <input type="date" id="add_dob" name="dob">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-graduation-cap"></i>
                        Year:
                    </label>
                    <select id="add_year" name="year">
                        <option value="" disabled selected>-- Select --</option>
                        <option value="1st">1st Year</option>
                        <option value="2nd">2nd Year</option>
                        <option value="3rd">3rd Year</option>
                        <option value="4th">4th Year</option>
                        <option value="5th">5th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-school"></i>
                        School:
                    </label>
                    <input type="text" id="add_school" name="school">
                </div>
                <div class="form-group">
                    <label>
                        <i class="fa-solid fa-align-left"></i>
                        Description:
                    </label>
                    <textarea id="add_description" name="description" rows="4"></textarea>
                </div>
        </div>

            <button type="submit">
                <i class="fa-solid fa-plus"></i>
                Add User
            </button>
        </form>

        <!-- Student List Section -->
        <section id="students">
            <h2>
                <i class="fa-solid fa-user-graduate"></i>
                Students
            </h2>
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Year</th><th>School</th><th>Location</th><?php if ($loggedInUserRole === 'admin'): ?><th>Created By</th><?php endif; ?><th>Actions</th></tr></thead>
                <tbody id="students-tbody">
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
            <div id="students-pagination"></div>
        </section>

        <!-- Pilote List Section (Admin Only) -->
        <?php if ($canManagePilotes): ?>
        <section id="pilotes"><h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
             <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Location</th><th>Actions</th></tr></thead><tbody id="pilotes-tbody"><?php if (!empty($pilotes)): foreach ($pilotes as $pilote): ?><tr><td><?= htmlspecialchars($pilote['id_pilote']) ?></td><td><?= htmlspecialchars($pilote['name']) ?></td><td><?= htmlspecialchars($pilote['email']) ?></td><td><?= htmlspecialchars($pilote['location'] ?? 'N/A') ?></td><td class="actions"><a href="editUser.php?id=<?= $pilote['id_pilote'] ?>&type=pilote" class="edit-btn" title="Edit Pilote"><i class="fa-solid fa-pen-to-square"></i> Edit</a><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('DELETE Pilote <?= htmlspecialchars(addslashes($pilote['name'])) ?>? Check DB constraints.');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="pilote"><input type="hidden" name="id" value="<?= $pilote['id_pilote'] ?>"><button type="submit" class="delete-btn" title="Delete Pilote"><i class="fa-solid fa-trash"></i> Delete</button></form></td></tr><?php endforeach; else: ?><tr><td colspan="5">No pilotes found.</td></tr><?php endif; ?></tbody></table>
            <div id="pilotes-pagination"></div>
        </section>
        <?php endif; ?>

        <!-- Admin List Section (Admin Only) -->
         <?php if ($canManageAdmins): ?>
        <section id="admins"><h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
            <table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead><tbody id="admins-tbody"><?php if (!empty($admins)): foreach ($admins as $admin): ?><tr><td><?= htmlspecialchars($admin['id_admin']) ?></td><td><?= htmlspecialchars($admin['name']) ?></td><td><?= htmlspecialchars($admin['email']) ?></td><td class="actions"><a href="editUser.php?id=<?= $admin['id_admin'] ?>&type=admin" class="edit-btn" title="Edit Admin"><i class="fa-solid fa-pen-to-square"></i> Edit</a><?php if ($admin['id_admin'] != $loggedInUserId): ?><form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete admin <?= htmlspecialchars(addslashes($admin['name'])) ?>?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="type" value="admin"><input type="hidden" name="id" value="<?= $admin['id_admin'] ?>"><button type="submit" class="delete-btn" title="Delete Admin"><i class="fa-solid fa-trash"></i> Delete</button></form><?php else: ?><span>(Current User)</span><?php endif; ?></td></tr><?php endforeach; else: ?><tr><td colspan="4">No admins found.</td></tr><?php endif; ?></tbody></table>
            <div id="admins-pagination"></div>
        </section>
        <?php endif; ?>
    </div>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
