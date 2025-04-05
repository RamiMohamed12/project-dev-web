<?php
// --- Required Includes & Session ---
require_once __DIR__ . '/../../config/config.php'; // Need $conn for DB access
require_once __DIR__ . '/../Auth/AuthCheck.php';   // Checks if user is allowed here
require_once __DIR__ . '/../Auth/AuthSession.php'; // For getting session data
require_once __DIR__ . '/../Model/user.php';      // To fetch user details from DB

// Start session VERY FIRST
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Authentication Check ---
AuthCheck::checkUserAuth('admin', 'login.php');
// --- End Authentication Check ---

// Get Logged-in User Info from Session initially
$loggedInUserId = AuthSession::getUserData('user_id');
$sessionUserName = AuthSession::getUserData('user_name');
$sessionUserEmail = AuthSession::getUserData('user_email');

// --- Fetch Full User Details (including profile pic) from Database ---
$userDetails = null;
$profilePicSrc = null;
$dbUserName = null;
$dbUserEmail = null;
$defaultPic = '../View/images/default_avatar.png'; // ** ADJUST PATH AS NEEDED **

if ($loggedInUserId && isset($conn)) {
    try {
        $userModel = new User($conn);
        $userDetails = $userModel->readAdmin($loggedInUserId); // Fetch this admin's details

        if ($userDetails) {
            // Use name/email from DB if available
            $dbUserName = $userDetails['name'];
            $dbUserEmail = $userDetails['email'];

            // Generate profile picture source if data exists
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                 // PDO might return BLOB as resource stream or string depending on config/driver
                 // Assuming string or easily readable data here. Adjust if needed.
                 $picData = is_resource($userDetails['profile_picture'])
                            ? stream_get_contents($userDetails['profile_picture'])
                            : $userDetails['profile_picture'];
                 if ($picData) {
                    $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                 }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching admin details for dashboard (ID: $loggedInUserId): " . $e->getMessage());
        // Silently fail, fall back to session data
    }
}

// Determine final display name/email (prefer DB, fallback to session)
$displayName = htmlspecialchars($dbUserName ?? $sessionUserName ?? 'Admin');
$displayEmail = htmlspecialchars($dbUserEmail ?? $sessionUserEmail ?? 'N/A');


// --- Message Handling (e.g., after profile update) ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Navigui</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet"> 
</head>
<body>
    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>
    
    <!-- Theme toggle button -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar glass animate-fadeUp">
            <div class="sidebar-header">
                <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture">
                <div>
                    <h3><?= $displayName ?></h3>
                    <p><?= $displayEmail ?></p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <?php if ($loggedInUserId): ?>
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="../Controller/settingsController.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="../Controller/logoutController.php" class="logout-btn">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main content -->
        <div class="main-container">
            <!-- Top navbar for mobile -->
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">
                        <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile">
                        <span><i class="fas fa-user-shield me-2"></i>Admin Panel</span>
                    </a>
                    <button class="navbar-toggler" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </nav>
            
            <!-- Success message -->
            <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success animate-fadeUp" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMessage) ?>
            </div>
            <?php endif; ?>
            
            <!-- Bento grid layout -->
            <div class="bento-grid">
                <!-- Welcome card -->
                <div class="bento-item glass welcome-card animate-fadeUp">
                    <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile Picture" class="profile-image">
                    <div class="profile-details">
                        <h2>Welcome, <?= $displayName ?>!</h2>
                        <p><i class="fas fa-envelope me-2"></i> <?= $displayEmail ?></p>
                        <p><i class="fas fa-user-tag me-2"></i> Administrator</p>
                        <p><i class="fas fa-clock me-2"></i> Logged in since: <?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></p>
                    </div>
                </div>
                
                <!-- Time card -->
                <div class="bento-item glass time-card animate-fadeUp delay-1">
                    <div class="time" id="currentTime">12:00</div>
                    <div class="date" id="currentDate">Monday, Jan 1</div>
                </div>
                
                <!-- Stats cards -->
                <div class="bento-item glass stats-card animate-fadeUp delay-1">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number">150</div>
                    <div class="stats-label">Total Users</div>
                </div>
                
                <div class="bento-item glass stats-card animate-fadeUp delay-2">
                    <div class="stats-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stats-number">45</div>
                    <div class="stats-label">Companies</div>
                </div>
                
                <div class="bento-item glass stats-card animate-fadeUp delay-3">
                    <div class="stats-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stats-number">78</div>
                    <div class="stats-label">Active Offers</div>
                </div>
                
                <div class="bento-item glass stats-card animate-fadeUp delay-4">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number">24</div>
                    <div class="stats-label">Approved Today</div>
                </div>
                
                <!-- Quick actions -->
                <div class="bento-item glass quick-actions animate-fadeUp delay-2">
                    <h3 class="actions-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="../Controller/userController.php" class="action-btn">
                            <i class="fas fa-users-gear"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="../Controller/companyController.php" class="action-btn">
                            <i class="fas fa-building"></i>
                            <span>Manage Companies</span>
                        </a>
                        <a href="../Controller/internshipController.php" class="action-btn">
                            <i class="fas fa-file-alt"></i>
                            <span>Manage Offers</span>
                        </a>
                        <?php if ($loggedInUserId): ?>
                        <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin" class="action-btn">
                            <i class="fas fa-user-pen"></i>
                            <span>Edit Profile</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent activity -->
                <div class="bento-item glass recent-activity animate-fadeUp delay-3">
                    <h3 class="activity-title"><i class="fas fa-history me-2"></i>Recent Activity</h3>
                    <ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <h4>New User Registered</h4>
                                <p>John Doe created a new student account</p>
                            </div>
                            <div class="activity-time">2 hours ago</div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Company Updated</h4>
                                <p>Acme Corp updated their company profile</p>
                            </div>
                            <div class="activity-time">3 hours ago</div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="activity-content">
                                <h4>New Internship Offer</h4>
                                <p>TechStart posted a new web development internship</p>
                            </div>
                            <div class="activity-time">5 hours ago</div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Offer Approved</h4>
                                <p>You approved DataSys's data analyst internship</p>
                            </div>
                            <div class="activity-time">Yesterday</div>
                        </li>
                    </ul>
                </div>
                
                <!-- System status -->
                <div class="bento-item glass system-status animate-fadeUp delay-4">
                    <h3 class="status-title"><i class="fas fa-server me-2"></i>System Status</h3>
                    <div class="status-grid">
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="status-info">
                                <h4>Database</h4>
                                <p>Connected • 45ms response</p>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <div class="status-info">
                                <h4>Server Load</h4>
                                <p>Normal • 23% CPU</p>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-memory"></i>
                            </div>
                            <div class="status-info">
                                <h4>Memory Usage</h4>
                                <p>Good • 2.1GB / 8GB</p>
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-icon">
                                <i class="fas fa-hdd"></i>
                            </div>
                            <div class="status-info">
                                <h4>Storage</h4>
                                <p>75% Free • 186GB available</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Calendar preview -->
                <div class="bento-item glass calendar-card animate-fadeUp delay-5">
                    <h3 class="calendar-title"><i class="fas fa-calendar me-2"></i>Calendar</h3>
                    <div class="calendar-content">
                        <div class="calendar-day">
                            <div class="day-name">Mon</div>
                            <div class="day-number">12</div>
                            <div class="day-events">2 events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Tue</div>
                            <div class="day-number">13</div>
                            <div class="day-events">1 event</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Wed</div>
                            <div class="day-number today">14</div>
                            <div class="day-events">4 events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Thu</div>
                            <div class="day-number">15</div>
                            <div class="day-events">No events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Fri</div>
                            <div class="day-number">16</div>
                            <div class="day-events">1 event</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Sat</div>
                            <div class="day-number">17</div>
                            <div class="day-events">No events</div>
                        </div>
                        <div class="calendar-day">
                            <div class="day-name">Sun</div>
                            <div class="day-number">18</div>
                            <div class="day-events">No events</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="JavaScript/adminAnimation.js"> </script> 
</body>
</html>
