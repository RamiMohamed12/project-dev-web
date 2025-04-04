<?php
// Location: src/View/viewOffersView.php
// Included by offerController.php (action=view for Students)

// Prevent direct access
if (!isset($loggedInUserRole) || $loggedInUserRole !== 'student') {
    die("Access Denied.");
}

// Ensure wishlist model is available
if (!isset($wishlistModel)) {
    require_once __DIR__ . '/../Model/Wishlist.php';
    $wishlistModel = new Wishlist($conn);
}

$defaultCompanyPic = '../View/images/default_company.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Internship Offers - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-theme">
    <header>
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

        <!-- Search Form -->
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
                        <option value="salary_high" <?= (($_GET['sort'] ?? '') === 'salary_high') ? 'selected' : '' ?>>Highest Salary</option>
                        <option value="salary_low" <?= (($_GET['sort'] ?? '') === 'salary_low') ? 'selected' : '' ?>>Lowest Salary</option>
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
                            <p><strong>Location:</strong> <?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></p>
                            <p><strong>Duration:</strong> <?= htmlspecialchars($internship['duration'] ?? 'N/A') ?></p>
                            <p><strong>Salary:</strong> <?= htmlspecialchars($internship['remuneration'] ?? 'N/A') ?> €</p>
                            <p><strong>Date Posted:</strong> <?= htmlspecialchars($internship['offre_date'] ?? 'N/A') ?></p>
                        </div>
                        
                        <div class="offer-description">
                            <p><?= nl2br(htmlspecialchars($internship['description'])) ?></p>
                        </div>
                        
                        <div class="offer-actions">
                            <a href="../Controller/applicationController.php?action=apply&id=<?= $internship['id_internship'] ?>" class="btn">
                                <i class="fa-solid fa-paper-plane"></i> Apply
                            </a>
                            
                            <?php 
                            // Check if internship is already in wishlist
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