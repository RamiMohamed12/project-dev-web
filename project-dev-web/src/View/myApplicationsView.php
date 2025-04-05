<?php
// Location: src/View/myApplicationsView.php
// Included by applicationController.php (action=myapps for Students)

// Prevent direct access and check necessary variables
if (!isset($loggedInUserRole) || $loggedInUserRole !== 'student' || !isset($loggedInUserId)) {
    die("Access Denied or missing data.");
}

// Ensure $applications is at least an empty array if it wasn't set by controller (e.g., on error)
$applications = $applications ?? [];

$defaultCompanyPic = '../View/images/default_company.png'; // Ensure path is correct
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Student Dashboard</title>
    <link rel="stylesheet" href="../View/css/styles.css">
    <link rel="stylesheet" href="../View/css/applications.css"> <!-- Ensure this styles the cards -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"> <!-- Use latest FontAwesome -->
    <style>
         /* Add styles for the rating form/section (should be in applications.css or style.css) */
         .rate-company-section {
             margin-top: 15px;
             padding-top: 15px;
             border-top: 1px dashed #eee;
             text-align: center; /* Center button/message */
         }
         .rate-company-section p {
             margin: 5px 0;
             color: #28a745; /* Green for success/already rated message */
             font-style: italic;
         }
         .rate-company-section .btn-sm { /* Style for smaller rating button */
            padding: 5px 10px;
            font-size: 0.9em;
         }
         .rating-form {
             margin-top: 15px;
             padding: 15px;
             background-color:#f8f9fa;
             border: 1px solid #dee2e6;
             border-radius: 5px;
             text-align: left; /* Align form elements left */
         }
         .star-rating-input {
             display: inline-block; /* Keep stars in a line */
             direction: rtl; /* Reverse direction for hover effect */
             text-align: left; /* Align stars left within the block */
         }
         .star-rating-input input[type="radio"] { display: none; } /* Hide radio buttons */
         .star-rating-input label { /* Style stars */
             font-size: 1.6em; /* Slightly larger stars */
             color: #d3d3d3; /* Grey empty star */
             cursor: pointer;
             padding: 0 2px;
             transition: color 0.2s ease-in-out;
             float: right; /* Float right because of rtl direction */
         }
         /* Style stars on hover and when selected (using sibling selectors with RTL) */
         .star-rating-input label:hover,
         .star-rating-input label:hover ~ label, /* Stars to the left on hover */
         .star-rating-input input[type="radio"]:checked ~ label { /* Selected star and those to its left */
             color: #f8b400; /* Gold color */
         }

         .rating-form textarea { width: 100%; margin-top: 10px; min-height: 60px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
         .rating-form button { margin-top: 10px; }
         .rating-form .form-group { margin-bottom: 10px; } /* Spacing within the form */

         /* Ensure base styles and application card styles are loaded from CSS files */

    </style>
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

        <h1><?= htmlspecialchars($pageTitle ?? 'My Applications') ?></h1>

        <!-- Messages -->
        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <!-- Debug info - Can remove in production -->
        <?php
        // error_log("Applications in view: " . (is_array($applications) ? count($applications) : 'not an array'));
        // if (is_array($applications) && count($applications) > 0) { error_log("First application in view: " . print_r($applications[0], true)); }
        ?>

        <!-- Applications List -->
        <div class="applications-container">
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-box-open fa-3x"></i>
                    <p>You haven't applied to any internships yet.</p>
                    <a href="../Controller/offerController.php?action=view" class="btn">Browse Internship Offers</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app):
                     // Ensure keys exist before trying to access them
                     $appId = $app['id_application'] ?? 0; // Assuming 'id_application' is the primary key
                     $companyId = $app['company_id'] ?? null;
                     $hasRated = $app['has_rated'] ?? false; // Get the flag passed from the controller
                ?>
                    <div class="application-card" id="app-card-<?= $appId ?>">
                        <div class="application-header">
                            <?php /* Logo display logic */
                            $companyLogoSrc = $defaultCompanyPic;
                            if (!empty($app['company_picture_mime']) && !empty($app['company_picture'])) {
                                $logoData = is_resource($app['company_picture']) ? stream_get_contents($app['company_picture']) : $app['company_picture'];
                                if ($logoData) { $companyLogoSrc = 'data:' . htmlspecialchars($app['company_picture_mime']) . ';base64,' . base64_encode($logoData); }
                            }
                            ?>
                            <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($app['name_company'] ?? 'Company') ?>" class="company-logo">
                            <h3><?= htmlspecialchars($app['title'] ?? 'Internship Offer') ?></h3>
                        </div>

                        <div class="application-details">
                            <p><strong>Company:</strong> <?= htmlspecialchars($app['name_company'] ?? 'N/A') ?></p>
                            <p><strong>Applied on:</strong> <?= htmlspecialchars(date('M d, Y', strtotime($app['created_at'] ?? ''))) ?></p>
                            <?php if (!empty($app['cv'])): ?>
                                <p><strong>CV:</strong> <?= htmlspecialchars(basename($app['cv'])) ?></p>
                                <?php /* Add download link here if needed */ ?>
                            <?php else: ?>
                                <p><strong>CV:</strong> Not submitted</p>
                            <?php endif; ?>
                        </div>

                        <div class="application-status">
                             <span class="status-badge status-<?= htmlspecialchars(strtolower($app['status'] ?? 'pending')) ?>">
                                <?php /* Status display logic */
                                $status = $app['status'] ?? 'pending';
                                $statusIcon = 'fa-clock'; $statusText = 'Pending';
                                if ($status === 'accepted' || $status === 'approved') { $statusIcon = 'fa-check-circle'; $statusText = 'Accepted'; }
                                elseif ($status === 'rejected') { $statusIcon = 'fa-times-circle'; $statusText = 'Rejected'; }
                                ?>
                               <i class="fa-solid <?= $statusIcon ?>"></i> <?= $statusText ?>
                            </span>
                        </div>

                        <div class="application-content">
                            <div class="motivation-letter">
                                <h4>Motivation Letter</h4>
                                <div class="letter-content">
                                    <?= nl2br(htmlspecialchars($app['cover_letter'] ?? 'Not provided.')) ?>
                                </div>
                            </div>
                            <?php if (!empty($app['feedback'])): ?>
                            <div class="feedback">
                                <h4>Feedback</h4>
                                <div class="feedback-content">
                                    <?= nl2br(htmlspecialchars($app['feedback'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- *** Rating Section *** -->
                        <div class="rate-company-section">
                            <?php if ($companyId): // Only show rating option if company ID is known ?>
                                <?php if ($hasRated): ?>
                                    <p><i class="fa-solid fa-star" style="color: #f8b400;"></i> You've rated this company.</p>
                                <?php else: ?>
                                    <!-- Button to toggle the form -->
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleRatingForm('rating-form-<?= $companyId ?>-<?= $appId ?>')">
                                        <i class="fa-regular fa-star"></i> Rate This Company
                                    </button>
                                    <!-- The Rating Form (Initially Hidden) -->
                                    <div id="rating-form-<?= $companyId ?>-<?= $appId ?>" class="rating-form" style="display:none;">
                                        <h4>Submit Your Rating</h4>
                                        <form method="post" action="../Controller/applicationController.php?action=rate_company">
                                            <input type="hidden" name="company_id" value="<?= $companyId ?>">
                                            <!-- Add application ID if needed for redirect context, though not directly used in rating -->
                                            <input type="hidden" name="application_context_id" value="<?= $appId ?>">

                                            <div class="form-group">
                                                <label>Rating:</label>
                                                <div class="star-rating-input">
                                                    <!-- Star inputs (reversed for CSS hover) -->
                                                    <input type="radio" id="star5-<?= $appId ?>" name="rating_value" value="5" required><label for="star5-<?= $appId ?>" title="5 stars"><i class="fa-solid fa-star"></i></label>
                                                    <input type="radio" id="star4-<?= $appId ?>" name="rating_value" value="4"><label for="star4-<?= $appId ?>" title="4 stars"><i class="fa-solid fa-star"></i></label>
                                                    <input type="radio" id="star3-<?= $appId ?>" name="rating_value" value="3"><label for="star3-<?= $appId ?>" title="3 stars"><i class="fa-solid fa-star"></i></label>
                                                    <input type="radio" id="star2-<?= $appId ?>" name="rating_value" value="2"><label for="star2-<?= $appId ?>" title="2 stars"><i class="fa-solid fa-star"></i></label>
                                                    <input type="radio" id="star1-<?= $appId ?>" name="rating_value" value="1"><label for="star1-<?= $appId ?>" title="1 star"><i class="fa-solid fa-star"></i></label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="comment-<?= $appId ?>">Comment (Optional):</label>
                                                <textarea id="comment-<?= $appId ?>" name="comment" rows="3" placeholder="Share your experience (optional)..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane"></i> Submit Rating</button>
                                             <button type="button" class="btn btn-secondary btn-sm" onclick="toggleRatingForm('rating-form-<?= $companyId ?>-<?= $appId ?>')">Cancel</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                 <p><small>Rating unavailable (Company details missing).</small></p>
                            <?php endif; ?>
                        </div>
                        <!-- *** End Rating Section *** -->

                    </div> <!-- /.application-card -->
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- /.applications-container -->
    </div> <!-- /.container -->

    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>

     <script>
         // Simple JS to toggle the rating form visibility
         function toggleRatingForm(formId) {
             const formDiv = document.getElementById(formId);
             if (formDiv) {
                 formDiv.style.display = (formDiv.style.display === 'none' || formDiv.style.display === '') ? 'block' : 'none';
             }
         }
     </script>
</body>
</html>