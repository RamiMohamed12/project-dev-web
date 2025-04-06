<?php
// Location: src/View/myApplicationsView.php

// --- Default Picture Paths ---
$defaultCompanyPic = '../View/images/default_company.png'; // Use relative path again
$defaultUserPic = '../View/images/default_avatar.png';    // Use relative path again

// --- Initialize User Details ---
$profilePicSrc = null;
$displayName = 'Student';
$displayEmail = '';

// --- Fetch User Details for Profile Display ---
if (isset($loggedInUserId) && isset($conn)) {
    try {
        require_once __DIR__ . '/../Model/user.php';
        $userModel = new User($conn);
        $userDetails = $userModel->readStudent($loggedInUserId);

        if ($userDetails) {
            $displayName = htmlspecialchars($userDetails['name'] ?? $displayName);
            $displayEmail = htmlspecialchars($userDetails['email'] ?? $displayEmail);
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                 $picData = is_resource($userDetails['profile_picture']) ? stream_get_contents($userDetails['profile_picture']) : $userDetails['profile_picture'];
                if ($picData) {
                    $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching student details for applications view header (ID: $loggedInUserId): " . $e->getMessage());
    }
}
// --- End Fetch User Details ---

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Navigui</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/css/stud.application.css"> <!-- Relative Path -->
    <style>
        /* Add any additional page-specific styles here if needed */
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container bento-card">
                <!-- Logo -->
                <a href="../View/student.php" class="logo"> <!-- Relative Path -->
                    <div class="logo-image-container">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="logo-text">Navigui</span>
                </a>

                <!-- Menu Items -->
                <div class="menu-items">
                    <a href="../View/student.php" class="menu-item">Home</a> <!-- Relative Path -->
                    <a href="../Controller/offerController.php?action=view" class="menu-item">Offers</a> <!-- Relative Path -->
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a> <!-- Relative Path -->
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item active">Applications</a> <!-- Relative Path -->
                    <?php if (isset($loggedInUserId)): ?>
                    <a href="../Controller/editUser.php?id=<?= htmlspecialchars((string)$loggedInUserId) ?>&type=student" class="menu-item">Profile</a> <!-- Relative Path -->
                    <?php endif; ?>
                </div>

                <!-- Auth & Theme -->
                <div class="nav-right">
                    <label class="switch">
                        <input type="checkbox" id="themeToggle">
                        <span class="slider"></span>
                    </label>

                    <?php if (isset($loggedInUserId)): ?>
                    <div class="user-dropdown">
                        <?php
                        $finalPicSrc = $profilePicSrc ?? $defaultUserPic; // Uses relative default path now
                        ?>
                        <img src="<?= $finalPicSrc ?>" alt="Profile" class="user-avatar"> <!-- Removed htmlspecialchars -->
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <img src="<?= $finalPicSrc ?>" alt="Profile" class="dropdown-avatar"> <!-- Removed htmlspecialchars -->
                                <div class="dropdown-user-info">
                                    <div class="dropdown-user-name"><?= htmlspecialchars($displayName) ?></div>
                                    <div class="dropdown-user-email"><?= htmlspecialchars($displayEmail) ?></div>
                                </div>
                            </div>
                            <div class="dropdown-items">
                                <a href="../Controller/editUser.php?id=<?= htmlspecialchars((string)$loggedInUserId) ?>&type=student" class="dropdown-item"> <!-- Relative Path -->
                                    <i class="fas fa-user-edit"></i>
                                    <span>Edit Profile</span>
                                </a>
                                <a href="../Controller/applicationController.php?action=myapps" class="dropdown-item"> <!-- Relative Path -->
                                    <i class="fas fa-file-alt"></i>
                                    <span>My Applications</span>
                                </a>
                                <a href="../Controller/wishlistController.php?action=view" class="dropdown-item"> <!-- Relative Path -->
                                    <i class="fas fa-heart"></i>
                                    <span>My Wishlist</span>
                                </a>
                                <a href="../Controller/logoutController.php" class="dropdown-item logout"> <!-- Relative Path -->
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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
                    <a href="../View/student.php" class="mobile-menu-item">Home</a> <!-- Relative Path -->
                    <a href="../Controller/offerController.php?action=view" class="mobile-menu-item">Offers</a> <!-- Relative Path -->
                    <a href="../Controller/wishlistController.php?action=view" class="mobile-menu-item">Wishlist</a> <!-- Relative Path -->
                    <a href="../Controller/applicationController.php?action=myapps" class="mobile-menu-item active">Applications</a> <!-- Relative Path -->
                    <?php if (isset($loggedInUserId)): ?>
                    <a href="../Controller/editUser.php?id=<?= htmlspecialchars((string)$loggedInUserId) ?>&type=student" class="mobile-menu-item">Profile</a> <!-- Relative Path -->
                    <?php endif; ?>
                    <a href="../Controller/logoutController.php" class="mobile-menu-item" style="color: #ef4444;">Logout <i class="fas fa-sign-out-alt"></i></a> <!-- Relative Path -->
                </div>
            </div>
        </nav>
    </header>

    <main>
       <!-- Rest of the main content (page header, application list) remains the same -->
        <section class="page-header">
            <div class="container">
                <div class="page-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h1 class="page-title">My Applications</h1>
                <p class="page-subtitle">Track the status of your internship applications and view feedback</p>
            </div>
        </section>

        <div class="container">
                       <!-- Messages -->
            <?php if (!empty($errorMessage)): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($errorMessage) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($successMessage) ?></span>
                </div>
            <?php endif; ?>

            <!-- Applications List -->
            <div class="applications-container">
                <?php if (empty($applications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>You haven't applied to any internships yet. Browse available offers and start applying today!</p>
                        <a href="../Controller/offerController.php?action=view" class="btn"> <!-- Relative Path -->
                            <i class="fas fa-search"></i> Browse Internship Offers
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                         <div class="application-card">
                             <!-- Application card content remains the same -->
                             <div class="application-header">
                                <?php
                                // Company logo logic
                                $companyLogoSrc = $defaultCompanyPic; // Uses relative path
                                if (!empty($app['company_picture_mime']) && !empty($app['company_picture'])) {
                                    $logoData = is_resource($app['company_picture']) ? stream_get_contents($app['company_picture']) : $app['company_picture'];
                                    if ($logoData) {
                                        $companyLogoSrc = 'data:' . htmlspecialchars($app['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                                    }
                                }
                                ?>
                                <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($app['name_company'] ?? 'Company') ?>" class="company-logo">
                                <h3><?= htmlspecialchars($app['internship_title'] ?? $app['title'] ?? 'Internship') ?></h3>
                            </div>

                            <div class="application-details">
                                <p><strong>Company:</strong> <span><?= htmlspecialchars($app['name_company'] ?? 'N/A') ?></span></p>
                                <p><strong>Location:</strong> <span><?= htmlspecialchars($app['company_location'] ?? 'N/A') ?></span></p>
                                <p><strong>Salary:</strong> <span><?= htmlspecialchars($app['remuneration'] ?? 'N/A') ?> €/month</span></p>
                                <!-- Duration removed -->
                                <p><strong>Applied on:</strong> <span><?= htmlspecialchars(date('F j, Y', strtotime($app['app_created_at'] ?? $app['created_at'] ?? 'now'))) ?></span></p>
                            </div>

                            <div class="application-status">
                                <?php
                                $status = $app['status'] ?? 'pending documents'; // Use actual status column name, default to your ENUM default
                                $statusIcon = 'file-alt'; // Default icon
                                $statusText = 'Pending Documents'; // Default text

                                // Map your specific enum values
                                switch (strtolower($status)) {
                                     case 'submitted': $statusIcon = 'paper-plane'; $statusText = 'Submitted'; break;
                                     case 'under review': $statusIcon = 'search'; $statusText = 'Under Review'; break;
                                     case 'accepted': $statusIcon = 'check-circle'; $statusText = 'Accepted'; break;
                                     case 'rejected': $statusIcon = 'times-circle'; $statusText = 'Rejected'; break;
                                     case 'withdrawn': $statusIcon = 'undo'; $statusText = 'Withdrawn'; break;
                                     case 'pending documents':
                                     default: $statusIcon = 'file-alt'; $statusText = 'Pending Documents'; break;
                                }
                                ?>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $status)) ?>">
                                    <i class="fas fa-<?= $statusIcon ?>"></i> <?= htmlspecialchars($statusText) ?>
                                </span>
                            </div>

                            <div class="application-content">
                                <div class="motivation-letter">
                                    <h4>Your Motivation Letter</h4>
                                    <div class="letter-content">
                                        <?= nl2br(htmlspecialchars($app['cover_letter'] ?? 'No motivation letter provided.')) ?>
                                    </div>
                                </div>

                                <?php if (!empty($app['cv'])): ?>
                                <div class="cv-info">
                                    <h4>Your CV</h4>
                                    <p><i class="fas fa-file-pdf"></i> CV Submitted (<?= htmlspecialchars(basename($app['cv'])) ?>)</p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($app['feedback'])): ?>
                                <div class="feedback">
                                    <h4>Feedback from Company</h4>
                                    <div class="feedback-content">
                                        <?= nl2br(htmlspecialchars($app['feedback'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <!-- Footer content remains the same -->
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
                <p>© <?= date('Y'); ?> Navigui - Student Internship Platform</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // JS remains the same
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            const currentTheme = localStorage.getItem('theme') || 'light'; // Default to light
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'dark') { themeToggle.checked = true; }
            themeToggle.addEventListener('change', function() {
                const newTheme = this.checked ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });
            // Mobile Menu Toggle
            const mobileToggle = document.getElementById('mobile-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const navContainer = document.querySelector('.nav-container');
            if (mobileToggle && mobileMenu) {
                mobileToggle.addEventListener('click', function(event) { event.stopPropagation(); mobileMenu.classList.toggle('active'); mobileToggle.classList.toggle('active'); });
                document.addEventListener('click', function(event) {
                     const isClickInsideMenu = mobileMenu.contains(event.target);
                     const isClickInsideNav = navContainer ? navContainer.contains(event.target) : false;
                     if (mobileMenu.classList.contains('active') && !isClickInsideMenu && !isClickInsideNav) { mobileMenu.classList.remove('active'); mobileToggle.classList.remove('active'); }
                });
            }
             // User Dropdown Toggle
             const userAvatar = document.querySelector('.user-avatar');
             const dropdownMenu = document.querySelector('.dropdown-menu');
             if (userAvatar && dropdownMenu) {
                userAvatar.addEventListener('click', function(event) { event.stopPropagation(); dropdownMenu.classList.toggle('active'); });
                document.addEventListener('click', function(event) { if (dropdownMenu.classList.contains('active') && !dropdownMenu.contains(event.target) && event.target !== userAvatar) { dropdownMenu.classList.remove('active'); } });
            }
            // Auto hide success/error messages
            const messages = document.querySelectorAll('.message.success-message, .message.error-message');
            messages.forEach(message => { setTimeout(() => { message.style.transition = 'opacity 0.5s ease'; message.style.opacity = '0'; setTimeout(() => { message.style.display = 'none'; }, 500); }, 5000); });
        });
    </script>
</body>
</html>
