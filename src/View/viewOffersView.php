<?php
// Include the helper functions file
// Use __DIR__ to ensure the path is relative to the current file's directory
require_once __DIR__ . '/helpers/view_helpers.php'; // Adjust path if you placed it elsewhere

// Define default picture paths
$defaultCompanyPic = '../View/images/default_company.png';
$defaultUserPic = '../View/images/default_avatar.png'; // Default user avatar

// --- Fetch User Details for Profile Display (Copied & Adapted from wishlistView.php) ---
$userProfilePicSrc = $defaultUserPic; // Start with default
$userName = 'Student';                // Default name
$userEmail = '';                      // Default email

// Check if details are needed (user is logged in) and prerequisites are met (db connection assumed available)
// This block tries to fetch user details if they weren't explicitly passed by the controller.
// Ideally, the controller should handle fetching and passing this data.
if (isset($loggedInUserId) && isset($conn)) { // Added check for $conn (needs to be passed by controller)
    try {
        // Ensure the User model is included only once if potentially included elsewhere
        if (!class_exists('User')) {
             require_once __DIR__ . '/../Model/user.php'; // Adjust path if needed
        }
        $userModel = new User($conn); // Assumes $conn is a valid PDO connection
        $userDetails = $userModel->readStudent($loggedInUserId); // Fetch student data

        if ($userDetails) {
            // Assign to the variables used in the HTML header
            $userName = htmlspecialchars($userDetails['name']);
            $userEmail = htmlspecialchars($userDetails['email']);

            // Check for profile picture data
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                // Handle potential resource stream from database
                $picData = is_resource($userDetails['profile_picture']) ?
                    stream_get_contents($userDetails['profile_picture']) :
                    $userDetails['profile_picture'];

                // If picture data is successfully retrieved, create the data URI
                if ($picData) {
                    $userProfilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) .
                        ';base64,' . base64_encode($picData);
                }
                // If pic data exists but is invalid/unreadable, $userProfilePicSrc retains the $defaultUserPic
            }
            // If no picture data in DB, $userProfilePicSrc retains the $defaultUserPic
        }
         // If userDetails not found for the loggedInUserId, defaults remain

    } catch (Exception $e) {
        // Log the error, but don't break the page; defaults will be used.
        error_log("Error fetching student details for offers view header (ID: $loggedInUserId): " . $e->getMessage());
        // Ensure defaults are still set in case of error during fetch
        $userProfilePicSrc = $defaultUserPic;
        $userName = 'Student';
        $userEmail = '';
    }
}
// --- End Fetch User Details ---

?>
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
       /* --- Star Rating Styles --- */
       .star-rating i {
           color: #f8b400; /* Gold color for stars */
           margin-right: 2px; /* Spacing between stars */
       }
       .star-rating .star-empty {
           color: #ccc; /* Grey color for empty stars */
       }
       .rating-form .star-input {
           display: inline-block; /* Align stars horizontally */
           direction: rtl; /* Makes stars fill right-to-left */
       }
       .rating-form .star-input input[type="radio"] {
           display: none; /* Hide the actual radio buttons */
       }
       .rating-form .star-input label {
           color: #ccc; /* Default empty star color */
           font-size: 1.5em; /* Adjust star size */
           padding: 0 2px;
           cursor: pointer;
           transition: color 0.2s;
       }
       .rating-form .star-input input[type="radio"]:checked ~ label {
           color: #f8b400; /* Color stars up to the selected one */
       }
       .rating-form .star-input label:hover,
       .rating-form .star-input label:hover ~ label {
           color: #f8b400; /* Color stars on hover */
       }

       /* --- FIX: Offer Actions Button Layout --- */
       .offer-actions {
            display: flex; /* Use flexbox to arrange buttons */
            flex-wrap: wrap; /* Allow buttons to wrap onto the next line if space is tight */
            gap: 10px; /* Add space between buttons */
            margin-top: 15px; /* Space above the action buttons */
            align-items: center; /* Vertically align items if they wrap */
       }

       .offer-actions .btn,
       .offer-actions .btn-secondary {
            padding: 6px 12px; /* Slightly reduced padding */
            font-size: 0.9rem; /* Slightly smaller font size */
            text-align: center; /* Ensure text is centered */
            margin: 0; /* Reset margin if buttons had any default */
            display: inline-flex;
            align-items: center;
            justify-content: center;
       }

       .offer-actions .btn i,
       .offer-actions .btn-secondary i {
           margin-right: 5px; /* Consistent spacing for icons */
       }
       /* --- End FIX --- */

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
                    <a href="../Controller/offerController.php?action=view" class="menu-item active">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item">Applications</a>
                    <?php if (isset($loggedInUserId)): ?>
                    <a href="../Controller/editUser.php?id=<?= htmlspecialchars((string)$loggedInUserId) ?>&type=student" class="menu-item">Profile</a>
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
                        // Variables ($userProfilePicSrc, $userName, $userEmail) are now prepared by the PHP block above
                        ?>
                        <img src="<?= htmlspecialchars($userProfilePicSrc) ?>" alt="Profile" class="user-avatar">
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <img src="<?= htmlspecialchars($userProfilePicSrc) ?>" alt="Profile" class="dropdown-avatar">
                                <div class="dropdown-user-info">
                                    <div class="dropdown-user-name"><?= htmlspecialchars($userName) ?></div>
                                    <div class="dropdown-user-email"><?= htmlspecialchars($userEmail) ?></div>
                                </div>
                            </div>
                            <div class="dropdown-items">
                                <a href="../Controller/editUser.php?id=<?= htmlspecialchars((string)$loggedInUserId) ?>&type=student" class="dropdown-item">
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
                    <a href="../Controller/editUser.php?id=<?= htmlspecialchars((string)$loggedInUserId) ?>&type=student" class="mobile-menu-item">Profile</a>
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
                            $internshipId = $internship['id_internship'] ?? null; // Get internship ID
                            $safeInternshipId = $internshipId ? htmlspecialchars((string)$internshipId) : '';
                            $safeCompanyId = $companyId ? htmlspecialchars((string)$companyId) : '';
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
                                    <?php if ($ratingInfo['average'] !== null && is_numeric($ratingInfo['average'])): ?>
                                        <?= displayStars((float)$ratingInfo['average']) // Call the defined function. Cast to float for safety. ?>
                                        <span class="rating-count">(<?= number_format((float)$ratingInfo['average'], 1) ?>/5 from <?= htmlspecialchars((string)($ratingInfo['count'] ?? 0)) ?> review<?= ($ratingInfo['count'] ?? 0) !== 1 ? 's' : '' ?>)</span>
                                    <?php else: ?>
                                        <?= displayStars(0) // Display 0 stars if no rating ?>
                                        <span class="rating-count">(No reviews yet)</span>
                                    <?php endif; ?>
                                </div>

                                <p><strong>Location:</strong> <span><?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></span></p>
                                <p><strong>Duration:</strong> <span><?= htmlspecialchars($internship['duration'] ?? 'N/A') ?> months</span></p>
                                <p><strong>Salary:</strong> <span><?= htmlspecialchars($internship['remuneration'] ?? 'N/A') ?> €/month</span></p>
                                <p><strong>Date Posted:</strong> <span><?= htmlspecialchars(date('M d, Y', strtotime($internship['offre_date'] ?? 'now'))) ?></span></p>
                            </div>

                            <div class="offer-description">
                                <p><?= nl2br(htmlspecialchars($internship['description'] ?? '')) ?></p>
                            </div>

                            <div class="offer-actions">
                                <?php if ($internshipId): ?>
                                    <a href="../Controller/applicationController.php?action=apply&id=<?= $safeInternshipId ?>" class="btn">
                                        <i class="fas fa-paper-plane"></i> Apply Now
                                    </a>
                                <?php endif; ?>

                                <?php
                                // Ensure $wishlistModel is available in the view scope
                                // It should be passed from the controller or instantiated if necessary
                                // For this example, assuming $wishlistModel is correctly passed.
                                if (isset($wishlistModel) && isset($loggedInUserId) && $internshipId):
                                    $isInWishlist = $wishlistModel->isInWishlist($loggedInUserId, $internshipId);
                                    if ($isInWishlist):
                                ?>
                                    <a href="../Controller/wishlistController.php?action=remove&id=<?= $safeInternshipId ?>" class="btn-secondary">
                                        <i class="fas fa-heart-broken"></i> Remove from Wishlist
                                    </a>
                                <?php else: ?>
                                    <a href="../Controller/wishlistController.php?action=add&id=<?= $safeInternshipId ?>" class="btn-secondary">
                                        <i class="fas fa-heart"></i> Add to Wishlist
                                    </a>
                                <?php endif;
                                      endif; // End check for wishlistModel, loggedInUserId, internshipId ?>
                            </div>

                            <!-- Rate Company Section -->
                            <?php if ($companyId && isset($loggedInUserId)): ?>
                                <div class="rate-company-section">
                                    <?php if ($studentHasRated): ?>
                                        <p><i class="fas fa-check-circle"></i> You have already rated this company.</p>
                                    <?php else: ?>
                                        <h4><i class="fas fa-star"></i> Rate this Company</h4>
                                        <form action="../Controller/companyController.php?action=rate" method="post" class="rating-form">
                                            <input type="hidden" name="company_id" value="<?= $safeCompanyId ?>">
                                            <input type="hidden" name="student_id" value="<?= htmlspecialchars((string)$loggedInUserId) ?>">
                                             <!-- Add a redirect URL to come back here after rating -->
                                            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                                            <div class="star-input">
                                                <!-- Note: IDs should be unique on the page, using companyId ensures this -->
                                                <input type="radio" id="star5_<?= $safeCompanyId ?>" name="rating_value" value="5" required><label for="star5_<?= $safeCompanyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star4_<?= $safeCompanyId ?>" name="rating_value" value="4"><label for="star4_<?= $safeCompanyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star3_<?= $safeCompanyId ?>" name="rating_value" value="3"><label for="star3_<?= $safeCompanyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star2_<?= $safeCompanyId ?>" name="rating_value" value="2"><label for="star2_<?= $safeCompanyId ?>"><i class="fas fa-star"></i></label>
                                                <input type="radio" id="star1_<?= $safeCompanyId ?>" name="rating_value" value="1"><label for="star1_<?= $safeCompanyId ?>"><i class="fas fa-star"></i></label>
                                            </div>

                                            <textarea name="comment" placeholder="Share your experience with this company (optional)" rows="2"></textarea>

                                            <button type="submit" class="btn btn-small"> <!-- Added btn-small for consistency if needed -->
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
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            const currentTheme = localStorage.getItem('theme') || 'light'; // Default to light

            document.documentElement.setAttribute('data-theme', currentTheme);
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
            const navContainer = document.querySelector('.nav-container'); // To prevent clicks inside nav closing menu

            if (mobileToggle && mobileMenu) {
                mobileToggle.addEventListener('click', function(event) {
                    event.stopPropagation(); // Prevent click from bubbling up to document
                    mobileMenu.classList.toggle('active');
                    mobileToggle.classList.toggle('active');
                });

                // Close menu if clicking outside of it
                document.addEventListener('click', function(event) {
                    const isClickInsideMenu = mobileMenu.contains(event.target);
                    const isClickInsideNav = navContainer ? navContainer.contains(event.target) : false;

                    // Close only if the menu is active and the click is outside the menu AND outside the nav container (where the toggle button lives)
                    if (mobileMenu.classList.contains('active') && !isClickInsideMenu && !isClickInsideNav) {
                        mobileMenu.classList.remove('active');
                        mobileToggle.classList.remove('active');
                    }
                });
            }

            // Auto hide success/error messages after 5 seconds
            const messages = document.querySelectorAll('.message.success-message, .message.error-message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s ease';
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.style.display = 'none';
                    }, 500); // Wait for transition to finish
                }, 5000); // 5 seconds
            });

             // User Dropdown Toggle
             const userAvatar = document.querySelector('.user-avatar');
             const dropdownMenu = document.querySelector('.dropdown-menu');

             if (userAvatar && dropdownMenu) {
                userAvatar.addEventListener('click', function(event) {
                    event.stopPropagation(); // Prevent document click listener from closing it immediately
                    dropdownMenu.classList.toggle('active');
                });

                // Close dropdown if clicking outside
                document.addEventListener('click', function(event) {
                    if (dropdownMenu.classList.contains('active') && !dropdownMenu.contains(event.target) && event.target !== userAvatar) {
                        dropdownMenu.classList.remove('active');
                    }
                });
            }

        });
    </script>
</body>
</html>
