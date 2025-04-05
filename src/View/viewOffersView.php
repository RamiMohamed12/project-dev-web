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
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : ($loggedInUserRole === 'pilote' ? '../View/pilote.php' : '../View/student.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Internship Offers') ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/offers.css" rel="stylesheet">    
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
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../View/images/default_avatar.png" alt="Profile Picture">
                <div>
                    <h3><?= ucfirst($loggedInUserRole) ?> Dashboard</h3>
                    <p>Internship Offers</p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= $dashboardUrl ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Navigation based on role -->
                <?php if ($loggedInUserRole === 'student'): ?>
                <li class="nav-item">
                    <a href="../Controller/offerController.php?action=view" class="nav-link active">
                        <i class="fas fa-briefcase"></i>
                        <span>View Offers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/wishlistController.php?action=view" class="nav-link">
                        <i class="fas fa-heart"></i>
                        <span>My Wishlist</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/applicationController.php?action=myapps" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>My Applications</span>
                    </a>
                </li>
                <?php elseif ($loggedInUserRole === 'pilote' || $loggedInUserRole === 'admin'): ?>
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
                        <i class="fas fa-file-lines"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/offerController.php?action=view" class="nav-link active">
                        <i class="fas fa-briefcase"></i>
                        <span>View All Offers</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
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
                <a class="navbar-brand" href="#">
                    <img src="../View/images/default_avatar.png" alt="Profile">
                    <span><i class="fas fa-briefcase me-2"></i>Internship Offers</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <h1><i class="fas fa-briefcase"></i> <?= htmlspecialchars($pageTitle ?? 'Available Internship Offers') ?></h1>
                <ul class="breadcrumbs">
                    <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                    <li>Internship Offers</li>
                </ul>
            </div>
            
            <!-- Messages -->
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Search and filter card -->
            <div class="search-card fade-in">
                <form method="get" action="offerController.php" class="search-form">
                    <input type="hidden" name="action" value="view">
                    
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search by title, company name, or location..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="filter-group">
                        <select name="sort" id="sort-select">
                            <option value="newest" <?= (($_GET['sort'] ?? 'newest') === 'newest') ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= (($_GET['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="offerController.php?action=view" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                    </div>
                </form>
            </div>

            <!-- Offers grid -->
            <?php if (empty($internships)): ?>
                <div class="empty-state fade-in delay-1">
                    <i class="fas fa-folder-open"></i>
                    <h3>No internship offers found</h3>
                    <p>There are no internship offers matching your search criteria. Try adjusting your search or check back later for new opportunities.</p>
                </div>
            <?php else: ?>
                <div class="offers-grid">
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
                        <div class="offer-card fade-in delay-<?= $loop ?? 1 ?>">
                            <div class="offer-header">
                                <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($internship['name_company'] ?? 'Company') ?> Logo" class="company-logo">
                                <div class="offer-header-info">
                                    <h3><?= htmlspecialchars($internship['title'] ?? 'Internship Offer') ?></h3>
                                    <p><?= htmlspecialchars($internship['name_company'] ?? 'N/A') ?></p>
                                    <div class="star-rating"><?= $ratingHtml ?></div>
                                </div>
                            </div>
                            <div class="offer-body">
                                <div class="offer-details">
                                    <div class="detail-item">
                                        <i class="fas fa-location-dot"></i>
                                        <span class="detail-label">Location:</span>
                                        <span class="detail-value"><?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-euro-sign"></i>
                                        <span class="detail-label">Salary:</span>
                                        <span class="detail-value"><?= $internship['remuneration'] ? htmlspecialchars(number_format($internship['remuneration'], 2) . ' €/month') : 'Not specified' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <span class="detail-label">Posted:</span>
                                        <span class="detail-value"><?= htmlspecialchars(date('d M Y', strtotime($internship['offre_date'] ?? ''))) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-users"></i>
                                        <span class="detail-label">Applications:</span>
                                        <span class="detail-value"><?= htmlspecialchars($appCountText) ?></span>
                                    </div>
                                </div>
                                
                                <div class="offer-description">
                                    <?= nl2br(htmlspecialchars($internship['description'] ?? 'No description available.')) ?>
                                </div>
                            </div>
                            <div class="offer-footer">
                                <div class="application-count">
                                    <i class="fas fa-calendar"></i> Posted on <?= htmlspecialchars(date('d M Y', strtotime($internship['offre_date'] ?? ''))) ?>
                                </div>
                                <div class="offer-actions">
                                    <?php if ($loggedInUserRole === 'student'): ?>
                                        <a href="../Controller/applicationController.php?action=apply&id=<?= $internship['id_internship'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-paper-plane"></i> Apply
                                        </a>
                                        <?php if ($wishlistModelAvailable): ?>
                                            <?php if ($isInWishlist): ?>
                                                <a href="../Controller/wishlistController.php?action=remove&id=<?= $internship['id_internship'] ?>&ref=offers" class="btn btn-secondary btn-sm btn-circle" title="Remove from Wishlist">
                                                    <i class="fas fa-heart-crack"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="../Controller/wishlistController.php?action=add&id=<?= $internship['id_internship'] ?>&ref=offers" class="btn btn-secondary btn-sm btn-circle" title="Add to Wishlist">
                                                    <i class="far fa-heart"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="../Controller/internshipController.php?action=edit&id=<?= $internship['id_internship'] ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <footer>
                <p>© <?= date('Y'); ?> Project Dev Web Application - Current time: 2025-04-05 15:48:37</p>
            </footer>
        </div>
    </div>
<script src="JavaScript/offers.js"> </script> 
</body>
</html>
