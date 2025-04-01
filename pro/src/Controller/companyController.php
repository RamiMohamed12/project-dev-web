<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Model/company.php';

$company = new Company($conn);

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
        $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
        $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
        $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
        $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';

        // Validate phone number using regex
        if (!preg_match('/^\d+$/', $phone)) {
            echo "Error: Phone number must contain only numbers.";
            exit();
        }

        if ($company->create($name, $location, $description, $email, $phone)) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: Could not add company. " . $company->error;
        }
    } elseif ($action == 'delete') {
        $id = (int) $_POST['id'];

        if ($company->delete($id)) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: Could not delete company. " . $company->error;
        }
    }
}

// Fetch all companies to display in the table
$companies = $company->readAll();

?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Manage Companies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/dashboard.css">
    <link rel="stylesheet" type="text/css" href="../View/company.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-cube fa-2x"></i>
                <h2>Admin Panel</h2>
            </div>
            <ul class="nav-links">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="userController.php">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="active">
                    <a href="companyController.php">
                        <i class="fas fa-building"></i>
                        <span>Companies</span>
                    </a>
                </li>
                <li>
                    <a href="internshipController.php">
                        <i class="fas fa-briefcase"></i>
                        <span>Internships</span>
                    </a>
                </li>
                <li>
                    <a href="applicationController.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Applications</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Company Management</h1>
                <div class="user-info">
                    <button id="theme-toggle" class="theme-toggle">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>
            
            <!-- Rest of your form and table content goes here -->
            <div class="form-container">
                <a href="dashboard.php" style="text-decoration: none; font-size: 20px;">
                    <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                </a>
                <h2>Add Company</h2>
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" name="name" placeholder="Name" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" name="location" placeholder="Location" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" placeholder="Description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" name="phone" placeholder="Phone" required>
                    </div>
                    <button type="submit">Add Company</button>
                </form>
            </div>
            
            <div class="table-container">
                <h2>Company List</h2>
                <table border="1" cellpadding="10" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($companies)): ?>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td><?= htmlspecialchars($company['id_company']) ?></td>
                                    <td><?= htmlspecialchars($company['name_company']) ?></td>
                                    <td><?= htmlspecialchars($company['location']) ?></td>
                                    <td><?= htmlspecialchars($company['description']) ?></td>
                                    <td><?= htmlspecialchars($company['email']) ?></td>
                                    <td><?= htmlspecialchars($company['phone_number']) ?></td>
                                    <td>
                                        <form method="post" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $company['id_company'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this company?');">Delete</button>
                                        </form>
                                        <a href="editCompany.php?id=<?= $company['id_company'] ?>">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No companies found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;
        const themeIcon = themeToggle.querySelector('i');
        
        // Vérifier si un thème est déjà enregistré dans localStorage
        const savedTheme = localStorage.getItem('dashboard-theme');
        if (savedTheme) {
            htmlElement.setAttribute('data-theme', savedTheme);
            if (savedTheme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        }
        
        themeToggle.addEventListener('click', () => {
            if (htmlElement.getAttribute('data-theme') === 'light') {
                htmlElement.setAttribute('data-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                localStorage.setItem('dashboard-theme', 'dark');
            } else {
                htmlElement.setAttribute('data-theme', 'light');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                localStorage.setItem('dashboard-theme', 'light');
            }
        });
    </script>
</body>
</html>