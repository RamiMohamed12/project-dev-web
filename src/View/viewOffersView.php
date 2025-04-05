<?php
// Location: src/View/viewOffersView.php
// Included by offerController.php (action=view for Students)

// Prevent direct access (keep this check)
if (!isset($loggedInUserRole) || $loggedInUserRole !== 'student') {
    die("Access Denied.");
}

// Ensure wishlist model is available (keep this check)
if (!isset($wishlistModel)) {
    require_once __DIR__ . '/../Model/Wishlist.php';
    $wishlistModel = new Wishlist($conn);
}
// Ensure company model is available for rating check fallback (though it should be passed now)
 if (!isset($companyModel)) {
     require_once __DIR__ . '/../Model/Company.php';
     $companyModel = new Company($conn);
 }


$defaultCompanyPic = '../View/images/default_company.png';

// --- NEW HELPER FUNCTION for Stars (Optional but helpful) ---
function displayStars($rating) {
    $output = '';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    for ($i = 0; $i < $fullStars; $i++) {
        $output .= '<i class="fa-solid fa-star star-filled"></i>';
    }
    if ($halfStar) {
        $output .= '<i class="fa-solid fa-star-half-alt star-filled"></i>'; // Using font-awesome's half star
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $output .= '<i class="fa-regular fa-star star-empty"></i>'; // Using font-awesome's regular (empty) star
    }
    return $output;
}
// --- END HELPER FUNCTION ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Internship Offers - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add some basic CSS for stars -->
    <style>
        .star-rating { color: #f8d64e; /* Gold color */ margin-bottom: 10px; }
        .star-filled { color: #f8d64e; }
        .star-empty { color: #ccc; }
        .rating-count { font-size: 0.9em; color: #666; margin-left: 5px;}
        .rate-company-section { margin-top: 15px; padding-top: 10px; border-top: 1px solid #eee; }
        .rate-company-section h4 { margin-bottom: 8px; font-size: 1em; color: #333;}
        .rating-form .star-input i { cursor: pointer; margin-right: 2px; font-size: 1.2em; }
        .rating-form .star-input input[type="radio"] { display: none; } /* Hide radio buttons */
        /* Style stars based on radio hover/check */
        .rating-form .star-input label { color: #ccc; } /* Default empty */
        .rating-form .star-input input[type="radio"]:checked ~ label { color: #f8d64e; } /* Checked stars */
        .rating-form .star-input label:hover,
        .rating-form .star-input label:hover ~ label { color: #f8d64e; } /* Hover stars */
        /* Ensure stars are displayed in reverse order in HTML for correct CSS hover */
        .rating-form .star-input { display: inline-block; /* Or flex */ direction: rtl; /* Right-to-left for hover effect */ }
        .rating-form .star-input label { display: inline-block; transition: color 0.2s; }
         .rating-form textarea { width: 95%; margin-top: 5px; }
         .rating-form button { margin-top: 5px; }

    </style>
</head>
<body class="student-theme">
    <header>
        <!-- Header content remains the same -->
        <h1>
            <i class="fa-solid fa-briefcase"></i> Available Internship Offers
        </h1>
        <nav>
            <ul>
                <li><a href="../View/student.php">Dashboard</a></li>
                <li><a href="../Controller/offerController.php?action=view" class="active">View Offers</a></li>
                <li><a href="../Controller/wishlistController.php?action=view">My Wishlist</a></li>
                <li><a href="../Controller/applicationController.php?action=myapps">My Applications</a></li>
                <?php if ($loggedInUserId): ?>
                <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student">My Profile</a></li>
                <?php endif; ?>
                <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <a href="../View/student.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <h1><?= htmlspecialchars($pageTitle) ?></h1>

        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <!-- Search Form (remains the same) -->
        <div class="search-container">
             <form method="get" action="../Controller/offerController.php" class="search-form">
                 <input type="hidden" name="action" value="view">

                 <div class="search-group">
                     <input type="text" name="search" placeholder="Search by title, company, or location"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                     <button type="submit" class="btn"><i class="fa-solid fa-search"></i> Search</button>
                 </div>

                 <div class="filter-group">
                     <select name="sort">
                         <option value="newest" <?= (($_GET['sort'] ?? '') === 'newest') ? 'selected' : '' ?>>Newest First</option>
                         <option value="oldest" <?= (($_GET['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                         <!-- Add salary sort options back if column exists -->
                         <!-- <option value="salary_high" <?php //echo (($_GET['sort'] ?? '') === 'salary_high') ? 'selected' : '' ?>>Highest Salary</option> -->
                         <!-- <option value="salary_low" <?php //echo (($_GET['sort'] ?? '') === 'salary_low') ? 'selected' : '' ?>>Lowest Salary</option> -->
                     </select>

                     <button type="submit" class="filter-button"><i class="fa-solid fa-filter"></i> Apply Filters</button>
                     <a href="../Controller/offerController.php?action=view" class="reset-button"><i class="fa-solid fa-undo"></i> Reset</a>
                 </div>
             </form>
         </div>


        <!-- Internship Offers -->
        <div class="offers-container">
            <?php if (empty($internships)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-folder-open fa-3x"></i>
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
                            <p><strong>Company:</strong> <?= htmlspecialchars($internship['name_company'] ?? 'N/A') ?></p>
                            <!-- START: Display Company Rating -->
                            <div class="star-rating">
                                <?php if ($ratingInfo['average'] !== null): ?>
                                    <?= displayStars($ratingInfo['average']) ?>
                                    <span class="rating-count">(<?= number_format($ratingInfo['average'], 1) ?>/5 from <?= $ratingInfo['count'] ?> review<?= $ratingInfo['count'] !== 1 ? 's' : '' ?>)</span>
                                <?php else: ?>
                                    <i class="fa-regular fa-star star-empty"></i> <!-- Show empty stars -->
                                    <i class="fa-regular fa-star star-empty"></i>
                                    <i class="fa-regular fa-star star-empty"></i>
                                    <i class="fa-regular fa-star star-empty"></i>
                                    <i class="fa-regular fa-star star-empty"></i>
                                    <span class="rating-count">(No reviews yet)</span>
                                <?php endif; ?>
                            </div>
                             <!-- END: Display Company Rating -->
                            <p><strong>Location:</strong> <?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></p> <?php // Assuming location comes from company join now ?>
                            <p><strong>Duration:</strong> <?= htmlspecialchars($internship['duration'] ?? 'N/A') ?></p>
                            <p><strong>Salary:</strong> <?= htmlspecialchars($internship['remuneration'] ?? 'N/A') ?> €</p>
                            <p><strong>Date Posted:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($internship['offre_date'] ?? 'now'))) ?></p> <?php // Format date ?>
                        </div>

                        <div class="offer-description">
                            <p><?= nl2br(htmlspecialchars($internship['description'])) ?></p>
                        </div>

                        <div class="offer-actions">
                            <a href="../Controller/applicationController.php?action=apply&id=<?= $internship['id_internship'] ?>" class="btn">
                                <i class="fa-solid fa-paper-plane"></i> Apply
                            </a>

                            <?php
                            // Wishlist Button (remains the same)
                            $isInWishlist = $wishlistModel->isInWishlist($loggedInUserId, $internship['id_internship']);
                            if ($isInWishlist):
                            ?>
                                <a href="../Controller/wishlistController.php?action=remove&id=<?= $internship['id_internship'] ?>" class="btn btn-secondary">
                                    <i class="fa-solid fa-heart-broken"></i> Remove from Wishlist
                                </a>
                            <?php else: ?>
                                <a href="../Controller/wishlistController.php?action=add&id=<?= $internship['id_internship'] ?>" class="btn btn-secondary">
                                    <i class="fa-solid fa-heart"></i> Add to Wishlist
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- START: Rate Company Section -->
                        <?php if ($companyId && $loggedInUserId): // Ensure we have IDs needed ?>
                            <div class="rate-company-section">
                                <?php if ($studentHasRated): ?>
                                    <p><i class="fa-solid fa-check-circle" style="color: green;"></i> You have already rated this company.</p>
                                <?php else: ?>
                                    <h4>Rate this Company:</h4>
                                    <!-- Make sure companyController exists and handles 'rate' action -->
                                    <form action="../Controller/companyController.php?action=rate" method="post" class="rating-form">
                                        <input type="hidden" name="company_id" value="<?= $companyId ?>">
                                        <input type="hidden" name="student_id" value="<?= $loggedInUserId ?>"> <!-- Consider security: verify on server -->
                                        <!-- Star Rating Input using Radio Buttons (styled with CSS) -->
                                        <div class="star-input">
                                             <!-- Input stars in reverse order for CSS hover trick -->
                                            <input type="radio" id="star5_<?= $companyId ?>" name="rating_value" value="5" required><label for="star5_<?= $companyId ?>"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star4_<?= $companyId ?>" name="rating_value" value="4"><label for="star4_<?= $companyId ?>"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star3_<?= $companyId ?>" name="rating_value" value="3"><label for="star3_<?= $companyId ?>"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star2_<?= $companyId ?>" name="rating_value" value="2"><label for="star2_<?= $companyId ?>"><i class="fa-solid fa-star"></i></label>
                                            <input type="radio" id="star1_<?= $companyId ?>" name="rating_value" value="1"><label for="star1_<?= $companyId ?>"><i class="fa-solid fa-star"></i></label>
                                        </div>
                                        <textarea name="comment" placeholder="Optional: Add a comment..." rows="2"></textarea>
                                        <button type="submit" class="btn btn-small"><i class="fa-solid fa-paper-plane"></i> Submit Rating</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <!-- END: Rate Company Section -->

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
