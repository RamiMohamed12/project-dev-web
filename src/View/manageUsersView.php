<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// Included by userController.php
// Assumes variables: $students, $pilotes, $admins, $loggedInUserRole, $loggedInUserId,
// $canManageAdmins, $canManagePilotes, $pageTitle, $errorMessage, $successMessage,
// $studentPagination, $pilotePagination, $adminPagination

// Prevent direct access & check necessary variables
if (!isset($loggedInUserRole) || !isset($loggedInUserId) || !isset($studentPagination)) {
     die("Direct access not permitted or required data missing.");
}

// Get itemsPerPage from pagination data (passed by controller)
$itemsPerPage = $studentPagination['itemsPerPage'] ?? 4;

// *** Helper function to prepare string data within an array for safe JSON encoding for JavaScript ***
function prepare_data_for_js($data_array, $fields_to_escape = []) {
    if (!is_array($data_array)) {
        return []; // Return empty if not an array
    }
    $processed_array = [];
    foreach ($data_array as $item) {
        if (!is_array($item)) continue; // Skip if item is not an array

        $processed_item = $item; // Copy the item
        foreach ($fields_to_escape as $field) {
            // Check if the field exists and is a string
            if (isset($processed_item[$field]) && is_string($processed_item[$field])) {
                // Replace various newline types with escaped newline for JS
                $processed_item[$field] = str_replace(["\r\n", "\r", "\n"], '\\n', $processed_item[$field]);
                // You could add other replacements here if needed, e.g., for problematic quotes
                // $processed_item[$field] = str_replace("'", "\\'", $processed_item[$field]);
                // $processed_item[$field] = str_replace('"', '\\"', $processed_item[$field]); // json_encode handles quotes well usually
            }
        }
        $processed_array[] = $processed_item;
    }
    return $processed_array;
}

// Prepare the initial data *before* json_encode using the helper function
// Specify fields that might contain problematic characters (especially newlines)
$students_js_safe = prepare_data_for_js($students, ['description', 'location', 'school', 'name', 'email']);
$pilotes_js_safe = prepare_data_for_js($pilotes, ['location', 'name', 'email']); // Adjust fields if pilotes have descriptions etc.
$admins_js_safe = prepare_data_for_js($admins, ['name', 'email']); // Adjust fields if admins have descriptions etc.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Ensure path is correct -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Styles from previous steps (password indicator, base styles) */
        /* Pagination Styles (should be in style.css) */
        .pagination-controls { margin-top: 15px; text-align: center; padding-bottom: 15px;}
        .pagination-controls button, .pagination-controls span { display: inline-block; padding: 5px 10px; margin: 0 2px; border: 1px solid #ccc; background-color: #fff; color: #007bff; cursor: pointer; border-radius: 3px; text-decoration: none; vertical-align: middle; }
        .pagination-controls button:disabled { color: #6c757d; cursor: not-allowed; background-color: #e9ecef; border-color: #dee2e6; }
        .pagination-controls span.current-page { font-weight: bold; background-color: #007bff; color: #fff; border-color: #007bff; cursor: default; }
        .pagination-controls button:hover:not(:disabled) { background-color: #e9ecef; }
        .pagination-controls .page-info { display: block; margin-top: 5px; font-size: 0.9em; color: #6c757d; }
        .pagination-controls span { vertical-align: middle; } /* Align ellipsis span */

        /* Password strength indicator styles (should be in style.css) */
        .password-strength-indicator { display: block; margin-top: 5px; font-size: 0.9em; height: 1.2em; font-weight: bold; }
        .password-strength-indicator.weak { color: #dc3545; }
        .password-strength-indicator.medium { color: #ffc107; }
        .password-strength-indicator.strong { color: #28a745; }

        /* Base Styles (should be in style.css) */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 1200px; margin: auto; background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 30px; margin-bottom: 20px; }
        h1:first-child, h2:first-child { margin-top: 0; }
        section { margin-bottom: 40px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: middle; word-wrap: break-word; }
        th { background-color: #e9ecef; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        /* Define column widths */
         th:nth-child(1), td:nth-child(1) { width: 5%; text-align: right; } /* ID */
         th:nth-child(2), td:nth-child(2) { width: 18%; } /* Name */
         th:nth-child(3), td:nth-child(3) { width: 22%; } /* Email */
         th:nth-child(4), td:nth-child(4) { width: 7%; } /* Year */
         th:nth-child(5), td:nth-child(5) { width: 15%; } /* School */
         th:nth-child(6), td:nth-child(6) { width: 13%; } /* Location */
         th:nth-child(7), td:nth-child(7) { width: 8%; } /* Created By */
         th.actions-col, td.actions-col { width: 12%; text-align: center;} /* Actions */
         /* Pilote/Admin tables will have different colspans */

        .actions a, .actions button { display: inline-block; padding: 5px 8px; margin: 2px 3px 2px 0; text-decoration: none; color: #fff; border-radius: 3px; border: none; cursor: pointer; font-size: 0.85em; vertical-align: middle; }
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
        .loading-indicator { text-align: center; padding: 20px; color: #6c757d; font-style: italic; }
        .table-error { text-align: center; padding: 15px; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; }

    </style>
    <script>
        // Keep toggleUserFields separate
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
            if (schoolInput) { /* schoolInput.required = (type === 'student'); // Optional */ }
             const locationInput = document.getElementById('add_location');
             const phoneInput = document.getElementById('add_phone');
             if (locationInput) locationInput.required = (type === 'pilote'); // Example
             if (phoneInput) phoneInput.required = (type === 'pilote'); // Example
        }
        // Note: Moved DOMContentLoaded listener to bottom script block
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
                <div class="form-group">
                    <label for="add_password">Password:</label>
                    <input type="password" id="add_password" name="password" required autocomplete="new-password">
                    <span id="add_password-strength" class="password-strength-indicator"></span>
                    <small>Min. 8 chars, 1 uppercase, 1 number for Medium strength.</small>
                </div>
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
                <button type="submit"><i class="fa-solid fa-plus"></i> Add User</button>
            </form>
        </div>

        <!-- Student List Section -->
        <section id="students-section">
            <h2><i class="fa-solid fa-user-graduate"></i> Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Year</th>
                        <th>School</th>
                        <th>Location</th>
                        <?php if ($loggedInUserRole === 'admin'): ?><th>Created By</th><?php endif; ?>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="students-tbody">
                    <?php // Populated by JS ?>
                </tbody>
            </table>
            <div id="students-pagination" class="pagination-controls"></div>
            <div id="students-loading" class="loading-indicator" style="display: none;">Loading students...</div>
            <div id="students-error" class="table-error" style="display: none;"></div>
        </section>

        <!-- Pilote List Section (Admin Only) -->
        <?php if ($canManagePilotes): ?>
        <section id="pilotes-section">
            <h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="pilotes-tbody">
                     <?php // Populated by JS ?>
                </tbody>
            </table>
            <div id="pilotes-pagination" class="pagination-controls"></div>
             <div id="pilotes-loading" class="loading-indicator" style="display: none;">Loading pilotes...</div>
             <div id="pilotes-error" class="table-error" style="display: none;"></div>
        </section>
        <?php endif; ?>

        <!-- Admin List Section (Admin Only) -->
         <?php if ($canManageAdmins): ?>
        <section id="admins-section">
            <h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody id="admins-tbody">
                     <?php // Populated by JS ?>
                </tbody>
            </table>
            <div id="admins-pagination" class="pagination-controls"></div>
            <div id="admins-loading" class="loading-indicator" style="display: none;">Loading admins...</div>
            <div id="admins-error" class="table-error" style="display: none;"></div>
        </section>
        <?php endif; ?>

    </div><!-- /.container -->

    <!-- ****** AJAX, Pagination, and Password Strength Script ****** -->
    <script>
        // --- Password Strength Functions ---
        function checkPasswordStrength(password) {
            let strength = 0; let requirements = []; const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password); const hasNumber = /[0-9]/.test(password);
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
                if (password.length === 0) message = ''; // Clear message if empty
                else if (requirements.length === 0 && password.length < minLength) message = `Weak. Requires: ${minLength}+ characters`;
                else if (requirements.length === 0) message = 'Weak. (Error checking)';
                return { level: 'weak', message: message };
            }
        }
        function updateStrengthIndicator(fieldId, strengthData) {
             const indicator = document.getElementById(fieldId + '-strength');
             if (indicator) { indicator.textContent = strengthData.message; indicator.className = 'password-strength-indicator ' + strengthData.level;}
        }
        // --- End Password Strength ---

        // --- AJAX Pagination Variables and Functions ---
        const loggedInUserRoleJS = '<?= $loggedInUserRole ?>';
        const loggedInUserIdJS = <?= $loggedInUserId ?>;
        const itemsPerPageJS = <?= $itemsPerPage ?>;

        // Function to fetch user data
        async function fetchUsersPage(userType, page = 1) {
            const tbody = document.getElementById(`${userType}-tbody`);
            const paginationDiv = document.getElementById(`${userType}-pagination`);
            const loadingDiv = document.getElementById(`${userType}-loading`);
            const errorDiv = document.getElementById(`${userType}-error`);

            if (!tbody || !paginationDiv || !loadingDiv || !errorDiv) { console.error(`Missing HTML elements for ${userType}`); return; }

            loadingDiv.style.display = 'block'; errorDiv.style.display = 'none';
            tbody.innerHTML = ''; paginationDiv.innerHTML = ''; // Clear previous

            try {
                const response = await fetch(`../Controller/ajax_get_users.php?type=${userType}&page=${page}`);
                if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
                const data = await response.json();
                if (data.error) { throw new Error(data.error); }

                if (data.success) {
                    // Process data *after* fetch for AJAX response as well
                    const safeUsers = processUserDataForJS(data.users, userType);
                    renderTableRows(tbody, userType, safeUsers);
                    renderPagination(paginationDiv, userType, data.pagination);
                } else { throw new Error('API response indicates failure.'); }
            } catch (error) {
                console.error(`Error fetching ${userType} page ${page}:`, error);
                errorDiv.textContent = `Error loading ${userType}: ${error.message}. Please try again.`;
                errorDiv.style.display = 'block';
                let colspan = calculateColspan(userType, loggedInUserRoleJS);
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="table-error">Could not load user data.</td></tr>`;
            } finally {
                loadingDiv.style.display = 'none';
            }
        }

        // Function to calculate colspan dynamically
        function calculateColspan(userType, role) {
            if (userType === 'students') return (role === 'admin') ? 8 : 7;
            if (userType === 'pilotes') return 5;
            if (userType === 'admins') return 4;
            return 5; // Default fallback
        }

        // Function to render table rows
        function renderTableRows(tbody, userType, users) {
            tbody.innerHTML = ''; // Clear previous content
            let colspan = calculateColspan(userType, loggedInUserRoleJS);

            if (!users || users.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${colspan}">No ${userType} found.</td></tr>`;
                return;
            }

            users.forEach(user => {
                // Basic check for essential data
                if (!user || typeof user.user_id === 'undefined' || typeof user.name === 'undefined' || typeof user.email === 'undefined') {
                     console.warn("Skipping user row due to missing essential data:", user);
                     return; // Skip this iteration
                }

                const tr = document.createElement('tr');
                let actionsHtml = '';
                let rowHtml = '';

                // Determine actions based on server-provided 'canModify' flag
                if (user.canModify) {
                    actionsHtml = `
                        <a href="editUser.php?id=${user.user_id}&type=${user.user_type}" class="edit-btn" title="Edit ${user.user_type}"><i class="fa-solid fa-pen-to-square"></i> Edit</a>`;
                    if (!(userType === 'admins' && loggedInUserRoleJS === 'admin' && user.user_id === loggedInUserIdJS)) {
                         actionsHtml += `
                            <form method="post" action="userController.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete ${user.user_type} ${escapeHtml(user.name)}? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="${user.user_type}">
                                <input type="hidden" name="id" value="${user.user_id}">
                                <button type="submit" class="delete-btn" title="Delete ${user.user_type}"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                         `;
                     } else if (userType === 'admins') {
                         actionsHtml += ` <span>(Self)</span>`;
                     }
                } else {
                    // Handle admin viewing self (can edit, not delete via list)
                    if (userType === 'admins' && loggedInUserRoleJS === 'admin' && user.user_id === loggedInUserIdJS) {
                         actionsHtml = `<a href="editUser.php?id=${user.user_id}&type=${user.user_type}" class="edit-btn" title="Edit Admin"><i class="fa-solid fa-pen-to-square"></i> Edit</a> <span>(Self)</span>`;
                    } else {
                        actionsHtml = '<span>(View Only)</span>';
                    }
                }

                 // Build row cells based on user type
                 if (userType === 'students') {
                     rowHtml = `
                         <td>${escapeHtml(user.id_student)}</td>
                         <td>${escapeHtml(user.name)}</td>
                         <td>${escapeHtml(user.email)}</td>
                         <td>${escapeHtml(user.year || 'N/A')}</td>
                         <td>${escapeHtml(user.school || 'N/A')}</td>
                         <td>${escapeHtml(user.location || 'N/A')}</td>
                         ${loggedInUserRoleJS === 'admin' ? `<td>${escapeHtml(user.created_by_pilote_id || 'Admin/Old')}</td>` : ''}
                         <td class="actions-col">${actionsHtml}</td>
                     `;
                 } else if (userType === 'pilotes') {
                     rowHtml = `
                         <td>${escapeHtml(user.id_pilote)}</td>
                         <td>${escapeHtml(user.name)}</td>
                         <td>${escapeHtml(user.email)}</td>
                         <td>${escapeHtml(user.location || 'N/A')}</td>
                         <td class="actions-col">${actionsHtml}</td>
                     `;
                 } else if (userType === 'admins') {
                     rowHtml = `
                         <td>${escapeHtml(user.id_admin)}</td>
                         <td>${escapeHtml(user.name)}</td>
                         <td>${escapeHtml(user.email)}</td>
                         <td class="actions-col">${actionsHtml}</td>
                     `;
                 }

                 tr.innerHTML = rowHtml;
                 tbody.appendChild(tr);
            });
        }

        // Function to render pagination controls
        function renderPagination(paginationDiv, userType, pagination) {
            paginationDiv.innerHTML = ''; // Clear previous controls
            const { currentPage, totalPages, totalUsers } = pagination;

            if (totalPages <= 0) { paginationDiv.innerHTML = `<span class="page-info">No users found.</span>`; return; }
            if (totalPages === 1) { paginationDiv.innerHTML = `<span class="page-info">Page 1 of 1 (${totalUsers} total)</span>`; return; }

            let paginationHtml = '';
             paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>« Prev</button>`;

            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            if(endPage === totalPages) { startPage = Math.max(1, endPage - maxPagesToShow + 1); }

            if (startPage > 1) {
                 paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', 1)">1</button>`;
                 if (startPage > 2) { paginationHtml += `<span>...</span>`; }
            }
            for (let i = startPage; i <= endPage; i++) {
                 if (i === currentPage) { paginationHtml += `<span class="current-page">${i}</span>`; }
                 else { paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${i})">${i}</button>`; }
            }
            if (endPage < totalPages) {
                  if (endPage < totalPages - 1) { paginationHtml += `<span>...</span>`; }
                 paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${totalPages})">${totalPages}</button>`;
            }

             paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>Next »</button>`;
            paginationHtml += `<span class="page-info">Page ${currentPage} of ${totalPages} (${totalUsers} total users)</span>`;
            paginationDiv.innerHTML = paginationHtml;
        }

        // Helper to escape HTML special characters
        function escapeHtml(unsafe) {
             if (unsafe === null || typeof unsafe === 'undefined') return '';
             const div = document.createElement('div');
             div.textContent = unsafe; // Let the browser handle escaping
             return div.innerHTML;
        }

        // *** JS Helper function to process data (mirroring PHP helper) ***
        function processUserDataForJS(data_array, userType) {
             if (!Array.isArray(data_array)) return [];

             // Define fields potentially containing newlines per user type
             const fieldsToEscapeMap = {
                 students: ['description', 'location', 'school', 'name', 'email'],
                 pilotes: ['location', 'name', 'email'],
                 admins: ['name', 'email']
             };
             const fields_to_escape = fieldsToEscapeMap[userType] || [];

             return data_array.map(item => {
                 if (typeof item !== 'object' || item === null) return item; // Skip non-objects
                 const processed_item = { ...item }; // Shallow copy
                 fields_to_escape.forEach(field => {
                     if (processed_item.hasOwnProperty(field) && typeof processed_item[field] === 'string') {
                         // Replace newlines with escaped newlines for JS strings
                         processed_item[field] = processed_item[field].replace(/\\r\\n|\\r|\\n/g, '\n').replace(/\r\n|\r|\n/g, '\\n');
                     }
                 });
                 return processed_item;
             });
         }


        // --- Initial Setup on DOM Load ---
        document.addEventListener('DOMContentLoaded', () => {
            // Toggle fields for Add User form
            toggleUserFields();

            // Attach listeners for Add User password strength
            const addPasswordField = document.getElementById('add_password');
            const addUserForm = document.getElementById('addUserForm');
            if (addPasswordField && addUserForm) {
                 addPasswordField.addEventListener('input', function() {
                     const strengthData = checkPasswordStrength(this.value);
                     updateStrengthIndicator('add_password', strengthData);
                 });
                 addUserForm.addEventListener('submit', function(event) {
                     const strengthData = checkPasswordStrength(addPasswordField.value);
                     if (strengthData.level === 'weak') {
                         event.preventDefault();
                         alert('Password is too weak. Please meet the requirements: Minimum 8 characters, 1 uppercase letter, and 1 number.');
                         updateStrengthIndicator('add_password', strengthData);
                         addPasswordField.focus();
                     }
                 });
             }

            // --- Initial data rendering using PHP variables (processed for JS safety) ---
            try {
                 // Use the PHP-processed variables directly
                 const initialStudents = <?= json_encode($students_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                 const initialStudentPagination = <?= json_encode($studentPagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                 console.log("Initial Students (JS Safe):", initialStudents);
                 renderTableRows(document.getElementById('students-tbody'), 'students', initialStudents);
                 renderPagination(document.getElementById('students-pagination'), 'students', initialStudentPagination);

                <?php if ($canManagePilotes): ?>
                    const initialPilotes = <?= json_encode($pilotes_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                    const initialPilotePagination = <?= json_encode($pilotePagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                     console.log("Initial Pilotes (JS Safe):", initialPilotes);
                    renderTableRows(document.getElementById('pilotes-tbody'), 'pilotes', initialPilotes);
                    renderPagination(document.getElementById('pilotes-pagination'), 'pilotes', initialPilotePagination);
                <?php endif; ?>

                <?php if ($canManageAdmins): ?>
                     const initialAdmins = <?= json_encode($admins_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                     const initialAdminPagination = <?= json_encode($adminPagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                      console.log("Initial Admins (JS Safe):", initialAdmins);
                     renderTableRows(document.getElementById('admins-tbody'), 'admins', initialAdmins);
                     renderPagination(document.getElementById('admins-pagination'), 'admins', initialAdminPagination);
                <?php endif; ?>
            } catch (e) {
                 console.error("Error during initial data rendering:", e);
                 const errorContainer = document.querySelector('.container');
                 if (errorContainer) {
                    const initialErrorDiv = document.createElement('div');
                    initialErrorDiv.className = 'message error-message';
                    initialErrorDiv.textContent = 'Error rendering initial user data. Please check console for details.';
                    errorContainer.insertBefore(initialErrorDiv, errorContainer.firstChild);
                 }
            }

        }); // End DOMContentLoaded

    </script>
    <!-- End Scripts -->

</body>
</html>