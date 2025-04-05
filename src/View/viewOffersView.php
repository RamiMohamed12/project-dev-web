<?php
// Location: src/View/viewOffersView.php
// Included by offerController.php (action=view)
// Assumes $internships array contains application_count, company_average_rating, company_rating_count

// Basic security check (Ensure essential variables are set)
if (!isset($loggedInUserRole) || !isset($loggedInUserId) || !isset($internships)) {
    die("Access Denied or essential data missing.");
}

// Wishlist model check (only relevant for students)
$wishlistModelAvailable = false;
if ($loggedInUserRole === 'student' && isset($wishlistModel) && is_object($wishlistModel)) {
     $wishlistModelAvailable = true;
} elseif ($loggedInUserRole === 'student') {
    // Attempt to include/instantiate if missed by controller - less ideal but fallback
    try {
        require_once __DIR__ . '/../Model/Wishlist.php';
        if (isset($conn)) { // Check if $conn is available
             $wishlistModel = new Wishlist($conn);
             $wishlistModelAvailable = true;
        }
    } catch (Exception $e) {
        error_log("viewOffersView: Failed to load Wishlist model: " . $e->getMessage());
    }
}

$defaultCompanyPic = '../View/images/default_company.png'; // Ensure path is correct
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : ($loggedInUserRole === 'pilote' ? '../View/pilote.php' : '../View/student.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Internship Offers') ?></title>
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
            --glass-bg-light: rgba(255, 255, 255, 0.7);
            --glass-border-light: rgba(255, 255, 255, 0.5);
            
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
            --glass-bg-dark: rgba(26, 30, 44, 0.7);
            --glass-border-dark: rgba(45, 55, 72, 0.5);
            
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
            --bg-dots: var(--bg-dots-light);
            --glass-bg: var(--glass-bg-light);
            --glass-border: var(--glass-border-light);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Main Layout */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Background elements */
        .bg-gradient-spot {
            position: fixed;
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
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 40px 40px;
            background-image: 
                radial-gradient(circle, var(--bg-dots) 1px, transparent 1px);
            z-index: -1;
            opacity: 0.4;
        }

        /* Sidebar styles */
        .sidebar {
            width: 280px;
            min-height: 100vh;
            padding: 2rem 1.5rem;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid var(--glass-border);
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 2.5rem;
            padding: 0 0.5rem;
        }
        
        .sidebar-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--card-border);
            margin-right: 1rem;
        }
        
        .sidebar-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        
        .sidebar-header p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link i {
            margin-right: 1rem;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: var(--gradient-primary);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            transform: translateY(-3px);
        }
        
        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 0.5rem;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            border: 1px solid var(--card-border);
        }
        
        .logout-btn i {
            margin-right: 1rem;
        }
        
        .logout-btn:hover {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* Main content */
        .main-container {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Header styles */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            margin: 0;
        }
        
        .page-header h1 i {
            margin-right: 0.75rem;
            font-size: 1.6rem;
            color: #6366f1;
        }
        
        .breadcrumbs {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumbs li {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .breadcrumbs li:not(:last-child)::after {
            content: '/';
            margin: 0 0.5rem;
            color: var(--text-secondary);
        }
        
        .breadcrumbs li a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .breadcrumbs li a:hover {
            color: var(--text-primary);
        }

        /* Search and filter styles */
        .search-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }
        
        .search-input-group {
            flex: 1;
            min-width: 200px;
            position: relative;
        }
        
        .search-input-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            padding-right: 3rem;
            border-radius: 8px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .search-input-group button {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            background: none;
            border: none;
            padding: 0 1rem;
            color: var(--text-secondary);
            cursor: pointer;
        }
        
        .search-input-group button:hover {
            color: #6366f1;
        }
        
        .filter-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .filter-group select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
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
        
        .btn-secondary {
            background-color: var(--input-bg);
            color: var(--text-secondary);
            border: 1px solid var(--input-border);
        }
        
        .btn-secondary:hover {
            background-color: var(--card-border);
            color: var(--text-primary);
        }
        
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }
        
        .btn-circle {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-circle i {
            margin: 0;
        }

        /* Offers card grid */
        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .offer-card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .offer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            border-color: #6366f1;
        }
        
        .offer-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .company-logo {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            object-fit: contain;
            background-color: var(--input-bg);
            padding: 5px;
            flex-shrink: 0;
        }
        
        .offer-header-info {
            flex: 1;
        }
        
        .offer-header-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 0.25rem;
        }
        
        .offer-header-info p {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .star-rating {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .star-rating i {
            color: #f59e0b;
            margin-right: 0.1rem;
        }
        
        .star-rating .fa-regular {
            color: var(--card-border);
        }
        
        .rating-count {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-left: 0.5rem;
        }
        
        .no-rating {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-style: italic;
        }
        
        .offer-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .offer-details {
            margin-bottom: 1.25rem;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .detail-item i {
            width: 20px;
            margin-right: 0.75rem;
            color: #6366f1;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-right: 0.5rem;
        }
        
        .detail-value {
            color: var(--text-secondary);
        }
        
        .offer-description {
            position: relative;
            max-height: 100px;
            overflow: hidden;
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }
        
        .offer-description::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 40px;
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, var(--bg-secondary) 100%);
        }
        
        .dark-mode .offer-description::after {
            background: linear-gradient(to bottom, rgba(26,30,44,0) 0%, var(--bg-secondary-dark) 100%);
        }
        
        .offer-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .application-count {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .application-count i {
            margin-right: 0.5rem;
            color: #6366f1;
        }
        
        .offer-actions {
            display: flex;
            gap: 0.75rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }
        
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }
        
        .empty-state p {
            color: var(--text-secondary);
            max-width: 400px;
            margin: 0 auto;
        }

        /* Alert/message styling */
        .message {
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            animation: fadeUp 0.6s ease forwards;
            border: 1px solid transparent;
        }
        
        .message i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
        
        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
        }
        
        .success-message {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.2);
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--card-border);
            padding-top: 1.5rem;
            margin-top: 2rem;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Theme toggle button */
        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            z-index: 100;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(99, 102, 141, 0.4);
        }

        /* Navbar for mobile */
        .navbar {
            display: none;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: var(--navbar-bg-light);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 1.5rem;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
        }
        
        .navbar-brand img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.75rem;
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            color: var(--text-primary);
            background: var(--input-bg);
            border-radius: 8px;
        }

        /* Dark mode */
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
            --bg-dots: var(--bg-dots-dark);
            --glass-bg: var(--glass-bg-dark);
            --glass-border: var(--glass-border-dark);
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            opacity: 0;
            animation: fadeUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Media queries */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1.5rem 0.5rem;
                transform: translateX(-100%);
                z-index: 1050;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-header h3, .sidebar-header p, .nav-link span {
                display: none;
            }
            
            .sidebar-header {
                justify-content: center;
                margin-bottom: 2rem;
            }
            
            .sidebar-header img {
                margin-right: 0;
            }
            
            .nav-link {
                justify-content: center;
                padding: 1rem;
            }
            
            .nav-link i {
                margin-right: 0;
                font-size: 1.5rem;
            }
            
            .logout-btn {
                justify-content: center;
                padding: 1rem;
            }
            
            .logout-btn i {
                margin-right: 0;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .main-container {
                margin-left: 0;
                width: 100%;
            }
            
            .navbar {
                display: flex;
            }
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input-group, .filter-group {
                width: 100%;
            }
            
            .offers-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .offer-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .offer-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Background elements -->
    <div class="bg-gradient-spot bg-gradient-spot-1"></div>
    <div class="bg-gradient-spot bg-gradient-spot-2"></div>
    <div class="bg-grid"></div>
    
    <!-- Theme toggle button -->
    <button id="themeToggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../View/images/default_avatar.png" alt="Profile Picture">
                <div>
                    <h3><?= ucfirst($loggedInUserRole) ?> Dashboard</h3>
                    <p>Internship Offers</p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= $dashboardUrl ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Navigation based on role -->
                <?php if ($loggedInUserRole === 'student'): ?>
                <li class="nav-item">
                    <a href="../Controller/offerController.php?action=view" class="nav-link active">
                        <i class="fas fa-briefcase"></i>
                        <span>View Offers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/wishlistController.php?action=view" class="nav-link">
                        <i class="fas fa-heart"></i>
                        <span>My Wishlist</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/applicationController.php?action=myapps" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>My Applications</span>
                    </a>
                </li>
                <?php elseif ($loggedInUserRole === 'pilote' || $loggedInUserRole === 'admin'): ?>
                <li class="nav-item">
                    <a href="../Controller/userController.php" class="nav-link">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link">
                        <i class="fas fa-file-lines"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/offerController.php?action=view" class="nav-link active">
                        <i class="fas fa-briefcase"></i>
                        <span>View All Offers</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="../Controller/logoutController.php" class="logout-btn">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <div class="main-container">
            <!-- Top navbar for mobile -->
            <nav class="navbar navbar-expand-lg sticky-top d-lg-none">
                <a class="navbar-brand" href="#">
                    <img src="../View/images/default_avatar.png" alt="Profile">
                    <span><i class="fas fa-briefcase me-2"></i>Internship Offers</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <h1><i class="fas fa-briefcase"></i> <?= htmlspecialchars($pageTitle ?? 'Available Internship Offers') ?></h1>
                <ul class="breadcrumbs">
                    <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                    <li>Internship Offers</li>
                </ul>
            </div>
            
            <!-- Messages -->
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Search and filter card -->
            <div class="search-card fade-in">
                <form method="get" action="offerController.php" class="search-form">
                    <input type="hidden" name="action" value="view">
                    
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search by title, company name, or location..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="filter-group">
                        <select name="sort" id="sort-select">
                            <option value="newest" <?= (($_GET['sort'] ?? 'newest') === 'newest') ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= (($_GET['sort'] ?? '') === 'oldest') ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="offerController.php?action=view" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                    </div>
                </form>
            </div>

            <!-- Offers grid -->
            <?php if (empty($internships)): ?>
                <div class="empty-state fade-in delay-1">
                    <i class="fas fa-folder-open"></i>
                    <h3>No internship offers found</h3>
                    <p>There are no internship offers matching your search criteria. Try adjusting your search or check back later for new opportunities.</p>
                </div>
            <?php else: ?>
                <div class="offers-grid">
                    <?php foreach ($internships as $internship):
                        // --- Prepare Rating Display ---
                        $avgRating = $internship['company_average_rating'] ?? null;
                        $ratingCount = $internship['company_rating_count'] ?? 0;
                        $ratingHtml = '';
                        if ($avgRating !== null && $ratingCount > 0) {
                            $roundedRating = round($avgRating); // Round for full/half star logic if needed
                            for ($i = 1; $i <= 5; $i++) {
                                // Simple filled/empty star logic
                                $ratingHtml .= ($i <= $roundedRating) ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                            }
                            $ratingHtml .= "<span class='rating-count'>(" . number_format($avgRating, 1) . " / " . $ratingCount . " rating" . ($ratingCount != 1 ? 's' : '') . ")</span>";
                        } else {
                            $ratingHtml = "<span class='no-rating'>No ratings yet</span>";
                        }

                        // --- Prepare Logo ---
                        $companyLogoSrc = $defaultCompanyPic;
                        if (!empty($internship['company_picture_mime']) && !empty($internship['company_picture'])) {
                            $logoData = is_resource($internship['company_picture']) ? stream_get_contents($internship['company_picture']) : $internship['company_picture'];
                            if ($logoData) {
                                $companyLogoSrc = 'data:' . htmlspecialchars($internship['company_picture_mime']) . ';base64,' . base64_encode($logoData);
                            }
                        }

                        // --- Prepare Application Count ---
                        $appCount = $internship['application_count'] ?? 0;
                        $appCountText = $appCount . " application" . ($appCount != 1 ? 's' : '');

                        // --- Check Wishlist Status ---
                        $isInWishlist = false;
                        if ($wishlistModelAvailable && $loggedInUserRole === 'student' && isset($internship['id_internship'])) {
                            $isInWishlist = $wishlistModel->isInWishlist($loggedInUserId, $internship['id_internship']);
                        }
                    ?>
                        <div class="offer-card fade-in delay-<?= $loop ?? 1 ?>">
                            <div class="offer-header">
                                <img src="<?= $companyLogoSrc ?>" alt="<?= htmlspecialchars($internship['name_company'] ?? 'Company') ?> Logo" class="company-logo">
                                <div class="offer-header-info">
                                    <h3><?= htmlspecialchars($internship['title'] ?? 'Internship Offer') ?></h3>
                                    <p><?= htmlspecialchars($internship['name_company'] ?? 'N/A') ?></p>
                                    <div class="star-rating"><?= $ratingHtml ?></div>
                                </div>
                            </div>
                            <div class="offer-body">
                                <div class="offer-details">
                                    <div class="detail-item">
                                        <i class="fas fa-location-dot"></i>
                                        <span class="detail-label">Location:</span>
                                        <span class="detail-value"><?= htmlspecialchars($internship['company_location'] ?? 'N/A') ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-euro-sign"></i>
                                        <span class="detail-label">Salary:</span>
                                        <span class="detail-value"><?= $internship['remuneration'] ? htmlspecialchars(number_format($internship['remuneration'], 2) . ' €/month') : 'Not specified' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <span class="detail-label">Posted:</span>
                                        <span class="detail-value"><?= htmlspecialchars(date('d M Y', strtotime($internship['offre_date'] ?? ''))) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-users"></i>
                                        <span class="detail-label">Applications:</span>
                                        <span class="detail-value"><?= htmlspecialchars($appCountText) ?></span>
                                    </div>
                                </div>
                                
                                <div class="offer-description">
                                    <?= nl2br(htmlspecialchars($internship['description'] ?? 'No description available.')) ?>
                                </div>
                            </div>
                            <div class="offer-footer">
                                <div class="application-count">
                                    <i class="fas fa-calendar"></i> Posted on <?= htmlspecialchars(date('d M Y', strtotime($internship['offre_date'] ?? ''))) ?>
                                </div>
                                <div class="offer-actions">
                                    <?php if ($loggedInUserRole === 'student'): ?>
                                        <a href="../Controller/applicationController.php?action=apply&id=<?= $internship['id_internship'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-paper-plane"></i> Apply
                                        </a>
                                        <?php if ($wishlistModelAvailable): ?>
                                            <?php if ($isInWishlist): ?>
                                                <a href="../Controller/wishlistController.php?action=remove&id=<?= $internship['id_internship'] ?>&ref=offers" class="btn btn-secondary btn-sm btn-circle" title="Remove from Wishlist">
                                                    <i class="fas fa-heart-crack"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="../Controller/wishlistController.php?action=add&id=<?= $internship['id_internship'] ?>&ref=offers" class="btn btn-secondary btn-sm btn-circle" title="Add to Wishlist">
                                                    <i class="far fa-heart"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="../Controller/internshipController.php?action=edit&id=<?= $internship['id_internship'] ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <footer>
                <p>© <?= date('Y'); ?> Project Dev Web Application - Current time: 2025-04-05 15:48:37</p>
            </footer>
        </div>
    </div>

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
            
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target) && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                });
            }
            
            // Animation delay for offer cards
            const offerCards = document.querySelectorAll('.offer-card');
            offerCards.forEach((card, index) => {
                card.classList.add('delay-' + ((index % 3) + 1));
            });
        });
    </script>
</body>
</html>