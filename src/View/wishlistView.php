<?php
// Location: src/View/wishlistView.php
// Included by wishlistController.php (action=view for Students)

// Prevent direct access
if (!isset($loggedInUserRole) || $loggedInUserRole !== 'student') {
    die("Access Denied.");
}

$defaultCompanyPic = '../View/images/default_company.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-theme">
    <header>
        <h1>
            <i class="fa-solid fa-heart"></i> My Wishlist
        </h1>
        <nav>
            <ul>
                <li><a href="../View/student.php">Dashboard</a></li>
                <li><a href="../Controller/offerController.php?action=view">View Offers</a></li>
                <li><a href="../Controller/wishlistController.php?action=view" class="active">My Wishlist</a></li>
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

        <!-- Wishlist Items -->
        <div class="offers-container">
            <?php if (empty($wishlistItems)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-heart-crack fa-3x"></i>
                    <p>Your wishlist is empty. Browse available internship offers and add them to your wishlist.</p>
                    <a href="../Controller/offerController.php?action=view" class="btn">View Available Offers</a>
                </div>
            <?php else: ?>
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="offer-card">
                        <div class="offer-header">
                            <?php 
                            $companyLogoSrc = $defaultCompanyPic;
                            if (!empty($item['company_picture']) && !empty($item['company_picture_mime'])) {
                                $logoData = is_resource($item['company_picture']) ? stream_get_contents($item['company_picture']) : $item['company_picture'];
                                if ($logoData) {
                                    $companyLogoSrc = 'data:' . htmlspecialchars($item['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                                }
                            }
                            ?>
                            <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($item['name_company'] ?? 'Company') ?>" class="company-logo">
                            <h3><?= htmlspecialchars($item['title']) ?></h3>
                        </div>
                        
                        <div class="offer-details">
                            <p><strong>Company:</strong> <?= htmlspecialchars($item['name_company'] ?? 'N/A') ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($item['company_location'] ?? 'N/A') ?></p>
                            <p><strong>Salary:</strong> <?= htmlspecialchars($item['remuneration'] ?? 'N/A') ?> €/month</p>
                            <p><strong>Duration:</strong> <?= htmlspecialchars($item['duration'] ?? 'N/A') ?> months</p>
                            <p><strong>Date Posted:</strong> <?= date('M d, Y', strtotime($item['offre_date'])) ?></p>
                        </div>
                        
                        <div class="offer-description">
                            <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        </div>
                        
                        <div class="offer-actions">
                            <a href="../Controller/applicationController.php?action=apply&id=<?= $item['id_internship'] ?>" class="btn">
                                <i class="fa-solid fa-paper-plane"></i> Apply
                            </a>
                            <a href="../Controller/wishlistController.php?action=remove&id=<?= $item['id_internship'] ?>" class="btn btn-danger">
                                <i class="fa-solid fa-heart-crack"></i> Remove
                            </a>
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