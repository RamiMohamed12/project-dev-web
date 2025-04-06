<?php
// Location: /home/demy/project-dev-web/src/View/student.php

// --- Required Includes & Session ---
require_once __DIR__ . '/../../config/config.php'; // Need $conn
require_once __DIR__ . '/../Auth/AuthCheck.php';
require_once __DIR__ . '/../Auth/AuthSession.php';
require_once __DIR__ . '/../Model/user.php';      // To fetch user details
require_once __DIR__ . '/../Model/Application.php'; // To fetch application statistics

if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Authentication Check ---
AuthCheck::checkUserAuth('student', 'login.php');
// --- End Authentication Check ---

// Get Logged-in User Info from Session initially
$loggedInUserId = AuthSession::getUserData('user_id');
$sessionUserName = AuthSession::getUserData('user_name');
$sessionUserEmail = AuthSession::getUserData('user_email');

// --- Fetch Full User Details from Database ---
$userDetails = null;
$profilePicSrc = null;
$dbUserName = null;
$dbUserEmail = null;
$dbUserSchool = null; // Variable for school
$defaultPic = '../View/images/default_avatar.png'; // ** ADJUST PATH AS NEEDED **

// --- Application Statistics ---
$totalApplications = 0;
$acceptedApplications = 0;
$pendingApplications = 0;
$rejectedApplications = 0;
$recentApplications = [];

if ($loggedInUserId && isset($conn)) {
    try {
        $userModel = new User($conn);
        $userDetails = $userModel->readStudent($loggedInUserId); // Fetch this student's details

        if ($userDetails) {
            $dbUserName = $userDetails['name'];
            $dbUserEmail = $userDetails['email'];
            $dbUserSchool = $userDetails['school']; // Get school
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                 $picData = is_resource($userDetails['profile_picture']) ? stream_get_contents($userDetails['profile_picture']) : $userDetails['profile_picture'];
                 if ($picData) {
                     $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                 }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching student details for dashboard (ID: $loggedInUserId): " . $e->getMessage());
    }
}
$displayName = htmlspecialchars($dbUserName ?? $sessionUserName ?? 'Student');
$displayEmail = htmlspecialchars($dbUserEmail ?? $sessionUserEmail ?? 'N/A');
$displaySchool = htmlspecialchars($dbUserSchool ?? 'N/A'); // Display school


// --- Message Handling ---
$successMessage = '';
if (isset($_GET['profile_update']) && $_GET['profile_update'] == 'success') {
    $successMessage = "Your profile has been updated successfully.";
}
if (isset($_GET['success'])) {
    $successMessage = htmlspecialchars($_GET['success']);
}
$errorMessage = '';
if (isset($_GET['error'])) {
    $errorMessage = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigui - Student Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/css/stud.css">
    <style>
    /* Additional animations and styles */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stats-section {
        padding: 3rem 0;
        animation: fadeInUp 0.8s ease-out;
        position: relative;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: var(--bg-secondary);
        border-radius: var(--border-radius);
        border: 1px solid var(--card-border);
        box-shadow: var(--card-shadow);
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        display: inline-block;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .stat-label {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }
    
    .total-applications .stat-icon { color: #6366f1; animation: pulse 2s infinite; }
    .accepted-applications .stat-icon { color: #10b981; animation: pulse 2s infinite 0.3s; }
    .pending-applications .stat-icon { color: #f59e0b; animation: pulse 2s infinite 0.6s; }
    .rejected-applications .stat-icon { color: #ef4444; animation: pulse 2s infinite 0.9s; }
    
    .recent-applications {
        margin-top: 2rem;
    }
    
    .recent-app-card {
        background: var(--bg-secondary);
        border-radius: var(--border-radius);
        border: 1px solid var(--card-border);
        box-shadow: var(--card-shadow);
        padding: 1.25rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        animation: fadeInUp 0.5s ease-out;
    }
    
    .recent-app-card:hover {
        transform: translateX(5px);
        border-color: #6366f1;
    }
    
    .recent-app-logo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .recent-app-info {
        flex: 1;
    }
    
    .recent-app-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .recent-app-company {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    
    .recent-app-date {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    .recent-app-status {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-pending {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    .status-accepted {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    
    .status-rejected {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    
    .view-all-btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: var(--gradient-primary);
        color: white;
        border-radius: var(--inner-radius);
        font-weight: 500;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }
    
    .view-all-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
    }
    
    .floating-accent {
        animation: float 6s ease-in-out infinite;
    }
    
    .action-card {
        animation: fadeInUp 0.5s ease-out;
    }
    
    .action-card:nth-child(1) { animation-delay: 0.1s; }
    .action-card:nth-child(2) { animation-delay: 0.2s; }
    .action-card:nth-child(3) { animation-delay: 0.3s; }
    .action-card:nth-child(4) { animation-delay: 0.4s; }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--text-secondary);
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--text-secondary);
        opacity: 0.5;
    }
</style>
</head>
<body>
    <!-- Floating Layout Accents (Optional) -->
    <div class="floating-accent accent-1"></div>
    <div class="floating-accent accent-2"></div>
    <div class="floating-accent accent-3"></div>
    
    <header class="header">
        <nav class="navbar">
            <div class="nav-container bento-card">
                <!-- Logo -->
                <a href="student.php" class="logo">
                    <div class="logo-image-container">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="logo-text">Navigui</span>
                </a>

                <!-- Menu Items -->
                <div class="menu-items">
                    <a href="student.php" class="menu-item active">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item">Applications</a>
                    <?php if ($loggedInUserId): ?>
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student" class="menu-item">Profile</a>
                    <?php endif; ?>
                </div>

                <!-- Auth & Theme -->
                <div class="nav-right">
                    <label class="switch">
                        <input type="checkbox" id="themeToggle">
                        <span class="slider"></span>
                    </label>
                    
                    <div class="user-dropdown">
                        <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile" class="user-avatar">
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="Profile" class="dropdown-avatar">
                                <div class="dropdown-user-info">
                                    <div class="dropdown-user-name"><?= $displayName ?></div>
                                    <div class="dropdown-user-email"><?= $displayEmail ?></div>
                                </div>
                            </div>
                            <div class="dropdown-items">
                                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student" class="dropdown-item">
                                    <i class="fas fa-user-edit"></i>
                                    <span>Edit Profile</span>
                                </a>
                                <a href="../Controller/applicationController.php?action=myapps" class="dropdown-item">
                                    <i class="fas fa-file-alt"></i>
                                    <span>My Applications</span>
                                </a>
                                <a href="../Controller/wishlistController.php?action=view" class="dropdown-item">
                                    <i class="fas fa-heart"></i>
                                    <span>My Wishlist</span>
                                </a>
                                <a href="../Controller/logoutController.php" class="dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button class="hamburger-menu" id="mobile-toggle" aria-label="Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobile-menu">
                <div class="mobile-menu-content">
                    <a href="student.php" class="mobile-menu-item active">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="mobile-menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="mobile-menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="mobile-menu-item">Applications</a>
                    <?php if ($loggedInUserId): ?>
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student" class="mobile-menu-item">Profile</a>
                    <?php endif; ?>
                    <a href="../Controller/logoutController.php" class="mobile-menu-item" style="color: #ef4444;">Logout <i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <!-- Success/Error Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($successMessage) ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($errorMessage) ?></span>
            </div>
        <?php endif; ?>

        <!-- Hero Section / Welcome -->
        <section class="hero-section">
            <div class="hero-pattern"></div>
            <div class="container">
                <div class="hero-content">
                        <div class="greeting-col">
                            <div class="user-school">
                                <i class="fas fa-university"></i>
                                <?= $displaySchool ?>
                            </div>
                            <h1>Welcome back, <?= $displayName ?>!</h1>
                            <p>Access your internship portal, explore opportunities, and manage your applications all in one place.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Application Statistics Section -->
        <section class="stats-section">
            <div class="container">
                <div class="section-header">
                    <h2>Your Application Statistics</h2>
                    <p>Track your progress and application status at a glance</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card total-applications">
                        <i class="fas fa-file-alt stat-icon"></i>
                        <div class="stat-value"><?= $totalApplications ?></div>
                        <div class="stat-label">Total Applications</div>
                    </div>
                    
                    <div class="stat-card accepted-applications">
                        <i class="fas fa-check-circle stat-icon"></i>
                        <div class="stat-value"><?= $acceptedApplications ?></div>
                        <div class="stat-label">Accepted</div>
                    </div>
                    
                    <div class="stat-card pending-applications">
                        <i class="fas fa-clock stat-icon"></i>
                        <div class="stat-value"><?= $pendingApplications ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    
                    <div class="stat-card rejected-applications">
                        <i class="fas fa-times-circle stat-icon"></i>
                        <div class="stat-value"><?= $rejectedApplications ?></div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>
                
                <div class="recent-applications">
                    <h3>Recent Applications</h3>
                    
                    <?php if (empty($recentApplications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <p>You haven't applied to any internships yet.</p>
                            <a href="../Controller/offerController.php?action=view" class="view-all-btn">Browse Internships</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentApplications as $app): ?>
                            <div class="recent-app-card">
                                <?php 
                                $companyLogoSrc = '../View/images/default_company.png';
                                if (!empty($app['company_picture']) && !empty($app['company_picture_mime'])) {
                                    $logoData = is_resource($app['company_picture']) ? stream_get_contents($app['company_picture']) : $app['company_picture'];
                                    if ($logoData) {
                                        $companyLogoSrc = 'data:' . htmlspecialchars($app['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                                    }
                                }
                                ?>
                                <img src="<?= $companyLogoSrc ?>" alt="Company" class="recent-app-logo">
                                <div class="recent-app-info">
                                    <div class="recent-app-title"><?= htmlspecialchars($app['title'] ?? 'Internship') ?></div>
                                    <div class="recent-app-company"><?= htmlspecialchars($app['name_company'] ?? 'Company') ?></div>
                                    <div class="recent-app-date">Applied on: <?= date('M d, Y', strtotime($app['created_at'] ?? date('Y-m-d'))) ?></div>
                                </div>
                                <?php 
                                $status = $app['status'] ?? 'pending';
                                $statusClass = 'status-pending';
                                $statusText = 'Pending';
                                
                                if ($status === 'accepted') {
                                    $statusClass = 'status-accepted';
                                    $statusText = 'Accepted';
                                } elseif ($status === 'rejected') {
                                    $statusClass = 'status-rejected';
                                    $statusText = 'Rejected';
                                }
                                ?>
                                <span class="recent-app-status <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <div style="text-align: center;">
                            <a href="../Controller/applicationController.php?action=myapps" class="view-all-btn">
                                <i class="fas fa-list"></i> View All Applications
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Quick Actions Section -->
        <section class="quick-actions-section">
            <div class="container">
                <div class="section-header">
                    <h2>Explore Your Options</h2>
                    <p>Find and track internship opportunities with top companies</p>
                </div>
                
                <div class="actions-grid">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="action-title">Available Offers</h3>
                        <p class="action-text">Browse through hundreds of internship opportunities from leading companies in various industries.</p>
                        <a href="../Controller/offerController.php?action=view" class="action-button">Explore Offers</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="action-title">My Wishlist</h3>
                        <p class="action-text">Access internships you've saved to review later and keep track of your favorite opportunities.</p>
                        <a href="../Controller/wishlistController.php?action=view" class="action-button">View Wishlist</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="action-title">My Applications</h3>
                        <p class="action-text">Monitor the status of your applications, prepare for interviews, and track your progress.</p>
                        <a href="../Controller/applicationController.php?action=myapps" class="action-button">Check Applications</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h3 class="action-title">Profile Settings</h3>
                        <p class="action-text">Update your information, add skills and experiences to stand out to potential employers.</p>
                        <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student" class="action-button">Edit Profile</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- User Info Section -->
        <section class="user-info-section">
            <div class="info-pattern"></div>
            <div class="container">
                <div class="section-header">
                    <h2>Your Profile Information</h2>
                    <p>Keep your details updated to increase your chances of being noticed</p>
                </div>
                
                <div class="user-info-card">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-user"></i>
                                Full Name
                            </span>
                            <span class="info-value"><?= $displayName ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </span>
                            <span class="info-value"><?= $displayEmail ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-university"></i>
                                School
                            </span>
                            <span class="info-value"><?= $displaySchool ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-user-graduate"></i>
                                Role
                            </span>
                            <span class="info-value">Student</span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-clock"></i>
                                Logged in since
                            </span>
                            <span class="info-value"><?= date('Y-m-d H:i:s', AuthSession::getUserData('logged_in_time') ?? time()) ?></span>
                        </div>
                        
                        <?php if(!empty($userDetails['year'])): ?>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="fas fa-calendar-alt"></i>
                                Year
                            </span>
                            <span class="info-value"><?= htmlspecialchars($userDetails['year']) ?> Year</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">
                        <div class="logo-image-container">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span>Navigui</span>
                    </div>
                    <p>Connecting students with their dream internships since 2020.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>For Students</h3>
                    <ul>
                        <li><a href="#">Browse Internships</a></li>
                        <li><a href="#">Career Resources</a></li>
                        <li><a href="#">Resume Builder</a></li>
                        <li><a href="#">Success Stories</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>For Employers</h3>
                    <ul>
                        <li><a href="#">Post an Internship</a></li>
                        <li><a href="#">Browse Candidates</a></li>
                        <li><a href="#">Employer Resources</a></li>
                        <li><a href="#">Success Stories</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-envelope"></i> support@navigui.com</li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Innovation Drive, San Francisco, CA 94107</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y'); ?> Navigui - Student Internship Platform</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            
            // Check for saved theme preference or use default
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);
            
            // Set toggle checked state based on current theme
            if (currentTheme === 'dark') {
                themeToggle.checked = true;
            }
            
            themeToggle.addEventListener('change', function() {
                const newTheme = this.checked ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
            
            // Mobile Menu Toggle
            const mobileToggle = document.getElementById('mobile-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            
            mobileToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                mobileToggle.classList.toggle('active');
            });
            
            // Auto hide success messages after 5 seconds
            const successMessages = document.querySelectorAll('.success-message');
            if (successMessages.length > 0) {
                setTimeout(function() {
                    successMessages.forEach(message => {
                        message.style.opacity = '0';
                        setTimeout(() => {
                            message.style.display = 'none';
                        }, 300);
                    });
                }, 5000);
            }
            
            // Add scroll reveal animation
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.stat-card, .action-card, .recent-app-card, .user-info-card');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (elementPosition < screenPosition) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Set initial state for scroll animations
            document.querySelectorAll('.stat-card, .action-card, .recent-app-card, .user-info-card').forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.5s ease-out';
            });
            
            // Run on load and scroll
            window.addEventListener('scroll', animateOnScroll);
            window.addEventListener('load', animateOnScroll);
            
            // Add hover effects for cards
            const cards = document.querySelectorAll('.action-card, .stat-card, .recent-app-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = this.classList.contains('recent-app-card') ? 'translateX(5px)' : 'translateY(-5px)';
                    this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>
