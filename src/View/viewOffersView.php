<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Offers - Navigui</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../View/css/stud_offre.css">
    <style>
       
    </style>
</head>
<body>
    <?php
    // Define the default company picture path
    $defaultCompanyPic = '../View/images/default_company.png';
    ?>
    
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
                    <a href="../Controller/offerController.php?action=view" class="menu-item active">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item">Applications</a>
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
                    <a href="../Controller/offerController.php?action=view" class="mobile-menu-item active">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="mobile-menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="mobile-menu-item">Applications</a>
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
                    <i class="fas fa-briefcase"></i>
                </div>
                <h1 class="page-title">Available Internship Offers</h1>
                <p class="page-subtitle">Find the perfect opportunity to kickstart your career</p>
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

            <!-- Search & Filter Section -->
            <div class="search-container">
                <form method="get" action="../Controller/offerController.php" class="search-form">
                    <input type="hidden" name="action" value="view">

                    <div class="search-group">
                        <input type="text" name="search" placeholder="Search by title, company, or location"
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit" class="btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>

                    <div class="filter-group">
                        <select name="sort">
                            <option value="newest" <?= (($_GET['sort'] ?? '') === 'newest') ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= (($_GET['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                        </select>

                        <button type="submit" class="filter-button">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        
                        <a href="../Controller/offerController.php?action=view" class="reset-button">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Internship Offers -->
            <div class="offers-container">
                <?php if (empty($internships)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>No internship offers found. Please check back later or adjust your search criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($internships as $internship): ?>
                        <?php
                            // Extract rating info passed from controller
                            $ratingInfo = $internship['rating_info'] ?? ['average' => null, 'count' => 0];
                            $studentHasRated = $internship['student_has_rated'] ?? false; // Default to false if not set
                            $companyId = $internship['id_company'] ?? null; // Get company ID
                        ?>
                        <div class="offer-card">
                            <div class="offer-header">
                                <?php
                                $companyLogoSrc = $defaultCompanyPic;
                                if (!empty($internship['company_picture_mime']) && !empty($internship['company_picture'])) {
                                    $logoData = is_resource($internship['company_picture']) ? stream_get_contents($internship['company_picture']) : $internship['company_picture'];
                                    if ($logoData) {
                                        $companyLogoSrc = 'data:' . htmlspecialchars($internship['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                                    }
                                }
                                ?>
                                <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($internship['name_company'] ?? 'Company') ?>" class="company-logo">
                                <h3><?= htmlspecialchars($internship['title'] ?? 'Internship') ?></h3>
                            </div>

                            <div class="offer-details">
                                <p><strong>Company:</strong> <span><?= htmlspecialchars($internship['name_company'] ?? 'N/A') ?></span></p>
                                
                                <!-- Company Rating Display -->
                                <div class="star-rating">
                                    <?php if ($ratingInfo['average'] !== null): ?>
                                        <?= displayStars($ratingInfo['average']) ?>
                                        <span class="rating-count">(<?= number_format($ratingInfo['average'], 1) ?>/5 from <?= $ratingInfo['count'] ?> review<?= $ratingInfo['count'] !== 1 ? 's' : '' ?>)</span>
                                    <?php else: ?>
                                        <i class="fa-regular fa-star star-empty"></i>
                                        <i class="fa-regular fa-star star-empty"></i>
                                        <i class="fa-regular fa-star star-empty"></i>
                                        <i class="fa-regular fa-star star-empty"></i>
                                        <i class="fa-regular fa-star star-empty"></i>
                                        <span class="rating-count">(No reviews yet)</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p><strong>Location:</strong> <span><?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></span></p>
                                <p><strong>Duration:</strong> <span><?= htmlspecialchars($internship['duration'] ?? 'N/A') ?> months</span></p>
                                <p><strong>Salary:</strong> <span><?= htmlspecialchars($internship['remuneration'] ?? 'N/A') ?> €/month</span></p>
                                <p><strong>Date Posted:</strong> <span><?= htmlspecialchars(date('M d, Y', strtotime($internship['offre_date'] ?? 'now'))) ?></span></p>
                            </div>

                            <div class="offer-description">
                                <p><?= nl2br(htmlspecialchars($internship['description'])) ?></p>
                            </div>

                            <div class="offer-actions">
                                <a href="../Controller/applicationController.php?action=apply&id=<?= $internship['id_internship'] ?>" class="btn">
                                    <i class="fas fa-paper-plane"></i> Apply Now
                                </a>

                                <?php
                                // Wishlist Button
                                $isInWishlist = $wishlistModel->isInWishlist($loggedInUserId, $internship['id_internship']);
                                if ($isInWishlist):
                                ?>
                                    <a href="../Controller/wishlistController.php?action=remove&id=<?= $internship['id_internship'] ?>" class="btn-secondary">
                                        <i class="fas fa-heart-broken"></i> Remove from Wishlist
                                    </a>
                                <?php else: ?>
                                    <a href="../Controller/wishlistController.php?action=add&id=<?= $internship['id_internship'] ?>" class="btn-secondary">
                                        <i class="fas fa-heart"></i> Add to Wishlist
                                    </a>
                                <?php endif; ?>
                            </div>

                            <!-- Rate Company Section -->
                            <?php if ($companyId && $loggedInUserId): ?>
                                <div class="rate-company-section">
                                    <?php if ($studentHasRated): ?>
                                        <p><i class="fas fa-check-circle"></i> You have already rated this company.</p>
                                    <?php else: ?>
                                        <h4><i class="fas fa-star"></i> Rate this Company</h4>
                                        <form action="../Controller/companyController.php?action=rate" method="post" class="rating-form">
                                            <input type="hidden" name="company_id" value="<?= $companyId ?>">
                                            <input type="hidden" name="student_id" value="<?= $loggedInUserId ?>">
                                            
                                            <div class="star-input">
                                                <input type="radio" id="star5_<?= $companyId ?>" name="rating_value" value="5" required><label for="star5_<?= $companyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star4_<?= $companyId ?>" name="rating_value" value="4"><label for="star4_<?= $companyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star3_<?= $companyId ?>" name="rating_value" value="3"><label for="star3_<?= $companyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star2_<?= $companyId ?>" name="rating_value" value="2"><label for="star2_<?= $companyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star1_<?= $companyId ?>" name="rating_value" value="1"><label for="star1_<?= $companyId ?>"><i class="fas fa-star"></i></label>
                                            </div>
                                            
                                            <textarea name="comment" placeholder="Share your experience with this company (optional)" rows="2"></textarea>
                                            
                                            <button type="submit" class="btn btn-small">
                                                <i class="fas fa-paper-plane"></i> Submit Rating
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
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