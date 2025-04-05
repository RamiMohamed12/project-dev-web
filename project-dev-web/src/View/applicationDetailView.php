<?php
// Location: src/View/applicationDetailView.php
// Included by applicationController.php (action=view for Students)

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
    <title>Application Details - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .application-detail {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 20px;
        }
        
        .application-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .application-header h2 {
            margin: 0;
            color: #4e73df;
        }
        
        .application-content {
            padding: 20px;
        }
        
        .application-section {
            margin-bottom: 25px;
        }
        
        .application-section h3 {
            color: #343a40;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .application-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        
        .meta-item i {
            margin-right: 8px;
            color: #6c757d;
        }
        
        .motivation-text {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-line;
        }
        
        .feedback-section {
            background-color: #e9f7ef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body class="student-theme">
    <header>
        <h1>
            <i class="fa-solid fa-file-lines"></i> Application Details
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
        <a href="../Controller/applicationController.php?action=myapps" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to My Applications
        </a>
        
        <h1>Application Details</h1>
        
        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        
        <!-- Application Details -->
        <div class="application-detail">
            <div class="application-header">
                <h2><?= htmlspecialchars($application['title']) ?> at <?= htmlspecialchars($application['company_name']) ?></h2>
            </div>
            
            <div class="application-content">
                <div class="application-meta">
                    <div class="meta-item">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Applied on: <?= date('M d, Y', strtotime($application['application_date'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Location: <?= htmlspecialchars($application['location']) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-clock"></i>
                        <span>Duration: <?= htmlspecialchars($application['duration']) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Salary: <?= htmlspecialchars($application['salary']) ?> €</span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Status: 
                            <?php if ($application['status'] === 'pending'): ?>
                                <span class="status-badge status-pending">Pending</span>
                            <?php elseif ($application['status'] === 'approved'): ?>
                                <span class="status-badge status-approved">Approved</span>
                            <?php elseif ($application['status'] === 'rejected'): ?>
                                <span class="status-badge status-rejected">Rejected</span>
                            <?php else: ?>
                                <span class="status-badge"><?= htmlspecialchars(ucfirst($application['status'])) ?></span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="application-section">
                    <h3>Internship Description</h3>
                    <p><?= nl2br(htmlspecialchars($application['description'])) ?></p>
                </div>
                
                <div class="application-section">
                    <h3>Your Motivation Letter</h3>
                    <div class="motivation-text">
                        <?= nl2br(htmlspecialchars($application['motivation'])) ?>
                    </div>
                </div>
                
                <div class="application-section">
                    <h3>Your CV</h3>
                    <a href="../Controller/applicationController.php?action=download_cv&id=<?= $application['id_application'] ?>" class="btn">
                        <i class="fa-solid fa-download"></i> Download CV
                    </a>
                </div>
                
                <?php if (!empty($application['feedback']) && $application['status'] !== 'pending'): ?>
                <div class="application-section">
                    <h3>Feedback from Company</h3>
                    <div class="feedback-section">
                        <?= nl2br(htmlspecialchars($application['feedback'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($application['status'] === 'pending'): ?>
                <div class="application-section">
                    <a href="../Controller/applicationController.php?action=cancel&id=<?= $application['id_application'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this application?');">
                        <i class="fa-solid fa-ban"></i> Cancel Application
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>