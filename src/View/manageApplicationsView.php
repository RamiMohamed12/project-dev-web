<?php
// Location: src/View/manageApplicationsView.php
// Included by applicationController.php (action=manage)
// Assumes variables: $applications, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage, $allowed_statuses, $conn, $userModel

// Prevent direct access (basic check)
if (!isset($loggedInUserRole) || !in_array($loggedInUserRole, ['admin', 'pilote'])) {
    die("Direct access not permitted or invalid role.");
}

// Determine Dashboard URL based on role
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php';

// --- Fetch User Details for Header ---
$profilePicSrc = null;
$displayName = ucfirst($loggedInUserRole); // Default Display Name
$defaultPic = '../View/images/default_avatar.png'; // Relative path for fallback

// Ensure $userModel is available (should be passed by controller)
if (isset($loggedInUserId) && isset($conn) && isset($userModel) && $userModel instanceof User) {
    try {
        $method = ($loggedInUserRole === 'admin') ? 'readAdmin' : 'readPilote';
        if (method_exists($userModel, $method)) {
            $userDetails = $userModel->$method($loggedInUserId);
            if ($userDetails) {
                 $displayName = htmlspecialchars($userDetails['name'] ?? $displayName);
                 if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                     $picData = is_resource($userDetails['profile_picture']) ? stream_get_contents($userDetails['profile_picture']) : $userDetails['profile_picture'];
                     if ($picData) {
                        $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                     }
                 }
            }
        } else {
             error_log("Method $method does not exist on User model for manageApplicationsView.");
        }
    } catch (Exception $e) {
        error_log("Error fetching $loggedInUserRole details for manage applications view (ID: $loggedInUserId): " . $e->getMessage());
    }
} else {
    // Log missing prerequisites
    if(!isset($userModel) || !$userModel instanceof User) error_log("manageApplicationsView: UserModel not passed or invalid.");
    if(!isset($conn)) error_log("manageApplicationsView: \$conn not passed.");
}
// --- End Fetch User Details ---

// Ensure $allowed_statuses is an array, provide fallback if not passed
// Use the correct statuses from your 'application' table ENUM
$allowed_statuses = $allowed_statuses ?? ['pending', 'accepted', 'rejected'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manage Applications') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- Core Styles (reuse from admin/pilote/manageCompanies) --- */
         :root {
            /* Light Theme */
            --bg-primary-light: #f8f9fc; --bg-secondary-light: #ffffff; --text-primary-light: #1a1e2c; --text-secondary-light: #4a5568;
            --card-border-light: #e2e8f0; --card-shadow-light: 0 4px 20px rgba(0, 0, 0, 0.05); --gradient-primary-light: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-light: linear-gradient(135deg, #3b82f6, #2dd4bf); --input-bg-light: #f1f5f9; --input-border-light: #e2e8f0;
            --button-hover-light: #f1f5f9; --bg-gradient-spot1-light: rgba(99, 102, 241, 0.15); --bg-gradient-spot2-light: rgba(139, 92, 246, 0.15);
            --bg-dots-light: rgba(99, 102, 241, 0.15); --glass-bg-light: rgba(255, 255, 255, 0.7); --glass-border-light: rgba(255, 255, 255, 0.5);
            /* Dark Theme */
            --bg-primary-dark: #13151e; --bg-secondary-dark: #1a1e2c; --text-primary-dark: #f1f5f9; --text-secondary-dark: #a0aec0;
            --card-border-dark: #2d3748; --card-shadow-dark: 0 4px 20px rgba(0, 0, 0, 0.2); --gradient-primary-dark: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-dark: linear-gradient(135deg, #3b82f6, #2dd4bf); --input-bg-dark: #2d3748; --input-border-dark: #4a5568;
            --button-hover-dark: #2d3748; --bg-gradient-spot1-dark: rgba(99, 102, 241, 0.2); --bg-gradient-spot2-dark: rgba(139, 92, 246, 0.2);
            --bg-dots-dark: rgba(139, 92, 246, 0.15); --glass-bg-dark: rgba(26, 30, 44, 0.7); --glass-border-dark: rgba(45, 55, 72, 0.5);
            /* Active theme */
            --bg-primary: var(--bg-primary-light); --bg-secondary: var(--bg-secondary-light); --text-primary: var(--text-primary-light); --text-secondary: var(--text-secondary-light);
            --card-border: var(--card-border-light); --card-shadow: var(--card-shadow-light); --gradient-primary: var(--gradient-primary-light); --gradient-accent: var(--gradient-accent-light);
            --input-bg: var(--input-bg-light); --input-border: var(--input-border-light); --button-hover: var(--button-hover-light); --bg-gradient-spot1: var(--bg-gradient-spot1-light);
            --bg-gradient-spot2: var(--bg-gradient-spot2-light); --glass-bg: var(--glass-bg-light); --glass-border: var(--glass-border-light);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-primary); color: var(--text-primary); min-height: 100vh; position: relative; overflow-x: hidden; padding-top: 80px; }
        .main-wrapper { display: block; min-height: 100vh; }
        .bg-gradient-spot { position: fixed; border-radius: 50%; filter: blur(80px); z-index: -1; }
        .bg-gradient-spot-1 { width: 40vw; height: 40vw; background: var(--bg-gradient-spot1); top: -10%; left: -10%; }
        .bg-gradient-spot-2 { width: 30vw; height: 30vw; background: var(--bg-gradient-spot2); bottom: -5%; right: -5%; }
        .bg-grid { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-size: 40px 40px; background-image: radial-gradient(circle, var(--bg-dots-light) 1px, transparent 1px); z-index: -1; opacity: 0.4; }
        .main-container { padding: 2rem; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--text-secondary) transparent; max-width: 1440px; /* Wider for management table */ margin-left: auto; margin-right: auto; } /* Wider container */
        .main-container::-webkit-scrollbar { width: 6px; } .main-container::-webkit-scrollbar-track { background: transparent; } .main-container::-webkit-scrollbar-thumb { background-color: var(--text-secondary); border-radius: 10px; }
        .page-header { margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; color: var(--text-primary); }
        .page-header .breadcrumb { display: flex; list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: var(--text-secondary); }
        .page-header .breadcrumb li { display: flex; align-items: center; } .page-header .breadcrumb li:not(:last-child)::after { content: '/'; margin: 0 0.5rem; color: var(--text-secondary); }
        .page-header .breadcrumb a { color: var(--text-secondary); text-decoration: none; transition: color 0.3s ease; } .page-header .breadcrumb a:hover { color: var(--text-primary); }
        .message { padding: 1rem 1.25rem; margin-bottom: 1.5rem; border-radius: 12px; display: flex; align-items: center; animation: fadeUp 0.6s ease forwards; border: 1px solid transparent; }
        .message i { margin-right: 0.75rem; font-size: 1.25rem; } .error-message { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
        .success-message { background-color: rgba(34, 197, 94, 0.1); color: #22c55e; border-color: rgba(34, 197, 94, 0.2); }
        .card { background: var(--bg-secondary); border-radius: 16px; border: 1px solid var(--card-border); box-shadow: var(--card-shadow); overflow: hidden; margin-bottom: 1.5rem; }
        .card-header { background: var(--input-bg); padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--card-border); display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 1.25rem; font-weight: 600; color: var(--text-primary); margin: 0; display: flex; align-items: center; } .card-header h2 i { margin-right: 0.75rem; }
        .card-body { padding: 0; } /* Remove padding for table */
        .glass { background: var(--glass-bg); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid var(--glass-border); }
        .table-responsive { border-radius: 0 0 16px 16px; overflow: hidden; }
        .table { margin-bottom: 0; border-color: var(--card-border); }
        .table th, .table td { padding: 1rem 1.25rem; vertical-align: middle; font-size: 0.95rem; color: var(--text-primary); border-top-color: var(--card-border); }
        .table thead th { background-color: var(--input-bg); color: var(--text-secondary); font-weight: 600; border-bottom-width: 1px; border-bottom-color: var(--card-border) !important; white-space: nowrap; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table-hover tbody tr:hover { background-color: var(--input-bg); }
        .form-select-sm { font-size: 0.85rem; padding: 0.4rem 0.8rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; font-size: 0.95rem; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn i { margin-right: 0.5rem; } .btn-primary { background: var(--gradient-primary); color: white; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); color: white; }
        .btn-secondary { background-color: var(--input-bg); color: var(--text-primary); border: 1px solid var(--input-border); } .btn-secondary:hover { background-color: var(--button-hover); color: var(--text-primary); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3); }
        .btn-success:hover { transform: translateY(-2px); color: white; }
        .btn-outline-primary { color: var(--text-primary); border-color: var(--input-border); background-color: transparent; } .btn-outline-primary:hover { background-color: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.3); color: #6366f1; }
        .btn-sm { padding: 0.5rem 0.75rem; font-size: 0.85rem; border-radius: 6px; }
        .btn-link { text-decoration: none; padding: 0; background: none; border: none; color: #6366f1; } .btn-link:hover { color: #4f46e5; text-decoration: underline; }
        /* Enhanced Status Badges */
        .status-badge { padding: 0.4em 0.8em; font-size: 0.85rem; font-weight: 500; border-radius: 20px; /* Pill shape */ display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
        .status-badge i { font-size: 0.9em; }
        .status-pending { background-color: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-accepted { background-color: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .status-rejected { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; }
        /* Add styles for other statuses if needed */
        .theme-toggle { position: fixed; bottom: 20px; right: 20px; background: var(--gradient-primary); color: white; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer; box-shadow: 0 4px 15px rgba(99, 102, 141, 0.3); z-index: 100; transition: all 0.3s ease; }
        .theme-toggle:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4); }
        .empty-state { text-align: center; padding: 3rem 1rem; background: var(--bg-secondary); border-radius: 16px; border: 1px solid var(--card-border); }
        .empty-state i { font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem; } .empty-state h3 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary); }
        .empty-state p { color: var(--text-secondary); margin-bottom: 1.5rem; }
        .dark-mode { /* Dark mode variables */
            --bg-primary: var(--bg-primary-dark); --bg-secondary: var(--bg-secondary-dark); --text-primary: var(--text-primary-dark); --text-secondary: var(--text-secondary-dark);
            --card-border: var(--card-border-dark); --card-shadow: var(--card-shadow-dark); --input-bg: var(--input-bg-dark); --input-border: var(--input-border-dark);
            --button-hover: var(--button-hover-dark); --bg-gradient-spot1: var(--bg-gradient-spot1-dark); --bg-gradient-spot2: var(--bg-gradient-spot2-dark);
            --glass-bg: var(--glass-bg-dark); --glass-border: var(--glass-border-dark);
        }
        .dark-mode .bg-grid { background-image: radial-gradient(circle, var(--bg-dots-dark) 1px, transparent 1px); }
        .dashboard-button-top-left { position: fixed; top: 15px; left: 15px; z-index: 1010; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { opacity: 0; animation: fadeUp 0.6s ease forwards; }
        .delay-1 { animation-delay: 0.1s; }
        /* Specific table styles */
        .applications-table td .student-info { font-size: 0.9em; color: var(--text-secondary); }
        .applications-table .actions-cell { min-width: 150px; /* Ensure space for buttons */ text-align: right; }
        .applications-table .status-update-form { display: flex; align-items: center; gap: 0.5rem; min-width: 220px; /* Ensure dropdown + button fit */ }
    </style>
</head>
<body>
    <!-- Back to Dashboard Button -->
    <div class="dashboard-button-top-left fade-in">
        <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>

    <!-- Theme toggle button -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="main-wrapper">
        <div class="main-container">

            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fas fa-tasks me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= htmlspecialchars($dashboardUrl) ?>">Dashboard</a></li>
                        <li>Manage Applications</li>
                    </ul>
                </div>
            </div>

            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Applications List Card -->
            <div class="card glass fade-in delay-1"> <!-- Added glass effect -->
                <div class="card-header">
                    <h2><i class="fa-solid fa-list-check"></i> Applications Received</h2>
                    <?php if(is_array($applications)): ?>
                        <span class="badge bg-secondary rounded-pill"><?= count($applications) ?> Total</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (is_array($applications) && !empty($applications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle applications-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag"></i> ID</th>
                                        <th><i class="fas fa-user-graduate"></i> Student</th>
                                        <th><i class="fas fa-briefcase"></i> Internship</th>
                                        <th><i class="fas fa-building"></i> Company</th>
                                        <th><i class="fas fa-calendar-alt"></i> Applied</th>
                                        <th><i class="fas fa-file-alt"></i> CV</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th style="min-width: 240px;"><i class="fas fa-edit"></i> Update Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app):
                                       // *** FIX: Use id_application ***
                                       $appId = htmlspecialchars($app['id_application']);
                                       $status = $app['status'] ?? 'pending';
                                       $statusClass = strtolower(str_replace(' ', '-', $status));
                                       $statusIcon = 'fa-question-circle';
                                        switch ($status) {
                                            case 'pending': $statusIcon = 'fa-clock'; break;
                                            case 'accepted': $statusIcon = 'fa-check-circle'; break;
                                            case 'rejected': $statusIcon = 'fa-times-circle'; break;
                                            // Add other cases if needed
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $appId ?></td>
                                            <td>
                                                <div><?= htmlspecialchars($app['student_name'] ?? 'N/A') ?></div>
                                                <div class="student-info"><?= htmlspecialchars($app['student_email'] ?? '') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($app['internship_title'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($app['name_company'] ?? 'N/A') ?></td>
                                            <td><?= isset($app['app_created_at']) ? htmlspecialchars(date('d M Y', strtotime($app['app_created_at']))) : 'N/A' ?></td>
                                            <td>
                                                <?php if (!empty($app['cv'])): ?>
                                                     <!-- *** FIX: Use $appId *** -->
                                                    <a href="applicationController.php?action=downloadCv&id=<?= $appId ?>" class="btn btn-sm btn-outline-primary" title="Download <?= htmlspecialchars(basename($app['cv'])) ?>">
                                                        <i class="fas fa-download"></i> CV
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-secondary fst-italic">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?= $statusClass ?>">
                                                   <i class="fas <?= $statusIcon ?>"></i>
                                                    <?= htmlspecialchars(ucwords($status)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="post" action="applicationController.php" class="status-update-form">
                                                    <input type="hidden" name="action" value="updateStatus">
                                                     <!-- *** FIX: Use $appId *** -->
                                                    <input type="hidden" name="application_id" value="<?= $appId ?>">
                                                    <!-- *** FIX: Use $appId *** -->
                                                    <select name="new_status" class="form-select form-select-sm" required aria-label="Update status for application <?= $appId ?>">
                                                        <?php foreach ($allowed_statuses as $statusOption): ?>
                                                            <option value="<?= htmlspecialchars($statusOption) ?>" <?= ($status === $statusOption) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars(ucwords($statusOption)) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-success" title="Save Status Update">
                                                        <i class="fas fa-check"></i> Save
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h3>No applications found</h3>
                            <p>
                                <?= ($loggedInUserRole === 'pilote') ? 'No applications have been submitted for internships created by you yet.' : 'No applications have been submitted to the system yet.' ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div> <!-- End card-body -->
            </div> <!-- End card -->
        </div> <!-- End main-container -->
    </div> <!-- End main-wrapper -->

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            function applyTheme(theme) {
                if (theme === 'dark') { body.classList.add('dark-mode'); icon.classList.replace('fa-moon', 'fa-sun'); }
                 else { body.classList.remove('dark-mode'); icon.classList.replace('fa-sun', 'fa-moon'); }
            }
            if (savedTheme) { applyTheme(savedTheme); } else { applyTheme(prefersDark ? 'dark' : 'light'); }
            themeToggle.addEventListener('click', function() {
                const newTheme = body.classList.contains('dark-mode') ? 'light' : 'dark';
                applyTheme(newTheme); localStorage.setItem('theme', newTheme);
            });

             // Fade out messages
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => { setTimeout(() => { message.style.transition = 'opacity 0.5s ease'; message.style.opacity = '0'; setTimeout(() => message.remove(), 500); }, 5000); });

        });
    </script>
</body>
</html>
