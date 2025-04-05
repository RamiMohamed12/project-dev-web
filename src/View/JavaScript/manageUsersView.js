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
            const listContainer = document.getElementById(`${userType}-list`);
            const paginationDiv = document.getElementById(`${userType}-pagination`);
            const loadingDiv = document.getElementById(`${userType}-loading`);
            const errorDiv = document.getElementById(`${userType}-error`);

            if (!listContainer || !paginationDiv || !loadingDiv || !errorDiv) { 
                console.error(`Missing HTML elements for ${userType}`); 
                return; 
            }

            loadingDiv.style.display = 'block'; 
            errorDiv.style.display = 'none';
            listContainer.innerHTML = ''; 
            paginationDiv.innerHTML = ''; // Clear previous

            try {
                const response = await fetch(`../Controller/ajax_get_users.php?type=${userType}&page=${page}`);
                if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
                const data = await response.json();
                if (data.error) { throw new Error(data.error); }

                if (data.success) {
                    // Process data *after* fetch for AJAX response as well
                    const safeUsers = processUserDataForJS(data.users, userType);
                    renderUserCards(listContainer, userType, safeUsers);
                    renderPagination(paginationDiv, userType, data.pagination);
                } else { throw new Error('API response indicates failure.'); }
            } catch (error) {
                console.error(`Error fetching ${userType} page ${page}:`, error);
                errorDiv.textContent = `Error loading ${userType}: ${error.message}. Please try again.`;
                errorDiv.style.display = 'block';
                listContainer.innerHTML = `<div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Could not load user data</h3>
                    <p>Please try again or contact support.</p>
                </div>`;
            } finally {
                loadingDiv.style.display = 'none';
            }
        }

        // Function to render user cards
        function renderUserCards(container, userType, users) {
            container.innerHTML = ''; // Clear previous content

            if (!users || users.length === 0) {
                container.innerHTML = `<div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No ${userType} found</h3>
                    <p>Add your first ${userType.substring(0, userType.length-1)} using the form above.</p>
                </div>`;
                return;
            }

            users.forEach(user => {
                // Basic check for essential data
                if (!user || typeof user.user_id === 'undefined' || typeof user.name === 'undefined' || typeof user.email === 'undefined') {
                    console.warn("Skipping user card due to missing essential data:", user);
                    return; // Skip this iteration
                }

                let actionsHtml = '';
                let detailsHtml = '';
                const initials = getInitials(user.name);
                const userId = userType === 'students' ? user.id_student : (userType === 'pilotes' ? user.id_pilote : user.id_admin);

                // Determine actions based on server-provided 'canModify' flag
                if (user.canModify) {
                    actionsHtml = `
                        <a href="editUser.php?id=${user.user_id}&type=${user.user_type}" class="btn btn-warning btn-sm">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>`;
                    if (!(userType === 'admins' && loggedInUserRoleJS === 'admin' && user.user_id === loggedInUserIdJS)) {
                        actionsHtml += `
                            <form method="post" action="userController.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete ${user.user_type} ${escapeHtml(user.name)}? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="${user.user_type}">
                                <input type="hidden" name="id" value="${user.user_id}">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>`;
                    } else if (userType === 'admins') {
                        actionsHtml += `<span class="user-id"><i class="fas fa-info-circle"></i>Current user</span>`;
                    }
                } else {
                    // Handle admin viewing self (can edit, not delete via list)
                    if (userType === 'admins' && loggedInUserRoleJS === 'admin' && user.user_id === loggedInUserIdJS) {
                        actionsHtml = `
                            <a href="editUser.php?id=${user.user_id}&type=${user.user_type}" class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <span class="user-id"><i class="fas fa-info-circle"></i>Current user</span>`;
                    } else {
                        actionsHtml = '<span class="user-id"><i class="fas fa-eye"></i>View only</span>';
                    }
                }

                // Build detail fields based on user type
                if (userType === 'students') {
                    detailsHtml = `
                        <div class="user-detail">
                            <i class="fas fa-graduation-cap"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Year</div>
                                <div class="user-detail-value">${escapeHtml(user.year || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-school"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">School</div>
                                <div class="user-detail-value">${escapeHtml(user.school || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Location</div>
                                <div class="user-detail-value">${escapeHtml(user.location || 'N/A')}</div>
                            </div>
                        </div>
                        ${loggedInUserRoleJS === 'admin' ? `
                        <div class="user-detail">
                            <i class="fas fa-user-plus"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Created By</div>
                                <div class="user-detail-value">${escapeHtml(user.created_by_pilote_id || 'Admin/Old')}</div>
                            </div>
                        </div>` : ''}
                    `;
                } else if (userType === 'pilotes') {
                    detailsHtml = `
                        <div class="user-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Location</div>
                                <div class="user-detail-value">${escapeHtml(user.location || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-phone"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Phone</div>
                                <div class="user-detail-value">${escapeHtml(user.phone || 'N/A')}</div>
                            </div>
                        </div>
                    `;
                } else if (userType === 'admins') {
                    detailsHtml = `
                        <div class="user-detail">
                            <i class="fas fa-shield-alt"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Role</div>
                                <div class="user-detail-value">Administrator</div>
                            </div>
                        </div>
                    `;
                }

                // Create user card
                const userCard = document.createElement('div');
                userCard.className = 'user-card';
                userCard.innerHTML = `
                    <div class="user-card-header">
                        <div class="user-avatar">${initials}</div>
                        <div class="user-info">
                            <h3>${escapeHtml(user.name)}</h3>
                            <p>${escapeHtml(user.email)}</p>
                        </div>
                    </div>
                    <div class="user-card-body">
                        ${detailsHtml}
                    </div>
                    <div class="user-card-footer">
                        <div class="user-id"><i class="fas fa-id-card"></i> ID: ${escapeHtml(userId)}</div>
                        <div class="user-actions">${actionsHtml}</div>
                    </div>
                `;
                container.appendChild(userCard);
            });
        }

        // Function to get initials from name
        function getInitials(name) {
            if (!name || typeof name !== 'string') return '?';
            const nameParts = name.trim().split(' ');
            if (nameParts.length === 1) return nameParts[0].charAt(0).toUpperCase();
            return (nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)).toUpperCase();
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
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');
            
            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }
            
            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                
                if (body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('theme', 'dark');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('theme', 'light');
                }
            });
            
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target) && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                });
            }

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
                 renderUserCards(document.getElementById('students-list'), 'students', initialStudents);
                 renderPagination(document.getElementById('students-pagination'), 'students', initialStudentPagination);

                <?php if ($canManagePilotes): ?>
                    const initialPilotes = <?= json_encode($pilotes_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                    const initialPilotePagination = <?= json_encode($pilotePagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                    console.log("Initial Pilotes (JS Safe):", initialPilotes);
                    renderUserCards(document.getElementById('pilotes-list'), 'pilotes', initialPilotes);
                    renderPagination(document.getElementById('pilotes-pagination'), 'pilotes', initialPilotePagination);
                <?php endif; ?>

                <?php if ($canManageAdmins): ?>
                     const initialAdmins = <?= json_encode($admins_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                     const initialAdminPagination = <?= json_encode($adminPagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                     console.log("Initial Admins (JS Safe):", initialAdmins);
                     renderUserCards(document.getElementById('admins-list'), 'admins', initialAdmins);
                     renderPagination(document.getElementById('admins-pagination'), 'admins', initialAdminPagination);
                <?php endif; ?>
                
                // Set current date in footer
                const currentDateElement = document.getElementById('currentDate');
                if (currentDateElement) {
                    const now = new Date();
                    currentDateElement.textContent = now.toLocaleDateString('fr-FR', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            } catch (e) {
                 console.error("Error during initial data rendering:", e);
                 const errorContainer = document.querySelector('.main-container');
                 if (errorContainer) {
                    const initialErrorDiv = document.createElement('div');
                    initialErrorDiv.className = 'message error-message';
                    initialErrorDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i> Error rendering initial user data. Please check console for details.';
                    errorContainer.insertBefore(initialErrorDiv, errorContainer.children[1]);
                 }
            }
        }); // End DOMContentLoaded
   
