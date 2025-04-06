<?php
// Start session VERY FIRST
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Redirect if already logged in ---
// Include AFTER starting session
require_once __DIR__ . '/../Auth/AuthSession.php';
if (AuthSession::isUserLoggedIn()) {
    $role = AuthSession::getUserData('user_role');
    $redirectUrl = null;
     switch ($role) {
        case 'admin': $redirectUrl = 'admin.php'; break;
        case 'pilote': $redirectUrl = 'pilote.php'; break;
        case 'student': $redirectUrl = 'student.php'; break;
        default:
             AuthSession::destroySession();
             header("Location: login.php?error=" . urlencode("Invalid session. Please login again."));
             exit();
    }
    if ($redirectUrl) {
        header("Location: " . $redirectUrl);
        exit();
    }
}

// --- Message Handling ---
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars(urldecode($_GET['error']));
}
$success_message = '';
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = "You have been logged out successfully.";
}

// --- Form Persistence ---
$prev_email = isset($_SESSION['login_attempt_email']) ? htmlspecialchars($_SESSION['login_attempt_email']) : '';
$prev_type = isset($_SESSION['login_attempt_type']) ? htmlspecialchars($_SESSION['login_attempt_type']) : '';
unset($_SESSION['login_attempt_email'], $_SESSION['login_attempt_type']); // Clear after use

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Navigui</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Light Theme Colors */
            --bg-primary-light: #f8f9fc;
            --bg-secondary-light: #ffffff;
            --text-primary-light: #1a1e2c;
            --text-secondary-light: #4a5568;
            --card-border-light: #e2e8f0;
            --card-shadow-light: 0 4px 20px rgba(0, 0, 0, 0.05);
            --navbar-bg-light: rgba(255, 255, 255, 0.8);
            --gradient-primary-light: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-light: linear-gradient(135deg, #3b82f6, #2dd4bf);
            --input-bg-light: #f1f5f9;
            --input-border-light: #e2e8f0;
            --button-hover-light: #f1f5f9;
            --bg-gradient-spot1-light: rgba(99, 102, 241, 0.15);
            --bg-gradient-spot2-light: rgba(139, 92, 246, 0.15);
            --bg-dots-light: rgba(99, 102, 241, 0.15);

            /* Dark Theme Colors */
            --bg-primary-dark: #13151e;
            --bg-secondary-dark: #1a1e2c;
            --text-primary-dark: #f1f5f9;
            --text-secondary-dark: #a0aec0;
            --card-border-dark: #2d3748;
            --card-shadow-dark: 0 4px 20px rgba(0, 0, 0, 0.2);
            --navbar-bg-dark: rgba(26, 30, 44, 0.8);
            --gradient-primary-dark: linear-gradient(135deg, #6366f1, #8b5cf6);
            --gradient-accent-dark: linear-gradient(135deg, #3b82f6, #2dd4bf);
            --input-bg-dark: #2d3748;
            --input-border-dark: #4a5568;
            --button-hover-dark: #2d3748;
            --bg-gradient-spot1-dark: rgba(99, 102, 241, 0.2);
            --bg-gradient-spot2-dark: rgba(139, 92, 246, 0.2);
            --bg-dots-dark: rgba(139, 92, 246, 0.15);

            /* Active theme (default to light) */
            --bg-primary: var(--bg-primary-light);
            --bg-secondary: var(--bg-secondary-light);
            --text-primary: var(--text-primary-light);
            --text-secondary: var(--text-secondary-light);
            --card-border: var(--card-border-light);
            --card-shadow: var(--card-shadow-light);
            --gradient-primary: var(--gradient-primary-light);
            --gradient-accent: var(--gradient-accent-light);
            --input-bg: var(--input-bg-light);
            --input-border: var(--input-border-light);
            --button-hover: var(--button-hover-light);
            --bg-gradient-spot1: var(--bg-gradient-spot1-light);
            --bg-gradient-spot2: var(--bg-gradient-spot2-light);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            position: relative; /* Needed for absolute positioning of children */
            overflow-x: hidden;
        }

        /* Futuristic background elements */
        .bg-gradient-spot {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }

        .bg-gradient-spot-1 {
            width: 40vw;
            height: 40vw;
            background: var(--bg-gradient-spot1);
            top: -10%;
            left: -10%;
        }

        .bg-gradient-spot-2 {
            width: 30vw;
            height: 30vw;
            background: var(--bg-gradient-spot2);
            bottom: -5%;
            right: -5%;
        }

        .bg-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 40px 40px;
            background-image:
                radial-gradient(circle, var(--bg-dots-light) 1px, transparent 1px);
            z-index: -1;
            opacity: 0.4;
        }

        .login-container {
            background-color: var(--bg-secondary);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            width: 100%;
            max-width: 380px; /* Reduced from 450px */
            position: relative;
            border: 1px solid var(--card-border);
            backdrop-filter: blur(10px);
            padding: 2rem; /* Reduced from 2.5rem */
            margin: 0 auto;
        }

        /* Ensure container is properly centered vertically */
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
            padding: 1rem;
        }
        .form-title {
            font-size: 1.5rem; /* Reduced from 1.8rem */
            font-weight: 700;
            margin-bottom: 1.5rem; /* Reduced from 2rem */
            text-align: center;
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 10px; /* Reduced from 12px */
            padding: 0.6rem 0.8rem; /* Reduced from 0.8rem 1rem */
            font-size: 0.9rem; /* Reduced from 0.95rem */
            color: var(--text-primary);
            transition: all 0.3s ease;
            margin-bottom: 1rem; /* Reduced from 1.5rem */
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            border-color: #6366f1;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .btn-login {
            background: var(--gradient-primary);
            border: none;
            border-radius: 12px;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(99, 102, 241, 0.4);
        }

        .alert {
            border-radius: 10px; /* Reduced from 12px */
            padding: 0.7rem; /* Reduced from 1rem */
            margin-bottom: 1rem; /* Reduced from 1.5rem */
            border: none;
            font-size: 0.9rem; /* Added to make text smaller */
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .alert-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
        }

        /* Role selection styles */
        .role-selection {
            display: flex;
            justify-content: center;
            gap: 0.5rem; /* Reduced from 1rem */
            margin-bottom: 1.5rem; /* Reduced from 2rem */
        }

        .role-option {
            flex: 1;
            text-align: center;
            cursor: pointer;
            padding: 0.7rem 0.3rem; /* Reduced from 1rem 0.5rem */
            border-radius: 10px; /* Reduced from 12px */
            transition: all 0.3s ease;
            border: 2px solid var(--input-border);
            background-color: var(--input-bg);
            position: relative;
        }

        .role-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .role-option.selected {
            border-color: #6366f1;
            background-color: rgba(99, 102, 241, 0.1);
        }

        .role-icon {
            font-size: 1.5rem; /* Reduced from 2rem */
            margin-bottom: 0.3rem; /* Reduced from 0.5rem */
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .role-option.selected .role-icon {
            color: #6366f1;
        }

        .role-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .role-option.selected .role-name {
            color: #6366f1;
        }

        .role-radio {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeUp {
            animation: fadeUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                padding: 2rem;
            }

            .role-selection {
                flex-direction: column;
                gap: 0.5rem;
            }

            .role-option {
                display: flex;
                align-items: center;
                padding: 0.75rem;
            }

            .role-icon {
                font-size: 1.5rem;
                margin-bottom: 0;
                margin-right: 1rem;
            }
            /* Adjust button position on small screens */
            .dashboard-button-top-left {
                top: 10px;
                left: 10px;
            }
            .theme-toggle {
                top: 10px;
                right: 10px;
            }
        }

        /* Dark mode styles */
        .dark-mode {
            --bg-primary: var(--bg-primary-dark);
            --bg-secondary: var(--bg-secondary-dark);
            --text-primary: var(--text-primary-dark);
            --text-secondary: var(--text-secondary-dark);
            --card-border: var(--card-border-dark);
            --card-shadow: var(--card-shadow-dark);
            --input-bg: var(--input-bg-dark);
            --input-border: var(--input-border-dark);
            --button-hover: var(--button-hover-dark);
            --bg-gradient-spot1: var(--bg-gradient-spot1-dark);
            --bg-gradient-spot2: var(--bg-gradient-spot2-dark);
        }

        /* General Button Styles (already present, kept for context) */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
        }

        .btn-warning:hover, .btn-danger:hover {
            transform: translateY(-2px);
            color: white;
        }

        .btn-sm {
            padding: 0.1rem 0.5rem;
            font-size: 0.85rem;
            border-radius: 6px;
        }

        /* *** NEW/MODIFIED CSS for Top-Left Button *** */
        .dashboard-button-top-left {
            position: absolute; /* Position relative to the nearest positioned ancestor (body) */
            top: 20px;         /* Distance from the top edge */
            left: 20px;        /* Distance from the left edge */
            z-index: 10;       /* Ensure it's above other elements like background spots */
        }
        /* Optional: Adjust button size if needed, original was btn-lg */
        /* .dashboard-button-top-left .btn { */
            /* padding: 0.5rem 1rem; */
            /* font-size: 0.9rem; */
        /* } */


    </style>
</head>
<body>
   <!-- *** MOVED TO TOP LEFT VIA CSS *** -->
   <div class="dashboard-button-top-left fade-in">
        <!-- *** RENAMED TEXT *** -->
        <a href="/../../landing/index.html" class="btn btn-primary btn-lg">
            <i class="fas fa-arrow-left"></i> Back to Landing Page
        </a>
    </div>


    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>

    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <!-- *** THIS CONTAINER REMAINS CENTERED *** -->
    <div class="container">
        <div class="login-container animate-fadeUp">
            <h2 class="form-title">Sign In</h2>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_message ?>
                </div>
            <?php endif; ?>

            <form method="post" action="../Controller/loginController.php">
                <!-- Role selection with icons -->
                <div class="role-selection animate-fadeUp delay-1">
                    <label class="role-option" id="admin-option">
                        <input type="radio" name="user_type" value="admin" class="role-radio" <?= ($prev_type === 'admin') ? 'checked' : '' ?>>
                        <div class="role-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="role-name">Admin</div>
                    </label>

                    <label class="role-option" id="pilote-option">
                        <input type="radio" name="user_type" value="pilote" class="role-radio" <?= ($prev_type === 'pilote') ? 'checked' : '' ?>>
                        <div class="role-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="role-name">Pilote</div>
                    </label>

                    <label class="role-option" id="student-option">
                        <input type="radio" name="user_type" value="student" class="role-radio" <?= ($prev_type === 'student') ? 'checked' : '' ?>>
                        <div class="role-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="role-name">Student</div>
                    </label>
                </div>

                <div class="mb-2 animate-fadeUp delay-2"> <!-- Changed from mb-3 -->
                    <label for="email" class="form-label">
                        <i class="fa-solid fa-envelope me-2"></i>Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="name@example.com" value="<?= $prev_email ?>" required>
                </div>

                <div class="mb-2 animate-fadeUp delay-2"> <!-- Changed from mb-3 -->
                    <label for="password" class="form-label">
                        <i class="fa-solid fa-lock me-2"></i>Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="••••••••" required>
                </div>

                <div class="d-grid gap-2 mt-3 animate-fadeUp delay-3"> <!-- Changed from mt-4 -->
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </div>
            </form>

            <div class="text-center mt-4 text-secondary animate-fadeUp delay-3">
                <small>© 2025 Navigui. All rights reserved.</small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const icon = themeToggle.querySelector('i');

            // Check for saved theme preference or use system preference
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }

            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');

                if (body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    localStorage.setItem('theme', 'dark');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    localStorage.setItem('theme', 'light');
                }
            });

            // Role selection functionality
            const roleOptions = document.querySelectorAll('.role-option');
            const roleRadios = document.querySelectorAll('.role-radio');

            // Initialize selected state
            roleRadios.forEach(radio => {
                if (radio.checked) {
                    radio.parentElement.classList.add('selected');
                }
            });

            // If none is selected, select the first one by default
            // EDIT: Let's keep the previously attempted type if available,
            // otherwise select the first one.
            if (!document.querySelector('.role-radio:checked')) {
                 const firstRoleOption = roleOptions[0];
                 if (firstRoleOption) {
                    firstRoleOption.classList.add('selected');
                    firstRoleOption.querySelector('.role-radio').checked = true;
                 }
            }


            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    roleOptions.forEach(opt => opt.classList.remove('selected'));

                    // Add selected class to clicked option
                    this.classList.add('selected');

                    // Check the radio button
                    const radio = this.querySelector('.role-radio');
                    radio.checked = true;
                });
            });

            // Form validation with visual feedback
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input[type="email"], input[type="password"]');

            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.checkValidity()) {
                        this.classList.add('is-valid');
                        this.classList.remove('is-invalid');
                    } else if (this.value !== '') {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                });

                input.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.transition = 'transform 0.3s ease';
                });

                input.addEventListener('blur', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    // Add visual feedback for all fields
                    inputs.forEach(input => {
                        if (!input.checkValidity() && input.value !== '') {
                            input.classList.add('is-invalid');
                        }
                    });

                    // Check if a role is selected
                    if (!document.querySelector('.role-radio:checked')) {
                        // Add some visual indication for role selection error if desired
                        // Example: document.querySelector('.role-selection').style.border = '1px solid red';
                    }
                }
            });
        });
    </script>
</body>
</html>
