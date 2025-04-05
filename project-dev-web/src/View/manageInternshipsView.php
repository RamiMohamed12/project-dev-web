<?php
// Location: /home/demy/project-dev-web/src/View/manageInternshipsView.php
// Included by internshipController.php (action=list for Admin/Pilote)
// Assumes variables:
// $internships (array of offers), $companiesList (array for dropdown)
// $loggedInUserRole ('admin' or 'pilote'), $loggedInUserId (int)
// $pageTitle, $errorMessage, $successMessage

// Prevent direct access / access by students
if (!isset($loggedInUserRole) || !in_array($loggedInUserRole, ['admin', 'pilote'])) {
    die("Access Denied.");
}

$defaultCompanyPic = 'images/default_company.png'; // ** Relative path from THIS file **

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="../View/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Remove all containers */
        .container, .form-container {
            all: unset !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* Main content spacing */
        .main-content {
            margin-left: 250px;
            padding: 140px 30px 30px 30px;
            background-color: var(--bg-color, #f0f2f5);
        }

        /* Page title with circle plus icon */
        .page-title {
            font-size: 32px !important;
            font-weight: 600 !important;
            color: #2C3E50 !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            border: none !important;
        }

        /* Circle plus icon */
        .circle-plus-icon {
            width: 45px;
            height: 45px;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
        }

        .circle-plus-icon::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid #4B9FFF;
            border-radius: 50%;
            box-sizing: border-box;
        }

        .circle-plus-icon::after {
            content: '+';
            color: #4B9FFF;
            font-size: 35px;
            font-weight: bold;
            line-height: 1;
            margin-top: -2px;
            margin-left: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Section titles */
        section h2 {
            font-size: 28px !important;
            font-weight: 600 !important;
            color: #2C3E50 !important;
            margin: 40px 0 25px !important;
            padding: 0 !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            position: relative !important;
        }

        section h2 i {
            font-size: 24px !important;
            color: #4B9FFF !important;
            background: linear-gradient(135deg, #4B9FFF 0%, #2C7BE5 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            padding: 8px !important;
            border-radius: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        section h2::after {
            content: '' !important;
            position: absolute !important;
            bottom: -8px !important;
            left: 0 !important;
            width: 50px !important;
            height: 4px !important;
            background: linear-gradient(135deg, #4B9FFF 0%, #2C7BE5 100%) !important;
            border-radius: 2px !important;
        }

        /* Form styling */
        form {
            margin-top: 30px;
            margin-bottom: 50px;
            width: 100%;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin-bottom: 12px !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            color: #374151 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        .form-group label i {
            color: #6B7280;
            font-size: 16px;
        }

        /* Input fields styling */
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100% !important;
            padding: 15px 20px !important;
            border: 2px solid #E5E7EB !important;
            border-radius: 25px !important;
            background-color: #fff5e6 !important;
            font-size: 15px !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            color: #374151 !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            box-sizing: border-box !important;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none !important;
            border-color: #3B82F6 !important;
            background-color: #fff !important;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1) !important;
        }

        /* Select specific styling */
        .form-group select {
            appearance: none !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 15px center !important;
            background-size: 15px !important;
            padding-right: 45px !important;
        }

        /* Textarea specific styling */
        .form-group textarea {
            min-height: 120px !important;
            resize: vertical !important;
        }

        /* Submit button styling */
        button[type="submit"] {
            background-color: #3B82F6 !important;
            color: white !important;
            padding: 12px 25px !important;
            border: none !important;
            border-radius: 25px !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            transition: all 0.3s ease !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        button[type="submit"]:hover {
            background-color: #2563EB !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.1) !important;
        }

        /* Table styling */
        table {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            background: white !important;
            border-radius: 15px !important;
            overflow: hidden !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            margin-top: 20px !important;
        }

        thead {
            background: linear-gradient(135deg, #4B9FFF 0%, #2C7BE5 100%) !important;
            color: white !important;
        }

        th {
            padding: 16px 20px !important;
            font-weight: 600 !important;
            font-size: 15px !important;
            text-align: left !important;
            white-space: nowrap !important;
            border: none !important;
            color: white !important;
        }

        td {
            padding: 16px 20px !important;
            font-size: 14px !important;
            border-bottom: 1px solid #EDF2F7 !important;
            color: #2D3748 !important;
        }

        tr:nth-child(even) {
            background-color: #F8FAFC !important;
        }

        tbody tr:hover {
            background-color: #F1F5F9 !important;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-icon {
            color: white;
            font-size: 18px;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-icon:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .nav-icon i {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Footer styling */
        footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            padding: 25px;
            text-align: center;
            margin-left: 250px;
            margin-top: auto;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        }

        footer p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
            font-weight: 500;
        }

        /* Ensure proper layout */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
        }
    </style>
</head>
<body class="admin-layout">
    <!-- Navbar -->
    <nav class="top-navbar">
        <div class="nav-left">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
        </div>
        <div class="nav-right">
            <span class="nav-email"><?= htmlspecialchars($displayEmail ?? '') ?></span>
            <div class="nav-icons">
                <a href="#" class="nav-icon">
                    <i class="fa-solid fa-gear"></i>
                </a>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-icon">
                    <i class="fa-solid fa-user"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="<?= isset($profilePicSrc) ? $profilePicSrc : '../View/images/default_avatar.png' ?>" alt="Profile Picture">
            <h2><?= ucfirst($loggedInUserRole) ?> Panel</h2>
        </div>
        <div class="sidebar-menu">
            <?php if ($loggedInUserRole === 'admin'): ?>
                <a href="../View/admin.php">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
                <a href="../Controller/userController.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="../Controller/companyController.php">
                    <i class="fa-solid fa-building"></i>
                    <span>Manage Companies</span>
                </a>
                <a href="../Controller/internshipController.php" class="active">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Manage Offers</span>
                </a>
            <?php elseif ($loggedInUserRole === 'pilote'): ?>
                <a href="../View/pilote.php">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
                <a href="../Controller/userController.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Students</span>
                </a>
                <a href="../Controller/internshipController.php" class="active">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Manage Offers</span>
                </a>
            <?php endif; ?>
            
            <?php if ($loggedInUserId): ?>
                <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>My Profile</span>
                </a>
            <?php endif; ?>
            
            <a href="../Controller/logoutController.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="message success-message">
                <i class="fa-solid fa-check-circle"></i> 
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-message">
                <i class="fa-solid fa-circle-exclamation"></i> 
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <h1 class="page-title">
            <span class="circle-plus-icon"></span>
            Add New Internship Offer
        </h1>

        <form method="post" action="internshipController.php">
            <input type="hidden" name="action" value="add">

                 <div class="form-group">
                <label>
                    <i class="fa-regular fa-building"></i>
                    Company:
                </label>
                     <select id="add_id_company" name="id_company" required>
                         <option value="" disabled selected>-- Select Company --</option>
                         <?php if (is_array($companiesList) && !empty($companiesList)): ?>
                             <?php foreach ($companiesList as $company): ?>
                                 <option value="<?= htmlspecialchars($company['id_company']) ?>">
                                     <?= htmlspecialchars($company['name_company']) ?> (ID: <?= htmlspecialchars($company['id_company']) ?>)
                                 </option>
                             <?php endforeach; ?>
                         <?php else: ?>
                            <option value="" disabled>No companies available</option>
                         <?php endif; ?>
                     </select>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-briefcase"></i>
                    Title:
                </label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-align-left"></i>
                    Description:
                </label>
                <textarea name="description" required rows="4"></textarea>
                 </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-money-bill"></i>
                    Remuneration (€):
                </label>
                <input type="number" name="remuneration" required min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>
                    <i class="fa-solid fa-calendar"></i>
                    Offer Date:
                </label>
                <input type="date" name="offer_date" required>
         </div>

            <button type="submit">
                <i class="fa-solid fa-plus"></i>
                Add Offer
            </button>
        </form>

        <!-- Internship List Section -->
        <section id="internships">
            <h2>
                <i class="fa-solid fa-list-ul"></i>
                Current Internship Offers
            </h2>
             <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Description</th>
                        <th class="remuneration-col">Remun. (€)</th>
                        <th class="date-col">Offer Date</th>
                        <?php if ($loggedInUserRole === 'admin'): ?>
                            <th>Offer Creator</th>
                            <th>Company Creator</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($internships) && !empty($internships)): ?>
                        <?php foreach ($internships as $offer):
                            // Determine if current user can modify this offer
                            $canModify = ($loggedInUserRole === 'admin' || ($loggedInUserRole === 'pilote' && isset($offer['company_creator_id']) && $offer['company_creator_id'] == $loggedInUserId));
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($offer['id_internship']) ?></td>
                                <td><?= htmlspecialchars($offer['title']) ?></td>
                                <td>
                                    <?php /* Display company logo placeholder */ ?>
                                    <img src="<?= $defaultCompanyPic ?>" alt="Logo" class="company-logo-list" title="<?= !empty($offer['company_picture_mime']) ? 'Logo available' : 'No logo' ?>">
                                    <?= htmlspecialchars($offer['name_company'] ?? 'N/A') ?>
                                    <br><small style="color: #6c757d;"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($offer['company_location'] ?? '') ?></small>
                                </td>
                                <td><div class="description-preview" title="<?= htmlspecialchars($offer['description']) ?>"><?= nl2br(htmlspecialchars($offer['description'])) ?></div></td>
                                <td class="remuneration-col"><?= $offer['remuneration'] !== null ? htmlspecialchars(number_format($offer['remuneration'], 2)) : '-' ?></td>
                                <td class="date-col"><?= htmlspecialchars($offer['offre_date']) ?></td>
                                <?php if ($loggedInUserRole === 'admin'): ?>
                                    <td><?= htmlspecialchars($offer['created_by_pilote_id'] ?? 'Admin') ?></td>
                                    <td><?= htmlspecialchars($offer['company_creator_id'] ?? 'Admin/Old') ?></td>
                                <?php endif; ?>
                                <td class="actions">
                                     <?php if ($canModify): ?>
                                        <a href="internshipController.php?action=edit&id=<?= $offer['id_internship'] ?>" class="edit-btn">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                        
                                        <form method="post" action="internshipController.php" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $offer['id_internship'] ?>">
                                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this internship offer?');">
                                                <i class="fa-solid fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                     <?php else: ?>
                                        <span><i class="fa-solid fa-lock"></i> No permission</span>
                                     <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($loggedInUserRole === 'admin') ? 9 : 7 ?>"> 
                                No internship offers found<?= ($loggedInUserRole === 'pilote') ? ' for companies you manage' : '' ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
    <footer>
        <p>© <?= date('Y'); ?> Project Dev Web Application</p>
    </footer>
</body>
</html>
