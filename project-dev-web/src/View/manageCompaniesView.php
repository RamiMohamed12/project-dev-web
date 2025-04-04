<?php
// Location: /home/demy/project-dev-web/src/View/manageCompaniesView.php
// Included by companyController.php
// Assumes variables: $companies, $loggedInUserRole, $loggedInUserId, $pageTitle, $errorMessage, $successMessage

// Prevent direct access
if (!isset($loggedInUserRole) || !isset($loggedInUserId)) {
    die("Direct access not permitted.");
}

// Helper for profile pic
function generateProfilePicDataUri($mime, $data) { 
    if (!empty($mime) && !empty($data)) { 
        $picData = is_resource($data) ? stream_get_contents($data) : $data; 
        if ($picData) { 
            return 'data:' . htmlspecialchars($mime) . ';base64,' . base64_encode($picData); 
        } 
    } 
    return null; 
}

// Get the logged-in user's profile picture
$profilePicSrc = null;
if (isset($loggedInUserDetails)) {
    $profilePicSrc = generateProfilePicDataUri(
        $loggedInUserDetails['profile_picture_mime'] ?? null,
        $loggedInUserDetails['profile_picture'] ?? null
    );
}
$defaultPic = '../View/images/default_avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../View/js/darkMode.js"></script>
    <style>
        /* Additional styles specific to manage companies */
        .container {
            max-width: 1200px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="url"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: 'Poppins', sans-serif;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        .actions a,
        .actions button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        section {
            margin-bottom: 40px;
        }

        section h2 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }

        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid transparent;
            font-family: 'Poppins', sans-serif;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        table a i.fa-link {
            margin-right: 4px;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>
            <img src="<?= $profilePicSrc ?? $defaultPic ?>" alt="PFP" class="profile-pic-header">
            <span><?= htmlspecialchars($loggedInUserDetails['name'] ?? 'User') ?></span>
        </h1>
        <nav>
            <ul class="main-menu">
                <li><a href="../View/admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="../Controller/userController.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="../Controller/companyController.php" class="active"><i class="fa-solid fa-building"></i> Manage Companies</a></li>
                <li><a href="../Controller/offerController.php"><i class="fa-solid fa-file-alt"></i> Manage Offers</a></li>
                <?php if ($loggedInUserId): ?>
                    <li><a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=admin"><i class="fa-solid fa-user-pen"></i> My Profile</a></li>
                <?php endif; ?>
            </ul>
            <ul class="bottom-menu">
                <li><a href="../Controller/logoutController.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <li>
                    <button id="theme-toggle" class="theme-switch">
                        <i class="fa-solid fa-sun sun-icon"></i>
                        <i class="fa-solid fa-moon moon-icon"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="container">
            <!-- Messages -->
            <?php if (!empty($errorMessage)): ?>
                <div class="message error-message">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="message success-message">
                    <i class="fa-solid fa-check-circle"></i>
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <!-- Add Company Form -->
            <div class="form-container">
                <h2><i class="fa-solid fa-building-circle-arrow-right"></i> Add New Company</h2>
                <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group"> <label for="add_name"><i class="fa-regular fa-building"></i> Company Name:</label> <input type="text" id="add_name" name="name" required> </div>
                    <div class="form-group"> <label for="add_location"><i class="fa-solid fa-location-dot"></i> Location:</label> <input type="text" id="add_location" name="location" required> </div>
                    <div class="form-group"> <label for="add_description"><i class="fa-solid fa-align-left"></i> Description:</label> <textarea id="add_description" name="description" rows="3"></textarea> </div>
                    <div class="form-group"> <label for="add_email"><i class="fa-regular fa-envelope"></i> Email:</label> <input type="email" id="add_email" name="email" required> </div>
                    <div class="form-group"> <label for="add_phone"><i class="fa-solid fa-phone"></i> Phone:</label> <input type="text" id="add_phone" name="phone" required pattern="^\+?[0-9\s\-()]+$" title="Enter a valid phone number"> </div>
                    <!-- ***** ADDED URL FIELD ***** -->
                    <div class="form-group">
                        <label for="add_url"><i class="fa-solid fa-link"></i> Website URL (Optional):</label>
                        <input type="url" id="add_url" name="url" placeholder="https://www.example.com">
                    </div>
                    <button type="submit"><i class="fa-solid fa-plus"></i> Add Company</button>
                </form>
            </div>

            <!-- Company List Section -->
            <section id="companies">
                <h2><i class="fa-solid fa-list-ul"></i> Company List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Website</th>
                            <?php if ($loggedInUserRole === 'admin'): ?>
                                <th>Created By</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($companies) && !empty($companies)): ?>
                            <?php foreach ($companies as $company):
                                $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($company['created_by_pilote_id']) && $company['created_by_pilote_id'] == $loggedInUserId));
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($company['id_company']) ?></td>
                                    <td><?= htmlspecialchars($company['name_company']) ?></td>
                                    <td><?= htmlspecialchars($company['location']) ?></td>
                                    <td><?= htmlspecialchars($company['email']) ?></td>
                                    <td><?= htmlspecialchars($company['phone_number']) ?></td>
                                    <td>
                                        <?php if (!empty($company['company_url'])): ?>
                                            <a href="<?= htmlspecialchars($company['company_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($company['company_url']) ?>">
                                                <i class="fa-solid fa-link"></i> Visit Site
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #888;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($loggedInUserRole === 'admin'): ?>
                                        <td><?= htmlspecialchars($company['created_by_pilote_id'] ?? 'Admin/Old') ?></td>
                                    <?php endif; ?>
                                    <td class="actions">
                                        <?php if ($canModify): ?>
                                            <a href="editCompany.php?id=<?= $company['id_company'] ?>" class="edit-btn" title="Edit Company">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" style="display: inline;" onsubmit="return confirm('Delete company <?= htmlspecialchars(addslashes($company['name_company'])) ?>? Check related internships.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $company['id_company'] ?>">
                                                <button type="submit" class="delete-btn" title="Delete Company">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span>(View Only)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= ($loggedInUserRole === 'admin') ? 8 : 7 ?>">
                                    No companies found<?= ($loggedInUserRole === 'pilote') ? ' created by you' : '' ?>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</body>
</html>
