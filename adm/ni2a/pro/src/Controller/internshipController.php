<?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../Model/internship.php'; 

    $internship = new Internship($conn); 

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'add') {
            $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title'])) : '';
            $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
            $duration = isset($_POST['duration']) ? htmlspecialchars(trim($_POST['duration'])) : '';
            $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
            $manager_name = isset($_POST['manager_name']) ? htmlspecialchars(trim($_POST['manager_name'])) : '';
            $manager_email = isset($_POST['manager_email']) ? htmlspecialchars(trim($_POST['manager_email'])) : '';
            $manager_phone = isset($_POST['manager_phone']) ? htmlspecialchars(trim($_POST['manager_phone'])) : '';

            // Validate required field
            if (empty($title) || empty($location) || empty($duration) || empty($manager_name) || empty($manager_email)) {
                echo "Error: Title, location, duration, manager name, and manager email are required fields.";
                exit();
            }

            // Validate email forma
            if (!filter_var($manager_email, FILTER_VALIDATE_EMAIL)) {
                echo "Error: Invalid manager email format.";
                exit();
            }

            // Validate phone number if provide
            if (!empty($manager_phone) && !preg_match('/^\d+$/', $manager_phone)) {
                echo "Error: Manager phone number must contain only numbers.";
                exit();
            }

            $success = $internship->createInternship(
                $title, 
                $location, 
                $duration, 
                $description, 
                $manager_name, 
                $manager_email, 
                $manager_phone
            );

            if ($success) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error: Could not create internship. " . $internship->getError();
            }
        } elseif ($action == 'delete') {
            // Handle internship deletio
            $id = (int) $_POST['id'];
            
            if ($id <= 0) {
                echo "Error: Invalid internship ID.";
                exit();
            }

            $success = $internship->deleteInternship($id);
            
            if ($success) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error: Could not delete internship. " . $internship->getError();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Manage Internships</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../View/dashboard.css">
    <link rel="stylesheet" type="text/css" href="../View/internship.css">
    <script>
        function toggleTheme() {
            const htmlElement = document.documentElement;
            const themeIcon = document.getElementById('theme-icon');
            
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
        }
        
        function filterInternships(filterValue) {
            const rows = document.querySelectorAll('#internship_table tbody tr');
            rows.forEach(row => {
                if (filterValue === 'all' || row.dataset.location === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
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
                <li>
                    <a href="companyController.php">
                        <i class="fas fa-building"></i>
                        <span>Companies</span>
                    </a>
                </li>
                <li class="active">
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
                <h1>Internship Management</h1>
                <div class="user-info">
                    <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()">
                        <i id="theme-icon" class="fas fa-moon"></i>
                    </button>
                    <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin'; ?></span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>
            
            <div class="form-container">
                <h2>Create New Internship</h2>
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label for="title">Internship Title:</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Location:</label>
                        <input type="text" id="location" name="location" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration (months):</label>
                        <input type="number" id="duration" name="duration" min="1" max="12" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="manager_name">Manager Name:</label>
                        <input type="text" id="manager_name" name="manager_name" required>
                    </div>

                    <div class="form-group">
                        <label for="manager_email">Manager Email:</label>
                        <input type="email" id="manager_email" name="manager_email" required>
                    </div>

                    <div class="form-group">
                        <label for="manager_phone">Manager Phone:</label>
                        <input type="tel" id="manager_phone" name="manager_phone">
                    </div>

                    <button type="submit">Create Internship</button>
                </form>
            </div>
            
            <div class="table-container">
                <h2>Internship List</h2>
                
                
                <div id="internship_table">
                    <table border="1" cellpadding="10" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Duration</th>
                                <th>Description</th>
                                <th>Manager</th>
                                <th>Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // This would be populated from your database
                            $internships = $internship->getAllInternships();
                            if (!empty($internships)):
                                foreach ($internships as $internship):
                            ?>
                                <tr data-location="<?= htmlspecialchars($internship['location']) ?>">
                                    <td><?= htmlspecialchars($internship['id']) ?></td>
                                    <td><?= htmlspecialchars($internship['title']) ?></td>
                                    <td><?= htmlspecialchars($internship['location']) ?></td>
                                    <td><?= htmlspecialchars($internship['duration']) ?> months</td>
                                    <td><?= htmlspecialchars(substr($internship['description'], 0, 50)) ?>...</td>
                                    <td><?= htmlspecialchars($internship['manager_name']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($internship['manager_email']) ?><br>
                                        <?= htmlspecialchars($internship['manager_phone']) ?>
                                    </td>
                                    <td>
                                        <a href="editInternship.php?id=<?= $internship['id'] ?>">Edit</a>
                                        <form method="post" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $internship['id'] ?>">
                                            <button type="submit" onclick="return confirm('Are you sure you want to delete this internship?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr><td colspan="8">No internships found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize theme from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('dashboard-theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
                const themeIcon = document.getElementById('theme-icon');
                if (savedTheme === 'dark') {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
            }
        });
    </script>
</body>
</html>