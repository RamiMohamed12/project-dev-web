<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Navigui</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/css/stud.application.css">
    <style>
       
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container bento-card">
                <!-- Logo -->
                <a href="../View/student.php" class="logo">
                    <div class="logo-image-container">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="logo-text">Navigui</span>
                </a>

                <!-- Menu Items -->
                <div class="menu-items">
                    <a href="../View/student.php" class="menu-item">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item active">Applications</a>
                    <?php if (isset($loggedInUserId)): ?>
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student" class="menu-item">Profile</a>
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
                        $userProfilePicSrc = isset($profilePicSrc) ? $profilePicSrc : '../View/images/default_avatar.png';
                        $userName = isset($displayName) ? $displayName : 'Student';
                        $userEmail = isset($displayEmail) ? $displayEmail : '';
                        ?>
                        <img src="<?= $userProfilePicSrc ?>" alt="Profile" class="user-avatar">
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <img src="<?= $userProfilePicSrc ?>" alt="Profile" class="dropdown-avatar">
                                <div class="dropdown-user-info">
                                    <div class="dropdown-user-name"><?= $userName ?></div>
                                    <div class="dropdown-user-email"><?= $userEmail ?></div>
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
                    <a href="../View/student.php" class="mobile-menu-item">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="mobile-menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="mobile-menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="mobile-menu-item active">Applications</a>
                    <?php if (isset($loggedInUserId)): ?>
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student" class="mobile-menu-item">Profile</a>
                    <?php endif; ?>
                    <a href="../Controller/logoutController.php" class="mobile-menu-item" style="color: #ef4444;">Logout <i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    </header>

    <main>
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
            <a href="../View/student.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
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

            <!-- Define default company picture -->
            <?php $defaultCompanyPic = '../View/images/default_company.png'; ?>

            <!-- Applications List -->
            <div class="applications-container">
                <?php if (empty($applications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>You haven't applied to any internships yet. Browse available offers and start applying today!</p>
                        <a href="../Controller/offerController.php?action=view" class="btn">
                            <i class="fas fa-search"></i> Browse Internship Offers
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <div class="application-card">
                            <div class="application-header">
                                <?php 
                                $companyLogoSrc = $defaultCompanyPic;
                                if (!empty($app['company_picture']) && !empty($app['company_picture_mime'])) {
                                    $logoData = is_resource($app['company_picture']) ? stream_get_contents($app['company_picture']) : $app['company_picture'];
                                    if ($logoData) {
                                        $companyLogoSrc = 'data:' . htmlspecialchars($app['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                                    }
                                }
                                ?>
                                <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($app['name_company'] ?? 'Company') ?>" class="company-logo">
                                <h3><?= htmlspecialchars($app['title'] ?? 'Internship') ?></h3>
                            </div>
                            
                            <div class="application-details">
                                <p><strong>Company:</strong> <span><?= htmlspecialchars($app['name_company'] ?? 'N/A') ?></span></p>
                                <p><strong>Location:</strong> <span><?= htmlspecialchars($app['company_location'] ?? 'N/A') ?></span></p>
                                <p><strong>Salary:</strong> <span><?= htmlspecialchars($app['remuneration'] ?? 'N/A') ?> €/month</span></p>
                                <p><strong>Duration:</strong> <span><?= htmlspecialchars($app['duration'] ?? 'N/A') ?> months</span></p>
                                <p><strong>Applied on:</strong> <span><?= htmlspecialchars(date('F j, Y', strtotime($app['created_at'] ?? date('Y-m-d')))) ?></span></p>
                            </div>
                            
                            <div class="application-status">
                                <?php 
                                $status = $app['status'] ?? 'pending';
                                $statusIcon = 'clock';
                                $statusText = 'Pending';
                                
                                if ($status === 'accepted') {
                                    $statusIcon = 'check-circle';
                                    $statusText = 'Accepted';
                                } elseif ($status === 'rejected') {
                                    $statusIcon = 'times-circle';
                                    $statusText = 'Rejected';
                                }
                                ?>
                                <span class="status-badge status-<?= strtolower($status) ?>">
                                    <i class="fas fa-<?= $statusIcon ?>"></i> <?= $statusText ?>
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
                                    <p><?= htmlspecialchars($app['cv']) ?></p>
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
        });
    </script>
</body>
</html>