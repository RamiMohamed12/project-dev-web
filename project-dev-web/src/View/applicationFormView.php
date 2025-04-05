<?php
// Location: src/View/applicationFormView.php
// Included by applicationController.php (action=apply for Students)

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
    <title>Apply for Internship - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-theme">
    <header>
        <h1>
            <i class="fa-solid fa-paper-plane"></i> Apply for Internship
        </h1>
        <nav>
            <ul>
                <li><a href="../View/student.php">Dashboard</a></li>
                <li><a href="../Controller/offerController.php?action=view">View Offers</a></li>
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
        <a href="javascript:history.back()" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Previous Page
        </a>
        
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        
        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        
        <!-- Internship Details -->
        <div class="internship-summary">
            <div class="internship-header">
                <?php 
                $companyLogoSrc = $defaultCompanyPic;
                if (!empty($internshipDetails['company_picture']) && !empty($internshipDetails['company_picture_mime'])) {
                    $logoData = is_resource($internshipDetails['company_picture']) ? stream_get_contents($internshipDetails['company_picture']) : $internshipDetails['company_picture'];
                    if ($logoData) {
                        $companyLogoSrc = 'data:' . htmlspecialchars($internshipDetails['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                    }
                }
                ?>
                <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($internshipDetails['name_company'] ?? 'Company') ?>" class="company-logo">
                <h3><?= htmlspecialchars($internshipDetails['title']) ?></h3>
            </div>
            <div class="internship-details">
                <p><strong>Company:</strong> <?= htmlspecialchars($internshipDetails['name_company'] ?? 'N/A') ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($internshipDetails['company_location'] ?? 'N/A') ?></p>
                <p><strong>Salary:</strong> <?= htmlspecialchars($internshipDetails['remuneration'] ?? 'N/A') ?> €/month</p>
                <p><strong>Duration:</strong> <?= htmlspecialchars($internshipDetails['duration'] ?? 'N/A') ?> months</p>
            </div>
        </div>
        
        <!-- Application Form -->
        <div class="application-form-container">
            <form method="post" action="../Controller/applicationController.php?action=submit" enctype="multipart/form-data" class="application-form">
                <input type="hidden" name="internship_id" value="<?= $internshipDetails['id_internship'] ?>">
                
                <div class="form-group">
                    <label for="motivation_letter">Motivation Letter:</label>
                    <textarea id="motivation_letter" name="motivation_letter" rows="10" required><?= htmlspecialchars($_POST['motivation_letter'] ?? '') ?></textarea>
                    <p class="form-hint">Explain why you're interested in this internship and why you would be a good fit.</p>
                </div>
                
                <div class="form-group">
                    <label for="cv_file">Upload CV (Optional):</label>
                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx">
                    <p class="form-hint">Accepted formats: PDF, DOC, DOCX. Max size: 5MB.</p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Submit Application
                    </button>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fa-solid fa-ban"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>