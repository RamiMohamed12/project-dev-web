<?php
// Location: src/View/viewOffersView.php
// Included by offerController.php (action=view)
// Assumes $internships array contains application_count, company_average_rating, company_rating_count

// Basic security check (Ensure essential variables are set)
if (!isset($loggedInUserRole) || !isset($loggedInUserId) || !isset($internships)) {
    die("Access Denied or essential data missing.");
}

// Wishlist model check (only relevant for students)
$wishlistModelAvailable = false;
if ($loggedInUserRole === 'student' && isset($wishlistModel) && is_object($wishlistModel)) {
     $wishlistModelAvailable = true;
} elseif ($loggedInUserRole === 'student') {
    // Attempt to include/instantiate if missed by controller - less ideal but fallback
    try {
        require_once __DIR__ . '/../Model/Wishlist.php';
        if (isset($conn)) { // Check if $conn is available
             $wishlistModel = new Wishlist($conn);
             $wishlistModelAvailable = true;
        }
    } catch (Exception $e) {
        error_log("viewOffersView: Failed to load Wishlist model: " . $e->getMessage());
    }
}

$defaultCompanyPic = '../View/images/default_company.png'; // Ensure path is correct
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Internship Offers') ?> - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css"> <!-- Main Styles -->
    <link rel="stylesheet" href="../View/css/offers.css"> <!-- Specific styles for offers page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> <!-- Updated FontAwesome -->
    <style>
        /* Star Rating Styles (Should be in offers.css or style.css) */
        .star-rating {
            color: #f8b400; /* Gold color for stars */
            font-size: 0.95em; /* Adjust size */
            margin-top: 4px; /* Spacing */
            display: inline-block; /* Keep stars and count together */
        }
        .star-rating .fa-regular { /* Empty stars */
             color: #ccc; /* Lighter grey */
        }
        .rating-count {
            font-size: 0.8em;
            color: #6c757d;
            margin-left: 6px;
            vertical-align: middle; /* Align count with stars */
        }
        .no-rating {
             font-size: 0.8em;
             color: #6c757d;
             margin-left: 6px;
             vertical-align: middle;
             font-style: italic;
        }

        /* Application Count Styles */
        .application-count {
             font-size: 0.9em;
             color: #17a2b8; /* Info color */
             margin-top: 8px;
             display: block; /* On its own line */
        }
        .application-count i {
             margin-right: 5px;
        }

        /* General Card/Layout improvements (Ensure these align with offers.css) */
         .offers-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;}
         .offer-card { background-color: #fff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: box-shadow 0.2s ease-in-out; }
         .offer-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
         .offer-header { display: flex; align-items: flex-start; padding: 15px; border-bottom: 1px solid #eee;}
         .company-logo { width: 45px; height: 45px; object-fit: contain; margin-right: 12px; border-radius: 4px; flex-shrink: 0; background-color: #f8f8f8; }
         .offer-header-info { flex-grow: 1; }
         .offer-header-info h3 { margin: 0 0 2px 0; font-size: 1.15em; color: #333; }
         .offer-header-info p { margin: 0; color: #555; font-size: 0.95em;}
         .offer-details { padding: 15px; font-size: 0.9em; color: #444; flex-grow: 1; /* Allow details to fill space */ }
         .offer-details p { margin: 6px 0; display: flex; align-items: center; }
         .offer-details i { margin-right: 8px; color: #6c757d; width: 16px; text-align: center; } /* Align icons */
         .offer-details strong { color: #555; min-width: 80px; display: inline-block;} /* Consistent label width */
         .offer-description { padding: 0 15px 15px 15px; font-size: 0.9em; color: #555; border-bottom: 1px solid #eee; max-height: 100px; overflow: hidden; position: relative;} /* Limit description height */
         .offer-description::after { /* Fade out effect for truncated description */ content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 20px; background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%); pointer-events: none;}
         .offer-actions { padding: 10px 15px; background-color: #fcfcfc; display: flex; justify-content: space-between; align-items: center; }
         .btn, .btn-secondary, .btn-primary { /* Ensure button styles are loaded from styles.css */ }
         .btn i { margin-right: 5px; }

        /* Search/Filter Bar Styles */
        .search-container { background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #dee2e6; }
        .search-form { display: flex; flex-wrap: wrap; gap: 15px; align-items: center; }
        .search-group { flex-grow: 1; display: flex; }
        .search-group input[type="text"] { flex-grow: 1; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px 0 0 4px; }
        .search-group button { border-radius: 0 4px 4px 0; }
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-group select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; }
        .filter-button, .reset-button { padding: 8px 12px; font-size: 0.9em; } /* Adjust padding */

    </style>
</head>
<body class="<?= htmlspecialchars($loggedInUserRole) ?>-theme">
    <header>
        <h1>
            <i class="fa-solid fa-briefcase"></i> Available Internship Offers
        </h1>
        <nav>
            <ul>
                <!-- Navigation based on role -->
                 <?php if ($loggedInUserRole === 'student'): ?>
                     <li><a href="../View/student.php">Dashboard</a></li>
                     <li><a href="../Controller/offerController.php?action=view" class="active">View Offers</a></li>
                     <li><a href="../Controller/wishlistController.php?action=view">My Wishlist</a></li>
                     <li><a href="../Controller/applicationController.php?action=myapps">My Applications</a></li>
                     <?php if ($loggedInUserId): ?><li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=student">My Profile</a></li><?php endif; ?>
                 <?php elseif ($loggedInUserRole === 'pilote' || $loggedInUserRole === 'admin'): ?>
                     <li><a href="../View/<?= $loggedInUserRole ?>.php">Dashboard</a></li>
                     <li><a href="../Controller/offerController.php?action=view" class="active">View Offers</a></li>
                     <li><a href="../Controller/internshipController.php">Manage Internships</a></li>
                     <!-- Add other relevant links -->
                 <?php endif; ?>
                <li><a href="../Controller/logoutController.php">Logout <i class="fa-solid fa-right-from-bracket"></i></a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <!-- Back link might not be needed if header nav is sufficient -->
        <!-- <a href="../View/student.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back</a> -->

        <h1><?= htmlspecialchars($pageTitle ?? 'Available Internship Offers') ?></h1>

        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <!-- Search and Filter Form -->
        <div class="search-container">
            <form method="get" action="offerController.php" class="search-form">
                <input type="hidden" name="action" value="view">

                <div class="search-group">
                    <input type="text" name="search" placeholder="Search by title, company..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn"><i class="fa-solid fa-search"></i></button>
                </div>

                <div class="filter-group">
                     <label for="sort-select" class="visually-hidden">Sort by:</label> <?php // Accessibility ?>
                    <select name="sort" id="sort-select">
                        <option value="newest" <?= (($_GET['sort'] ?? 'newest') === 'newest') ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= (($_GET['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                         <!-- Add other sort options if implemented in model -->
                        <!-- <option value="salary_high" ...>Highest Salary</option> -->
                        <!-- <option value="salary_low" ...>Lowest Salary</option> -->
                    </select>
                    <button type="submit" class="btn filter-button"><i class="fa-solid fa-filter"></i> Apply</button>
                    <a href="offerController.php?action=view" class="btn btn-secondary reset-button"><i class="fa-solid fa-undo"></i> Reset</a>
                </div>
            </form>
        </div>

        <!-- Internship Offers Grid -->
        <div class="offers-container">
            <?php if (empty($internships)): ?>
                <div class="empty-state" style="grid-column: 1 / -1;"> <?php // Span across grid columns ?>
                    <i class="fa-solid fa-folder-open fa-3x"></i>
                    <p>No internship offers found matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($internships as $internship):
                    // --- Prepare Rating Display ---
                    $avgRating = $internship['company_average_rating'] ?? null;
                    $ratingCount = $internship['company_rating_count'] ?? 0;
                    $ratingHtml = '';
                    if ($avgRating !== null && $ratingCount > 0) {
                        $roundedRating = round($avgRating); // Round for full/half star logic if needed
                        for ($i = 1; $i <= 5; $i++) {
                             // Simple filled/empty star logic
                            $ratingHtml .= ($i <= $roundedRating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                        }
                        $ratingHtml .= "<span class='rating-count'>(" . number_format($avgRating, 1) . " / " . $ratingCount . " rating" . ($ratingCount != 1 ? 's' : '') . ")</span>";
                    } else {
                         $ratingHtml = "<span class='no-rating'>No ratings yet</span>";
                    }

                    // --- Prepare Logo ---
                    $companyLogoSrc = $defaultCompanyPic;
                    if (!empty($internship['company_picture_mime']) && !empty($internship['company_picture'])) {
                         $logoData = is_resource($internship['company_picture']) ? stream_get_contents($internship['company_picture']) : $internship['company_picture'];
                         if ($logoData) {
                             $companyLogoSrc = 'data:' . htmlspecialchars($internship['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                         }
                    }

                    // --- Prepare Application Count ---
                     $appCount = $internship['application_count'] ?? 0;
                     $appCountText = $appCount . " application" . ($appCount != 1 ? 's' : '');

                     // --- Check Wishlist Status ---
                     $isInWishlist = false;
                     if ($wishlistModelAvailable && $loggedInUserRole === 'student' && isset($internship['id_internship'])) {
                         $isInWishlist = $wishlistModel->isInWishlist($loggedInUserId, $internship['id_internship']);
                     }
                ?>
                    <div class="offer-card">
                        <div class="offer-header">
                            <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($internship['name_company'] ?? 'Company') ?> Logo" class="company-logo">
                            <div class="offer-header-info">
                                <h3><?= htmlspecialchars($internship['title'] ?? 'Internship Offer') ?></h3>
                                <p><?= htmlspecialchars($internship['name_company'] ?? 'N/A') ?></p>
                                <!-- Display Rating -->
                                <div class="star-rating"><?= $ratingHtml ?></div>
                            </div>
                        </div>
                        <div class="offer-details">
                            <p><i class="fa-solid fa-location-dot"></i> <strong>Location:</strong> <?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></p>
                            <p><i class="fa-solid fa-euro-sign"></i> <strong>Salary:</strong> <?= htmlspecialchars($internship['remuneration'] ?? 'Not specified') ?> €/month</p>
                            <p><i class="fa-solid fa-calendar-day"></i> <strong>Posted:</strong> <?= htmlspecialchars(date('d M Y', strtotime($internship['offre_date'] ?? ''))) ?></p>
                             <!-- Display Application Count -->
                             <p><i class="fa-solid fa-users"></i> <strong>Applied:</strong> <?= htmlspecialchars($appCountText) ?></p>
                            <?php /* Add duration if available in $internship array
                            <p><i class="fa-solid fa-clock"></i> <strong>Duration:</strong> <?= htmlspecialchars($internship['duration'] ?? 'N/A') ?> months</p>
                            */ ?>
                        </div>
                        <div class="offer-description">
                            <p><?= nl2br(htmlspecialchars(substr($internship['description'] ?? '', 0, 120))) ?>...</p> <?php // Shorter preview ?>
                        </div>
                        <div class="offer-actions">
                             <?php /* Add Details Link if needed
                             <a href="offerController.php?action=detail&id=<?= $internship['id_internship'] ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-circle-info"></i> Details</a>
                             */ ?>
                             <?php if ($loggedInUserRole === 'student'): ?>
                                <a href="../Controller/applicationController.php?action=apply&id=<?= $internship['id_internship'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane"></i> Apply</a>
                                <?php if ($wishlistModelAvailable): ?>
                                    <?php if ($isInWishlist): ?>
                                        <a href="../Controller/wishlistController.php?action=remove&id=<?= $internship['id_internship'] ?>&ref=offers" class="btn btn-secondary btn-sm" title="Remove from Wishlist"><i class="fa-solid fa-heart-crack"></i></a>
                                    <?php else: ?>
                                        <a href="../Controller/wishlistController.php?action=add&id=<?= $internship['id_internship'] ?>&ref=offers" class="btn btn-secondary btn-sm" title="Add to Wishlist"><i class="fa-regular fa-heart"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- /.offers-container -->

    </div> <!-- /.container -->

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>