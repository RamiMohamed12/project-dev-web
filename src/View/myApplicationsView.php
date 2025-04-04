<?php
// Location: src/View/myApplicationsView.php
// Included by applicationController.php (action=myapps for Students)

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
    <title>My Applications - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="../View/css/applications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-theme">
    <header>
        <h1>
            <i class="fa-solid fa-clipboard-list"></i> My Applications
        </h1>
        <nav>
            <ul>
                <li><a href="../View/student.php">Dashboard</a></li>
                <li><a href="../Controller/offerController.php?action=view">View Offers</a></li>
                <li><a href="../Controller/wishlistController.php?action=view">My Wishlist</a></li>
                <li><a href="../Controller/applicationController.php?action=myapps" class="active">My Applications</a></li>
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
        
        <!-- Debug info - remove in production -->
        <?php 
        error_log("Applications in view: " . (is_array($applications) ? count($applications) : 'not an array'));
        if (is_array($applications)) {
            error_log("First application: " . print_r($applications[0] ?? 'none', true));
        }
        ?>

        <!-- Applications List -->
        <div class="applications-container">
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-clipboard-list fa-3x"></i>
                    <p>You haven't applied to any internships yet.</p>
                    <a href="../Controller/offerController.php?action=view" class="btn">Browse Internship Offers</a>
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
                            <p><strong>Company:</strong> <?= htmlspecialchars($app['name_company'] ?? 'N/A') ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($app['company_location'] ?? 'N/A') ?></p>
                            <p><strong>Salary:</strong> <?= htmlspecialchars($app['remuneration'] ?? 'N/A') ?> €/month</p>
                            <p><strong>Duration:</strong> <?= htmlspecialchars($app['duration'] ?? 'N/A') ?> months</p>
                            <!-- In the application-details section, update the Applied on line -->
                            <p><strong>Applied on:</strong> <?= htmlspecialchars(date('F j, Y', strtotime($app['created_at'] ?? date('Y-m-d')))) ?></p>
                        </div>
                        
                        <div class="application-status">
                            <span class="status-badge status-<?= strtolower($app['status'] ?? 'pending') ?>">
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
                                <i class="fa-solid fa-<?= $statusIcon ?>"></i> <?= $statusText ?>
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

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>