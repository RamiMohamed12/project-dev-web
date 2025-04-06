<?php
// File: src/View/applicationFormView.php

// Assume necessary variables are passed from the controller:
// $loggedInUserId, $profilePicSrc, $displayName, $displayEmail,
// $internshipDetails (array), $pageTitle (string),
// $errorMessage (string), $successMessage (string),
// $_POST data might be available if form submitted with errors

// Define default paths/values
$defaultCompanyPic = '../View/images/default_company.png';
$defaultAvatar = '../View/images/default_avatar.png';

// Prepare variables for the header/template (provide defaults if not set)
$userProfilePicSrc = isset($profilePicSrc) ? htmlspecialchars($profilePicSrc) : $defaultAvatar;
$userName = isset($displayName) ? htmlspecialchars($displayName) : 'Student';
$userEmail = isset($displayEmail) ? htmlspecialchars($displayEmail) : '';
$safeLoggedInUserId = isset($loggedInUserId) ? htmlspecialchars((string)$loggedInUserId) : ''; // Ensure it's safe for URLs/forms
$pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Apply for Internship';

// Data for the view (handle potential missing keys gracefully)
$internshipTitle = htmlspecialchars($internshipDetails['title'] ?? 'Internship Offer');
$companyName = htmlspecialchars($internshipDetails['name_company'] ?? 'N/A');
$companyLocation = htmlspecialchars($internshipDetails['company_location'] ?? 'N/A');
$salary = htmlspecialchars($internshipDetails['remuneration'] ?? 'N/A');
$duration = htmlspecialchars($internshipDetails['duration'] ?? 'N/A');
$internshipId = htmlspecialchars((string)($internshipDetails['id_internship'] ?? ''));

// Company Logo Logic
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
    <link rel="stylesheet" href="../View/css/stud_offre.css">
    <style>
        /* --- Application Form Specific Styles --- */

        /* Enhance Internship Summary */
        .internship-summary-card {
            background-color: var(--card-bg-color);
            border-radius: var(--card-border-radius);
            padding: 1.5rem 2rem; /* More padding */
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md); /* Slightly more prominent shadow */
            border: 1px solid var(--border-color);
            display: grid; /* Use grid for better layout */
            grid-template-columns: auto 1fr; /* Logo | Details */
            gap: 1rem 1.5rem;
            align-items: center;
        }

        .internship-summary-card .company-logo-wrapper {
             grid-row: 1 / span 2; /* Logo spans two rows */
             width: 70px;
             height: 70px;
             border-radius: 50%;
             overflow: hidden;
             border: 2px solid var(--border-color-light);
             display: flex;
             justify-content: center;
             align-items: center;
             background-color: #fff; /* Ensure bg for non-transparent logos */
        }
         .internship-summary-card .company-logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Use contain to prevent cropping */
         }

        .internship-summary-card h3 {
            margin: 0;
            font-size: 1.5rem; /* Larger title */
            font-weight: 600;
            color: var(--text-primary);
            grid-column: 2; /* Title in second column */
            align-self: end; /* Align to bottom */
        }

        .internship-summary-details {
             grid-column: 2; /* Details in second column */
             align-self: start; /* Align to top */
             display: flex;
             flex-wrap: wrap;
             gap: 0.5rem 1.5rem; /* Spacing between details */
             margin-top: 0.25rem;
             color: var(--text-secondary);
        }

        .internship-summary-details p {
            margin: 0;
            font-size: 0.95rem;
            display: flex; /* Align icon and text */
            align-items: center;
            gap: 0.5rem; /* Space between icon and text */
        }
        .internship-summary-details i {
             color: var(--primary-color); /* Color the icons */
             width: 1em; /* Consistent icon width */
             text-align: center;
        }
         .internship-summary-details strong { /* No longer needed if using icons */
             /* display: none; */
         }


        /* Enhanced Form Styling */
        .application-form-container {
            background-color: var(--card-bg-color);
            border-radius: var(--card-border-radius);
            padding: 2rem;
            box-shadow: var(--shadow-lg); /* Deeper shadow for focus */
            border: 1px solid var(--border-color);
            margin-top: 1rem;
        }
        .application-form-container h3 { /* Section titles */
             font-size: 1.3rem;
             font-weight: 600;
             margin-bottom: 1.5rem;
             padding-bottom: 0.75rem;
             border-bottom: 1px solid var(--border-color-light);
             color: var(--text-primary);
             display: flex;
             align-items: center;
             gap: 0.75rem;
        }
        .application-form-container h3 i {
             color: var(--primary-color);
        }

        .application-form .form-group {
            margin-bottom: 1.75rem; /* More spacing */
        }

        .application-form label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500; /* Slightly bolder label */
            color: var(--text-primary);
            font-size: 1.05rem; /* Slightly larger label */
        }

        .application-form input[type="file"],
        .application-form textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--input-border-color);
            border-radius: var(--input-border-radius);
            background-color: var(--input-bg-color);
            color: var(--input-text-color);
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease; /* Added box-shadow transition */
        }

        .application-form textarea {
            min-height: 180px; /* Taller text area */
            resize: vertical;
        }

        /* Custom File Input - More Creative */
        .form-group.file-input-group {
             position: relative;
        }
        .file-input-styled {
             display: inline-flex; /* Use flex */
             align-items: center;
             padding: 0.6rem 1.2rem;
             background-color: var(--primary-color-light);
             color: var(--primary-color);
             border: 1px dashed var(--primary-color);
             border-radius: var(--input-border-radius);
             cursor: pointer;
             transition: background-color 0.2s ease;
             font-weight: 500;
        }
        .file-input-styled i {
             margin-right: 0.75rem;
             font-size: 1.1em;
        }
        .file-input-styled:hover {
            background-color: var(--primary-color-lighter);
        }
        .file-input-group input[type="file"] {
             /* Hide the ugly default input but keep it accessible */
             position: absolute;
             left: 0;
             top: 0;
             width: 100%; /* Make it cover the styled label area for clicking */
             height: 100%;
             opacity: 0;
             cursor: pointer;
             z-index: 10; /* Make sure it's clickable */
        }
         #file-chosen { /* Span to display filename */
             margin-left: 1rem;
             font-style: italic;
             color: var(--text-muted);
             font-size: 0.9rem;
         }


        .application-form input:focus,
        .application-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-focus-ring); /* Enhanced focus ring */
        }

        .application-form .form-hint {
            font-size: 0.9rem; /* Slightly larger hint */
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .application-form .form-actions {
            margin-top: 2.5rem; /* More space before buttons */
            display: flex;
            gap: 1rem;
            justify-content: flex-end; /* Align buttons to the right */
            border-top: 1px solid var(--border-color-light); /* Separator line */
            padding-top: 1.5rem;
        }

         .form-actions .btn {
             padding: 0.8rem 1.8rem; /* Larger buttons */
             font-size: 1.05rem;
             font-weight: 500;
         }
          .form-actions .btn i {
              margin-right: 0.5rem;
          }
         .form-actions .btn-secondary {
             background-color: var(--secondary-button-bg);
             color: var(--secondary-button-text);
             border: 1px solid var(--secondary-button-border);
         }
         .form-actions .btn-secondary:hover {
             background-color: var(--secondary-button-hover-bg);
             border-color: var(--secondary-button-hover-border);
         }

         /* Simple animation for messages */
        @keyframes fadeInDown {
             from { opacity: 0; transform: translateY(-10px); }
             to { opacity: 1; transform: translateY(0); }
        }
        .message {
            animation: fadeInDown 0.3s ease-out;
        }

        .highlighted-border {
            border: 2px solid var(--primary-color);
            padding: 1rem;
            background-color: var(--highlight-bg-color);
        }

    </style>
</head>
<body>

    <!-- Consistent Header from viewOffersView.php -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container bento-card">
                <a href="../View/student.php" class="logo">
                    <div class="logo-image-container"><i class="fas fa-graduation-cap"></i></div>
                    <span class="logo-text">Navigui</span>
                </a>
                <div class="menu-items">
                    <a href="../View/student.php" class="menu-item">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="menu-item active">Applications</a>
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
                <div class="mobile-menu-content">
                    <a href="../View/student.php" class="mobile-menu-item">Home</a>
                    <a href="../Controller/offerController.php?action=view" class="mobile-menu-item">Offers</a>
                    <a href="../Controller/wishlistController.php?action=view" class="mobile-menu-item">Wishlist</a>
                    <a href="../Controller/applicationController.php?action=myapps" class="mobile-menu-item active">Applications</a>
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
                    <i class="fas fa-edit"></i> <!-- Changed icon to reflect editing/filling form -->
                </div>
                <h1 class="page-title"><?= $pageTitle ?></h1>
                <p class="page-subtitle">Review the details and craft your application. Good luck!</p> <!-- More engaging subtitle -->
            </div>
        </section>

        <div class="container">
             <a href="javascript:history.back()" class="back-link">
                 <i class="fas fa-chevron-left"></i> Go Back
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

            <?php if (!empty($internshipDetails)): // Only show form if details are available ?>

                <!-- Internship Summary - Enhanced -->
                <div class="internship-summary-card">
                     <div class="company-logo-wrapper">
                          <img src="<?= $companyLogoSrc ?>" alt="<?= $companyName ?> Logo" class="company-logo">
                     </div>
                     <h3><?= $internshipTitle ?></h3>
                    <div class="internship-summary-details">
                        <p><i class="fas fa-building"></i> <?= $companyName ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?= $companyLocation ?></p>
                        <p><i class="fas fa-euro-sign"></i> <?= $salary ?> / month</p>
                        <p><i class="far fa-clock"></i> <?= $duration ?> months</p>
                    </div>
                </div>

                <!-- Application Form -->
                <div class="application-form-container bento-card">
                     <form method="post" action="../Controller/applicationController.php?action=submit" enctype="multipart/form-data" class="application-form">
                        <input type="hidden" name="internship_id" value="<?= $internshipId ?>">
                        <input type="hidden" name="redirect_success_url" value="../Controller/applicationController.php?action=myapps&success=applied">
                        <input type="hidden" name="redirect_fail_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">

                         <!-- Section 1: Motivation -->
                         <h3 class="highlighted-border"><i class="fas fa-pen-fancy"></i> Your Motivation</h3>
                        <div class="form-group highlighted-border">
                            <label for="motivation_letter">Motivation Letter</label>
                            <textarea id="motivation_letter" name="motivation_letter" rows="10" required placeholder="Tell <?= $companyName ?> why you're excited about this opportunity and what makes you a great candidate. Mention specific skills or experiences relevant to the role..."><?= htmlspecialchars($_POST['motivation_letter'] ?? '') ?></textarea>
                            <p class="form-hint">This is your chance to shine! Be genuine and specific.</p>
                        </div>

                        <!-- Section 2: CV -->
                         <h3><i class="fas fa-file-alt"></i> Attach Your CV</h3>
                        <div class="form-group file-input-group">
                            <label for="cv_file">Upload CV (PDF, DOC, DOCX - Max 5MB)</label>
                            <!-- Visually hidden actual input -->
                            <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" aria-hidden="true">
                             <!-- Styled replacement label acting as button -->
                            <label for="cv_file" class="file-input-styled">
                               <i class="fas fa-upload"></i> Choose File...
                            </label>
                             <!-- Span to display chosen filename -->
                             <span id="file-chosen">No file chosen</span>
                            <p class="form-hint">Ensure your CV is up-to-date. If none is uploaded, we will use the one from your profile.</p>
                            <span id="file-chosen">No file chosen</span>
                            <p class="form-hint">Ensure your CV is up-to-date. If none is uploaded, your default profile CV might be used (if applicable).</p>
                        </div>

                        <!-- Section 3: Submit -->
                        <div class="form-actions">
                            <button type="submit" class="btn">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                            <a href="javascript:history.back()" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Whoops! We couldn't load the internship details. Please <a href="javascript:history.back()">go back</a> and try again.</span>
                </div>
            <?php endif; // End check for internshipDetails ?>

        </div> <!-- /.container -->
    </main>

    <!-- Consistent Footer -->
    <footer class="footer">
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
                        message.style.transform = 'translateY(-20px)'; // Slide up effect
                        setTimeout(() => { message.style.display = 'none'; }, 500);
                    }, 5000); // 5 seconds
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
                        // Display the name of the first file selected
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
