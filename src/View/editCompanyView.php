<?php
// Location: /home/demy/project-dev-web/src/View/editCompanyView.php
// Included by editCompany.php controller
// Assumes variables: $companyDetails (array), $pageTitle, $errorMessage, $successMessage
// Assumes $loggedInUserRole is available (implicitly via AuthSession::getUserData)

// Prevent direct access
if (!isset($companyDetails)) { die("Direct access not permitted."); }

// Back link logic
$backUrl = 'companyController.php'; $backText = 'Back to Company List';

// Helper function for picture Data URI
function generateCompanyPicDataUri($mime, $data) { if (!empty($mime) && !empty($data)) { $picData = is_resource($data) ? stream_get_contents($data) : $data; if ($picData) { return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData); } } return null; }
$companyPicSrc = generateCompanyPicDataUri($companyDetails['company_picture_mime'] ?? null, $companyDetails['company_picture'] ?? null);
$defaultCompanyPic = '../View/images/default_company.png'; // ** ADJUST PATH AS NEEDED **

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
     <link rel="stylesheet" type="text/css" href="../View/style.css"> <!-- Correct path -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Reusing styles from style.css - Add specifics here */
        .company-pic-preview { /* Specific class for company */
            width: 150px; /* Slightly larger maybe */
            height: auto; /* Maintain aspect ratio */
            max-height: 120px; /* Limit height */
            border-radius: 5px; /* Less rounded */
            object-fit: contain; /* Show whole logo */
            border: 3px solid #eaecf4;
            margin-bottom: 15px;
            display: block;
            background-color: #f8f9fc;
        }
         /* Make sure other styles from style.css apply correctly */
        body { font-family: sans-serif; margin: 0; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 700px; margin: auto; background-color: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-top: 0; margin-bottom: 25px; }
        .message { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; border: 1px solid transparent; }
        .error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: bold; color: #555;}
        .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="url"], .form-group input[type="file"], .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        .form-group input:focus, .form-group textarea:focus, .form-group input[type="url"]:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25); }
        .form-group textarea { min-height: 80px; }
        .form-group small { font-size: 0.85em; color: #6c757d; display: block; margin-top: 5px; }
        button[type="submit"] { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.05em; }
        button[type="submit"]:hover { background-color: #218838; }
        button[type="submit"] i { margin-right: 8px; }
        .back-link { display: inline-block; margin-bottom: 25px; text-decoration: none; color: #007bff; font-size: 1.1em; }
        .back-link:hover { text-decoration: underline; }
        .back-link i { margin-right: 5px; }
        .remove-pic-label { font-size: 0.9em; margin-left: 10px; color: #dc3545; cursor: pointer; vertical-align: middle;}
        #remove_company_pic { vertical-align: middle; width: auto; margin-right: 5px;}

    </style>
</head>
<body>
     <div class="container">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= htmlspecialchars($backText) ?>
        </a>
        <h2><?= htmlspecialchars($pageTitle) ?></h2>

        <?php if (!empty($errorMessage)): ?><div class="message error-message"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
        <?php if (!empty($successMessage)): ?><div class="message success-message"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

        <!-- ***** ADDED enctype ***** -->
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">

            <!-- ***** ADDED Company Picture Section ***** -->
            <div class="form-group">
                <label><i class="fa-solid fa-image"></i> Current Company Picture/Logo:</label>
                <img src="<?= $companyPicSrc ?? $defaultCompanyPic ?>" alt="Company Picture" class="company-pic-preview" id="picPreview">
                 <label for="company_picture">Upload New Picture (JPG, PNG, GIF, WebP - Max 2MB):</label>
                 <input type="file" id="company_picture" name="company_picture" accept=".jpg,.jpeg,.png,.gif,.webp" onchange="previewFile()">
                 <?php if ($companyPicSrc): ?>
                    <br>
                    <input type="checkbox" id="remove_company_pic" name="remove_company_pic" value="1">
                    <label for="remove_company_pic" class="remove-pic-label">Remove current picture</label>
                 <?php endif; ?>
                 <small>Recommended size: e.g., 300x150 pixels.</small>
            </div>
            <!-- ***** END Picture Section ***** -->

            <div class="form-group"> <label for="name"><i class="fa-regular fa-building"></i> Company Name:</label> <input type="text" id="name" name="name" value="<?= htmlspecialchars($companyDetails['name_company'] ?? '') ?>" required> </div>
            <div class="form-group"> <label for="location"><i class="fa-solid fa-location-dot"></i> Location:</label> <input type="text" id="location" name="location" value="<?= htmlspecialchars($companyDetails['location'] ?? '') ?>" required> </div>
            <div class="form-group"> <label for="description"><i class="fa-solid fa-align-left"></i> Description:</label> <textarea id="description" name="description" rows="4"><?= htmlspecialchars($companyDetails['description'] ?? '') ?></textarea> </div>
            <div class="form-group"> <label for="email"><i class="fa-regular fa-envelope"></i> Email:</label> <input type="email" id="email" name="email" value="<?= htmlspecialchars($companyDetails['email'] ?? '') ?>" required> </div>
            <div class="form-group"> <label for="phone"><i class="fa-solid fa-phone"></i> Phone:</label> <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($companyDetails['phone_number'] ?? '') ?>" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number"> </div>
             <!-- ***** ADDED URL FIELD ***** -->
             <div class="form-group">
                 <label for="url"><i class="fa-solid fa-link"></i> Website URL:</label>
                 <input type="url" id="url" name="url" value="<?= htmlspecialchars($companyDetails['company_url'] ?? '') ?>" placeholder="https://www.example.com">
             </div>

             <?php
                // Optionally display creator ID if admin is viewing
                // Use AuthSession::getUserData here for reliability
                if (isset($companyDetails['created_by_pilote_id']) && AuthSession::getUserData('user_role') === 'admin') {
                    echo '<p><small>Managed by Pilote ID: ' . htmlspecialchars($companyDetails['created_by_pilote_id']) . '</small></p>';
                }
             ?>

            <button type="submit"><i class="fa-solid fa-save"></i> Update Company</button>
        </form>
    </div>

     <script>
        // Preview function for company picture
        function previewFile() {
            const preview = document.getElementById('picPreview');
            const fileInput = document.getElementById('company_picture'); // Use correct ID
            if (!preview || !fileInput || !fileInput.files || fileInput.files.length === 0) return;
            const file = fileInput.files[0];
            const reader = new FileReader();
            reader.addEventListener("load", () => { preview.src = reader.result; }, false);
            if (file) { reader.readAsDataURL(file); }
            // Optionally clear remove checkbox if new file selected
            const removeCheckbox = document.getElementById('remove_company_pic');
            if (removeCheckbox) removeCheckbox.checked = false;
        }
    </script>
</body>
</html>
