<?php
// File: src/View/applicationFormView.php

// Assume necessary variables are passed from the controller:
// $loggedInUserId, $conn (crucial for pic fetch!),
// $internshipDetails (array), $pageTitle (string),
// $errorMessage (string), $successMessage (string),
// $_POST data might be available if form submitted with errors

// --- Define default paths/values ---
$defaultCompanyPic = '../View/images/default_company.png'; // Relative path
$defaultAvatar = '../View/images/default_avatar.png';    // Relative path

// --- Initialize User Details ---
$profilePicSrc = null;      // Initialize to null
$displayName = 'Student';   // Default name
$displayEmail = '';         // Default email

// --- Fetch User Details for Header ---
// This block relies on $loggedInUserId and $conn being passed from the controller
if (isset($loggedInUserId) && isset($conn)) {
    try {
        require_once __DIR__ . '/../Model/user.php'; // Ensure User model is loaded
        $userModel = new User($conn);
        $userDetails = $userModel->readStudent($loggedInUserId);

        if ($userDetails) {
            $displayName = htmlspecialchars($userDetails['name'] ?? $displayName);
            $displayEmail = htmlspecialchars($userDetails['email'] ?? $displayEmail);
            if (!empty($userDetails['profile_picture_mime']) && !empty($userDetails['profile_picture'])) {
                 $picData = is_resource($userDetails['profile_picture']) ? stream_get_contents($userDetails['profile_picture']) : $userDetails['profile_picture'];
                if ($picData) {
                    // Set the data URI - DO NOT htmlspecialchars the whole thing here
                    $profilePicSrc = 'data:' . htmlspecialchars($userDetails['profile_picture_mime']) . ';base64,' . base64_encode($picData);
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching student details for application form header (ID: $loggedInUserId): " . $e->getMessage());
        // $profilePicSrc remains null, default path will be used via ??
    }
} else {
     // Log if prerequisites missing
     if (!isset($loggedInUserId)) error_log("applicationFormView: Missing loggedInUserId for profile pic fetch.");
     if (!isset($conn)) error_log("applicationFormView: Missing \$conn for profile pic fetch.");
}
// --- End Fetch User Details ---

// Prepare other view variables
$safeLoggedInUserId = isset($loggedInUserId) ? htmlspecialchars((string)$loggedInUserId) : '';
$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Apply for Internship';
$internshipTitle = htmlspecialchars($internshipDetails['title'] ?? 'Internship Offer');
$companyName = htmlspecialchars($internshipDetails['name_company'] ?? 'N/A');
$companyLocation = htmlspecialchars($internshipDetails['company_location'] ?? 'N/A'); // Assuming location is available
$salary = htmlspecialchars($internshipDetails['remuneration'] ?? 'N/A');
// Duration was removed from DB previously, ensure it's not used or add it back if needed
// $duration = htmlspecialchars($internshipDetails['duration'] ?? 'N/A');
$internshipId = htmlspecialchars((string)($internshipDetails['id_internship'] ?? ''));

// Company Logo Logic (using relative default path)
$companyLogoSrc = $defaultCompanyPic;
if (!empty($internshipDetails['company_picture_mime']) && !empty($internshipDetails['company_picture'])) {
    $logoData = is_resource($internshipDetails['company_picture']) ? stream_get_contents($internshipDetails['company_picture']) : $internshipDetails['company_picture'];
    if ($logoData) {
        $companyLogoSrc = 'data:' . htmlspecialchars($internshipDetails['company_picture_mime']) . ';base64,' . base64_encode($logoData);
    }
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Navigui</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Use relative path for CSS -->
    <link rel="stylesheet" href="../View/css/stud_offre.css"> <!-- Assuming this CSS works -->
    <style>
        /* --- Application Form Specific Styles --- */
        /* Enhanced Internship Summary */
        .internship-summary-card { background-color: var(--card-bg-color); border-radius: var(--card-border-radius); padding: 1.5rem 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); display: grid; grid-template-columns: auto 1fr; gap: 1rem 1.5rem; align-items: center; }
        .internship-summary-card .company-logo-wrapper { grid-row: 1 / span 2; width: 70px; height: 70px; border-radius: 50%; overflow: hidden; border: 2px solid var(--border-color-light); display: flex; justify-content: center; align-items: center; background-color: #fff; }
        .internship-summary-card .company-logo-wrapper img { width: 100%; height: 100%; object-fit: contain; }
        .internship-summary-card h3 { margin: 0; font-size: 1.5rem; font-weight: 600; color: var(--text-primary); grid-column: 2; align-self: end; }
        .internship-summary-details { grid-column: 2; align-self: start; display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 0.25rem; color: var(--text-secondary); }
        .internship-summary-details p { margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem; }
        .internship-summary-details i { color: var(--primary-color); width: 1em; text-align: center; }
        /* Enhanced Form Styling */
        .application-form-container { background-color: var(--card-bg-color); border-radius: var(--card-border-radius); padding: 2rem; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); margin-top: 1rem; }
        .application-form-container h3 { font-size: 1.3rem; font-weight: 600; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color-light); color: var(--text-primary); display: flex; align-items: center; gap: 0.75rem; }
        .application-form-container h3 i { color: var(--primary-color); }
        .application-form .form-group { margin-bottom: 1.75rem; }
        .application-form label { display: block; margin-bottom: 0.6rem; font-weight: 500; color: var(--text-primary); font-size: 1.05rem; }
        .application-form input[type="file"], .application-form textarea { width: 100%; padding: 0.8rem 1rem; border: 1px solid var(--input-border-color); border-radius: var(--input-border-radius); background-color: var(--input-bg-color); color: var(--input-text-color); font-size: 1rem; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .application-form textarea { min-height: 180px; resize: vertical; }
        .form-group.file-input-group { position: relative; }
        .file-input-styled { display: inline-flex; align-items: center; padding: 0.6rem 1.2rem; background-color: var(--primary-color-light); color: var(--primary-color); border: 1px dashed var(--primary-color); border-radius: var(--input-border-radius); cursor: pointer; transition: background-color 0.2s ease; font-weight: 500; }
        .file-input-styled i { margin-right: 0.75rem; font-size: 1.1em; } .file-input-styled:hover { background-color: var(--primary-color-lighter); }
        .file-input-group input[type="file"] { position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
        #file-chosen { margin-left: 1rem; font-style: italic; color: var(--text-muted); font-size: 0.9rem; }
        .application-form input:focus, .application-form textarea:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px var(--primary-focus-ring); }
        .application-form .form-hint { font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem; }
        .application-form .form-actions { margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid var(--border-color-light); padding-top: 1.5rem; }
        .form-actions .btn { padding: 0.8rem 1.8rem; font-size: 1.05rem; font-weight: 500; } .form-actions .btn i { margin-right: 0.5rem; }
        .form-actions .btn-secondary { background-color: var(--secondary-button-bg); color: var(--secondary-button-text); border: 1px solid var(--secondary-button-border); }
        .form-actions .btn-secondary:hover { background-color: var(--secondary-button-hover-bg); border-color: var(--secondary-button-hover-border); }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .message { animation: fadeInDown 0.3s ease-out; }
        /* Styles for back link */
        .back-link { display: inline-block; margin-bottom: 1rem; color: var(--primary-color); text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 0.3rem; }
    </style>
</head>
<body>

    <!-- Consistent Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container bento-card">
                <!-- Use relative paths -->
                <a href="../View/student.php" class="logo">
                    <div class="logo-image-container"><i class="fas fa-graduation-cap"></i></div>
                    <span class="logo-text">Navigui</span>
                </a>
                <div class="menu-items">
                    <a href="../View/student.php" class="menu-item">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a>
                    <!-- Highlight Applications differently or remove active state here -->
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item">Applications</a>
                    <?php if ($safeLoggedInUserId): ?>
                    <a href="../Controller/editUser.php?id=<?= $safeLoggedInUserId ?>&type=student" class="menu-item">Profile</a>
                    <?php endif; ?>
                </div>
                <div class="nav-right">
                    <label class="switch">
                        <input type="checkbox" id="themeToggle"><span class="slider"></span>
                    </label>
                    <?php if ($safeLoggedInUserId): ?>
                    <div class="user-dropdown">
                        <!-- Use ?? operator for fallback -->
                        <img src="<?= $profilePicSrc ?? $defaultAvatar ?>" alt="Profile" class="user-avatar">
                        <div class="dropdown-menu">
                            <div class="dropdown-header">
                                <img src="<?= $profilePicSrc ?? $defaultAvatar ?>" alt="Profile" class="dropdown-avatar">
                                <div class="dropdown-user-info">
                                    <div class="dropdown-user-name"><?= $displayName /* Already escaped */ ?></div>
                                    <div class="dropdown-user-email"><?= $displayEmail /* Already escaped */ ?></div>
                                </div>
                            </div>
                            <div class="dropdown-items">
                                <!-- Use relative paths -->
                                <a href="../Controller/editUser.php?id=<?= $safeLoggedInUserId ?>&type=student" class="dropdown-item"><i class="fas fa-user-edit"></i><span>Edit Profile</span></a>
                                <a href="../Controller/applicationController.php?action=myapps" class="dropdown-item"><i class="fas fa-file-alt"></i><span>My Applications</span></a>
                                <a href="../Controller/wishlistController.php?action=view" class="dropdown-item"><i class="fas fa-heart"></i><span>My Wishlist</span></a>
                                <a href="../Controller/logoutController.php" class="dropdown-item logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <button class="hamburger-menu" id="mobile-toggle" aria-label="Menu"><span></span><span></span><span></span></button>
                </div>
            </div>
            <div class="mobile-menu" id="mobile-menu">
                 <!-- Use relative paths -->
                <div class="mobile-menu-content">
                    <a href="../View/student.php" class="mobile-menu-item">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="mobile-menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="mobile-menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="mobile-menu-item">Applications</a>
                    <?php if ($safeLoggedInUserId): ?>
                    <a href="../Controller/editUser.php?id=<?= $safeLoggedInUserId ?>&type=student" class="mobile-menu-item">Profile</a>
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
                    <i class="fas fa-paper-plane"></i> <!-- Use apply icon -->
                </div>
                <h1 class="page-title"><?= $pageTitle ?></h1>
                <p class="page-subtitle">Review the details and submit your application.</p>
            </div>
        </section>

        <div class="container">
             <a href="javascript:history.back()" class="back-link"> <!-- Go Back link -->
                 <i class="fas fa-chevron-left"></i> Go Back to Offer List
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

            <?php if (!empty($internshipDetails) && !empty($internshipId)): // Check ID too ?>

                <!-- Internship Summary -->
                <div class="internship-summary-card">
                     <div class="company-logo-wrapper">
                          <img src="<?= $companyLogoSrc ?>" alt="<?= $companyName ?> Logo" class="company-logo">
                     </div>
                     <h3><?= $internshipTitle ?></h3>
                    <div class="internship-summary-details">
                        <p><i class="fas fa-building"></i> <?= $companyName ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= $companyLocation ?></p>
                        <p><i class="fas fa-euro-sign"></i> <?= $salary ?> / month</p>
                        <!-- Duration removed -->
                        <!-- <p><i class="far fa-clock"></i> <?= $duration ?> months</p> -->
                    </div>
                </div>

                <!-- Application Form -->
                <div class="application-form-container bento-card">
                     <form method="post" action="../Controller/applicationController.php?action=submit" enctype="multipart/form-data" class="application-form">
                        <input type="hidden" name="internship_id" value="<?= $internshipId ?>">
                        <!-- Redirect URLs removed, controller handles redirects -->

                         <!-- Section 1: Motivation -->
                         <h3><i class="fas fa-pen-fancy"></i> Your Motivation</h3>
                        <div class="form-group">
                            <label for="motivation_letter">Motivation Letter</label>
                            <textarea id="motivation_letter" name="motivation_letter" rows="10" required placeholder="Tell <?= $companyName ?> why you're excited about this opportunity..."><?= htmlspecialchars($_POST['motivation_letter'] ?? '') ?></textarea>
                            <p class="form-hint">Explain your interest and suitability for the role.</p>
                        </div>

                        <!-- Section 2: CV -->
                         <h3><i class="fas fa-file-alt"></i> Attach Your CV</h3>
                        <div class="form-group file-input-group">
                            <label for="cv_file">Upload CV (PDF, DOC, DOCX - Max 5MB)</label>
                            <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" aria-hidden="true">
                            <label for="cv_file" class="file-input-styled">
                               <i class="fas fa-upload"></i> Choose File...
                            </label>
                             <span id="file-chosen">No file chosen</span>
                            <p class="form-hint">Optional. Ensure your CV is up-to-date.</p>
                        </div>

                        <!-- Section 3: Submit -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"> <!-- Changed class to primary -->
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                            <a href="javascript:history.back()" class="btn btn-secondary"> <!-- Added secondary class -->
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Could not load internship details. Please go back to the offers page and try again.</span>
                    <a href="../Controller/offerController.php?action=view" class="btn btn-sm btn-secondary ms-3">View Offers</a>
                </div>
            <?php endif; ?>

        </div> <!-- /.container -->
    </main>

    <!-- Consistent Footer -->
    <footer class="footer">
        <!-- Footer content remains the same -->
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">
                        <div class="logo-image-container"><i class="fas fa-graduation-cap"></i></div>
                        <span>Navigui</span>
                    </div>
                    <p>Your partner in finding the perfect internship match.</p>
                     <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                 <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="../Controller/offerController.php?action=view">Browse Internships</a></li>
                        <li><a href="../Controller/applicationController.php?action=myapps">My Applications</a></li>
                        <li><a href="../Controller/wishlistController.php?action=view">My Wishlist</a></li>
                         <?php if ($safeLoggedInUserId): ?>
                         <li><a href="../Controller/editUser.php?id=<?= $safeLoggedInUserId ?>&type=student">My Profile</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                 <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">Career Advice</a></li>
                        <li><a href="#">Resume Tips</a></li>
                        <li><a href="#">Interview Prep</a></li>
                        <li><a href="#">Company Reviews</a></li>
                    </ul>
                </div>
                 <div class="footer-column">
                    <h3>Contact Us</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-envelope"></i> <a href="mailto:support@navigui.com">support@navigui.com</a></li>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Innovation Dr, WebCity</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <?= date('Y'); ?> Navigui. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Consistent JavaScript + File Input Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Standard Navigui JS (Theme, Menu, Messages, Dropdown) ---
            const themeToggle = document.getElementById('themeToggle');
            const currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'dark') { themeToggle.checked = true; }
            themeToggle.addEventListener('change', function() {
                const newTheme = this.checked ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            });

            const mobileToggle = document.getElementById('mobile-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const navContainer = document.querySelector('.nav-container');
            if (mobileToggle && mobileMenu) {
                mobileToggle.addEventListener('click', function(e) { e.stopPropagation(); mobileMenu.classList.toggle('active'); mobileToggle.classList.toggle('active'); });
                document.addEventListener('click', function(e) {
                    if (mobileMenu.classList.contains('active') && !mobileMenu.contains(e.target) && !(navContainer && navContainer.contains(e.target))) {
                        mobileMenu.classList.remove('active'); mobileToggle.classList.remove('active');
                    }
                });
            }

            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                 const messageText = message.querySelector('span') ? message.querySelector('span').textContent.trim() : '';
                 if (messageText.length > 0) {
                    setTimeout(() => {
                        message.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        message.style.opacity = '0';
                        message.style.transform = 'translateY(-20px)';
                        setTimeout(() => { message.style.display = 'none'; }, 500);
                    }, 5000);
                 } else { message.style.display = 'none'; }
            });

             const userAvatar = document.querySelector('.user-avatar');
             const dropdownMenu = document.querySelector('.dropdown-menu');
             if (userAvatar && dropdownMenu) {
                userAvatar.addEventListener('click', function(e) { e.stopPropagation(); dropdownMenu.classList.toggle('active'); });
                document.addEventListener('click', function(e) {
                    if (dropdownMenu.classList.contains('active') && !dropdownMenu.contains(e.target) && e.target !== userAvatar) {
                        dropdownMenu.classList.remove('active');
                    }
                });
            }

            // --- Custom File Input Script ---
            const actualFileInput = document.getElementById('cv_file');
            const fileChosenDisplay = document.getElementById('file-chosen');

            if (actualFileInput && fileChosenDisplay) {
                actualFileInput.addEventListener('change', function() {
                    if (this.files && this.files.length > 0) {
                        fileChosenDisplay.textContent = this.files[0].name;
                    } else {
                        fileChosenDisplay.textContent = 'No file chosen';
                    }
                });
            }
        });
    </script>

</body>
</html>
