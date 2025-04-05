<?php
// Location: /home/demy/project-dev-web/src/View/manageUsersView.php
// Included by userController.php
// Assumes variables: $students, $pilotes, $admins, $loggedInUserRole, $loggedInUserId,
// $canManageAdmins, $canManagePilotes, $pageTitle, $errorMessage, $successMessage,
// $studentPagination, $pilotePagination, $adminPagination

// Prevent direct access & check necessary variables
if (!isset($loggedInUserRole) || !isset($loggedInUserId) || !isset($studentPagination)) {
     die("Direct access not permitted or required data missing.");
}

// Get itemsPerPage from pagination data (passed by controller)
$itemsPerPage = $studentPagination['itemsPerPage'] ?? 4;

// *** Helper function to prepare string data within an array for safe JSON encoding for JavaScript ***
function prepare_data_for_js($data_array, $fields_to_escape = []) {
    if (!is_array($data_array)) {
        return []; // Return empty if not an array
    }
    $processed_array = [];
    foreach ($data_array as $item) {
        if (!is_array($item)) continue; // Skip if item is not an array

        $processed_item = $item; // Copy the item
        foreach ($fields_to_escape as $field) {
            // Check if the field exists and is a string
            if (isset($processed_item[$field]) && is_string($processed_item[$field])) {
                // Replace various newline types with escaped newline for JS
                $processed_item[$field] = str_replace(["\r\n", "\r", "\n"], '\\n', $processed_item[$field]);
                // You could add other replacements here if needed, e.g., for problematic quotes
                // $processed_item[$field] = str_replace("'", "\\'", $processed_item[$field]);
                // $processed_item[$field] = str_replace('"', '\\"', $processed_item[$field]); // json_encode handles quotes well usually
            }
        }
        $processed_array[] = $processed_item;
    }
    return $processed_array;
}

// Prepare the initial data *before* json_encode using the helper function
// Specify fields that might contain problematic characters (especially newlines)
$students_js_safe = prepare_data_for_js($students, ['description', 'location', 'school', 'name', 'email']);
$pilotes_js_safe = prepare_data_for_js($pilotes, ['location', 'name', 'email']); // Adjust fields if pilotes have descriptions etc.
$admins_js_safe = prepare_data_for_js($admins, ['name', 'email']); // Adjust fields if admins have descriptions etc.

// URL for the appropriate dashboard
$dashboardUrl = ($loggedInUserRole === 'admin') ? '../View/admin.php' : '../View/pilote.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
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
                radial-gradient(circle, var(--bg-dots-light) 1px, transparent 1px);
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
            scrollbar-width: thin;
            scrollbar-color: var(--text-secondary) transparent;
        }
        
        .main-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .main-container::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .main-container::-webkit-scrollbar-thumb {
            background-color: var(--text-secondary);
            border-radius: 10px;
        }

        /* Header styles */
        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-primary);
        }
        
        .page-header .breadcrumb {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .page-header .breadcrumb li {
            display: flex;
            align-items: center;
        }
        
        .page-header .breadcrumb li:not(:last-child)::after {
            content: '/';
            margin: 0 0.5rem;
            color: var(--text-secondary);
        }
        
        .page-header .breadcrumb a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .page-header .breadcrumb a:hover {
            color: var(--text-primary);
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

        /* Card styling */
        .card {
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: var(--input-bg);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .card-header h2 i {
            margin-right: 0.75rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            background: var(--input-bg);
            border-top: 1px solid var(--card-border);
            padding: 1rem 1.5rem;
        }

        /* Form styling */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }
        
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
        }

        /* Password strength indicator */
        .password-strength-indicator {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            height: 1.2em;
        }
        
        .password-strength-indicator.weak {
            color: #ef4444;
        }
        
        .password-strength-indicator.medium {
            color: #f59e0b;
        }
        
        .password-strength-indicator.strong {
            color: #22c55e;
        }

        /* User Card List */
        .user-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }
        
        .user-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .user-card-header {
            padding: 1.25rem;
            background: var(--input-bg);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-right: 1rem;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0 0 0.25rem;
            color: var(--text-primary);
        }
        
        .user-info p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .user-card-body {
            padding: 1.25rem;
        }
        
        .user-detail {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
        }
        
        .user-detail:last-child {
            margin-bottom: 0;
        }
        
        .user-detail i {
            margin-right: 0.75rem;
            color: var(--text-secondary);
            width: 16px;
            text-align: center;
            margin-top: 0.2rem;
        }
        
        .user-detail-content {
            flex: 1;
        }
        
        .user-detail-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .user-detail-value {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .user-card-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
        }
        
        .user-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
        }
        
        .user-id i {
            margin-right: 0.5rem;
        }
        
        .user-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 6px;
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
        }

        /* Pagination styling */
        .pagination-controls {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 1.5rem 0;
            gap: 0.5rem;
        }
        
        .pagination-controls button, .pagination-controls span {
            min-width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--card-border);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .pagination-controls button {
            background: var(--bg-secondary);
            color: var(--text-primary);
            cursor: pointer;
        }
        
        .pagination-controls button:hover:not(:disabled) {
            background: var(--input-bg);
            transform: translateY(-2px);
        }
        
        .pagination-controls button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .pagination-controls span.current-page {
            background: var(--gradient-primary);
            color: white;
            border-color: transparent;
        }
        
        .page-info {
            width: 100%;
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* Loading and error indicators */
        .loading-indicator, .table-error {
            padding: 2rem;
            text-align: center;
            font-size: 0.95rem;
            border-radius: 12px;
        }
        
        .loading-indicator {
            color: var(--text-secondary);
            background: var(--input-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        
        .loading-indicator i {
            font-size: 2rem;
            color: #6366f1;
            animation: spin 1.5s linear infinite;
        }
        
        .table-error {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
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
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
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
            --glass-bg: var(--glass-bg-dark);
            --glass-border: var(--glass-border-dark);
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

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fade-in {
            opacity: 0;
            animation: fadeUp 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--card-border);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

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
            
            .user-list {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .user-card-body {
                padding: 1rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
    <script>
        // Keep toggleUserFields separate
        function toggleUserFields() {
            const typeElement = document.getElementById('add_user_type'); if (!typeElement) return;
            const type = typeElement.value;
            const studentFields = document.getElementById('student_specific_fields');
            const piloteCommonFields = document.getElementById('pilote_specific_fields');
            if (studentFields) studentFields.style.display = 'none';
            if (piloteCommonFields) piloteCommonFields.style.display = 'none';
            if (type === 'student' || type === 'pilote') { if (piloteCommonFields) piloteCommonFields.style.display = 'block'; }
            if (type === 'student') { if (studentFields) studentFields.style.display = 'block'; }
            const dobInput = document.getElementById('add_dob'); const yearSelect = document.getElementById('add_year'); const schoolInput = document.getElementById('add_school');
            if (dobInput) dobInput.required = (type === 'student');
            if (yearSelect) yearSelect.required = (type === 'student');
            if (schoolInput) { /* schoolInput.required = (type === 'student'); // Optional */ }
             const locationInput = document.getElementById('add_location');
             const phoneInput = document.getElementById('add_phone');
             if (locationInput) locationInput.required = (type === 'pilote'); // Example
             if (phoneInput) phoneInput.required = (type === 'pilote'); // Example
        }
        // Note: Moved DOMContentLoaded listener to bottom script block
    </script>
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
                    <h3>Admin Panel</h3>
                    <p>User Management</p>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="<?= $dashboardUrl ?>" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="userController.php" class="nav-link active">
                        <i class="fas fa-users-gear"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <?php if ($loggedInUserRole === 'admin'): ?>
                <li class="nav-item">
                    <a href="../Controller/companyController.php" class="nav-link">
                        <i class="fas fa-building"></i>
                        <span>Manage Companies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/internshipController.php" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Manage Offers</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="../Controller/editUser.php?id=<?= $loggedInUserId ?>&type=<?= $loggedInUserRole ?>" class="nav-link">
                        <i class="fas fa-user-pen"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../Controller/settingsController.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
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
                    <span><i class="fas fa-users-gear me-2"></i>User Management</span>
                </a>
                <button class="navbar-toggler" type="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Page header -->
            <div class="page-header fade-in">
                <div>
                    <h1><i class="fa-solid fa-users-gear me-2"></i><?= htmlspecialchars($pageTitle) ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="<?= $dashboardUrl ?>">Dashboard</a></li>
                        <li>User Management</li>
                    </ul>
                </div>
            </div>
            
            <?php if (!empty($errorMessage)): ?><div class="message error-message fade-in"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMessage) ?></div><?php endif; ?>
            <?php if (!empty($successMessage)): ?><div class="message success-message fade-in"><i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMessage) ?></div><?php endif; ?>

            <!-- Add User Card -->
            <div class="card fade-in">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-plus"></i> Add New User</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="addUserForm">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_user_type">User Type:</label>
                                    <select class="form-control" id="add_user_type" name="type" required onchange="toggleUserFields()">
                                        <option value="" disabled selected>-- Select --</option>
                                        <?php if ($loggedInUserRole === 'admin' || $loggedInUserRole === 'pilote'): ?><option value="student">Student</option><?php endif; ?>
                                        <?php if ($canManagePilotes): ?><option value="pilote">Pilote</option><?php endif; ?>
                                        <?php if ($canManageAdmins): ?><option value="admin">Admin</option><?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_name">Name:</label>
                                    <input type="text" class="form-control" id="add_name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_email">Email:</label>
                                    <input type="email" class="form-control" id="add_email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_password">Password:</label>
                                    <input type="password" class="form-control" id="add_password" name="password" required autocomplete="new-password">
                                    <span id="add_password-strength" class="password-strength-indicator"></span>
                                    <small class="text-muted">Min. 8 chars, 1 uppercase, 1 number for Medium strength.</small>
                                </div>
                            </div>
                        </div>

                        <div id="pilote_specific_fields" style="display: none;" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_location">Location:</label>
                                    <input type="text" class="form-control" id="add_location" name="location">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_phone">Phone:</label>
                                    <input type="text" class="form-control" id="add_phone" name="phone">
                                </div>
                            </div>
                        </div>

                        <div id="student_specific_fields" style="display: none;" class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_dob">Date of Birth:</label>
                                    <input type="date" class="form-control" id="add_dob" name="dob">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_year">Year:</label>
                                    <select class="form-control" id="add_year" name="year">
                                        <option value="" disabled selected>-- Select --</option>
                                        <option value="1st">1st</option>
                                        <option value="2nd">2nd</option>
                                        <option value="3rd">3rd</option>
                                        <option value="4th">4th</option>
                                        <option value="5th">5th</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_school">School:</label>
                                    <input type="text" class="form-control" id="add_school" name="school">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="add_description">Description:</label>
                                    <textarea class="form-control" id="add_description" name="description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add User</button>
                    </form>
                </div>
            </div>

            <!-- Student List Section -->
            <div class="card fade-in delay-1">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-graduate"></i> Students</h2>
                </div>
                <div class="card-body">
                    <div id="students-loading" class="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <p>Loading students...</p>
                    </div>
                    <div id="students-error" class="table-error" style="display: none;"></div>
                    <div id="students-list" class="user-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="card-footer">
                    <div id="students-pagination" class="pagination-controls"></div>
                </div>
            </div>

            <!-- Pilote List Section (Admin Only) -->
            <?php if ($canManagePilotes): ?>
            <div class="card fade-in delay-2">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-tie"></i> Pilotes</h2>
                </div>
                <div class="card-body">
                    <div id="pilotes-loading" class="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <p>Loading pilotes...</p>
                    </div>
                    <div id="pilotes-error" class="table-error" style="display: none;"></div>
                    <div id="pilotes-list" class="user-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="card-footer">
                    <div id="pilotes-pagination" class="pagination-controls"></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Admin List Section (Admin Only) -->
            <?php if ($canManageAdmins): ?>
            <div class="card fade-in delay-3">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-shield"></i> Administrators</h2>
                </div>
                <div class="card-body">
                    <div id="admins-loading" class="loading-indicator" style="display: none;">
                        <i class="fas fa-spinner"></i>
                        <p>Loading admins...</p>
                    </div>
                    <div id="admins-error" class="table-error" style="display: none;"></div>
                    <div id="admins-list" class="user-list">
                        <!-- Populated by JS -->
                    </div>
                </div>
                <div class="card-footer">
                    <div id="admins-pagination" class="pagination-controls"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ****** AJAX, Pagination, and Password Strength Script ****** -->
    <script>
        // --- Password Strength Functions ---
        function checkPasswordStrength(password) {
            let strength = 0; let requirements = []; const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password); const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[^A-Za-z0-9]/.test(password);
            if (password.length >= minLength) strength++; else requirements.push(`${minLength}+ characters`);
            if (hasUpperCase) strength++; else requirements.push("1 uppercase letter");
            if (hasNumber) strength++; else requirements.push("1 number");
            if (hasSymbol) strength++;
            if (password.length >= minLength && hasUpperCase && hasNumber) {
                if (hasSymbol && strength >= 4) return { level: 'strong', message: 'Password strength: Strong' };
                else return { level: 'medium', message: 'Password strength: Medium' };
            } else {
                let message = 'Weak. Requires: ' + requirements.join(', ');
                if (password.length === 0) message = ''; // Clear message if empty
                else if (requirements.length === 0 && password.length < minLength) message = `Weak. Requires: ${minLength}+ characters`;
                else if (requirements.length === 0) message = 'Weak. (Error checking)';
                return { level: 'weak', message: message };
            }
        }
        function updateStrengthIndicator(fieldId, strengthData) {
             const indicator = document.getElementById(fieldId + '-strength');
             if (indicator) { indicator.textContent = strengthData.message; indicator.className = 'password-strength-indicator ' + strengthData.level;}
        }
        // --- End Password Strength ---

        // --- AJAX Pagination Variables and Functions ---
        const loggedInUserRoleJS = '<?= $loggedInUserRole ?>';
        const loggedInUserIdJS = <?= $loggedInUserId ?>;
        const itemsPerPageJS = <?= $itemsPerPage ?>;

        // Function to fetch user data
        async function fetchUsersPage(userType, page = 1) {
            const listContainer = document.getElementById(`${userType}-list`);
            const paginationDiv = document.getElementById(`${userType}-pagination`);
            const loadingDiv = document.getElementById(`${userType}-loading`);
            const errorDiv = document.getElementById(`${userType}-error`);

            if (!listContainer || !paginationDiv || !loadingDiv || !errorDiv) { 
                console.error(`Missing HTML elements for ${userType}`); 
                return; 
            }

            loadingDiv.style.display = 'block'; 
            errorDiv.style.display = 'none';
            listContainer.innerHTML = ''; 
            paginationDiv.innerHTML = ''; // Clear previous

            try {
                const response = await fetch(`../Controller/ajax_get_users.php?type=${userType}&page=${page}`);
                if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
                const data = await response.json();
                if (data.error) { throw new Error(data.error); }

                if (data.success) {
                    // Process data *after* fetch for AJAX response as well
                    const safeUsers = processUserDataForJS(data.users, userType);
                    renderUserCards(listContainer, userType, safeUsers);
                    renderPagination(paginationDiv, userType, data.pagination);
                } else { throw new Error('API response indicates failure.'); }
            } catch (error) {
                console.error(`Error fetching ${userType} page ${page}:`, error);
                errorDiv.textContent = `Error loading ${userType}: ${error.message}. Please try again.`;
                errorDiv.style.display = 'block';
                listContainer.innerHTML = `<div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Could not load user data</h3>
                    <p>Please try again or contact support.</p>
                </div>`;
            } finally {
                loadingDiv.style.display = 'none';
            }
        }

        // Function to render user cards
        function renderUserCards(container, userType, users) {
            container.innerHTML = ''; // Clear previous content

            if (!users || users.length === 0) {
                container.innerHTML = `<div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No ${userType} found</h3>
                    <p>Add your first ${userType.substring(0, userType.length-1)} using the form above.</p>
                </div>`;
                return;
            }

            users.forEach(user => {
                // Basic check for essential data
                if (!user || typeof user.user_id === 'undefined' || typeof user.name === 'undefined' || typeof user.email === 'undefined') {
                    console.warn("Skipping user card due to missing essential data:", user);
                    return; // Skip this iteration
                }

                let actionsHtml = '';
                let detailsHtml = '';
                const initials = getInitials(user.name);
                const userId = userType === 'students' ? user.id_student : (userType === 'pilotes' ? user.id_pilote : user.id_admin);

                // Determine actions based on server-provided 'canModify' flag
                if (user.canModify) {
                    actionsHtml = `
                        <a href="editUser.php?id=${user.user_id}&type=${user.user_type}" class="btn btn-warning btn-sm">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>`;
                    if (!(userType === 'admins' && loggedInUserRoleJS === 'admin' && user.user_id === loggedInUserIdJS)) {
                        actionsHtml += `
                            <form method="post" action="userController.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete ${user.user_type} ${escapeHtml(user.name)}? This cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="${user.user_type}">
                                <input type="hidden" name="id" value="${user.user_id}">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>`;
                    } else if (userType === 'admins') {
                        actionsHtml += `<span class="user-id"><i class="fas fa-info-circle"></i>Current user</span>`;
                    }
                } else {
                    // Handle admin viewing self (can edit, not delete via list)
                    if (userType === 'admins' && loggedInUserRoleJS === 'admin' && user.user_id === loggedInUserIdJS) {
                        actionsHtml = `
                            <a href="editUser.php?id=${user.user_id}&type=${user.user_type}" class="btn btn-warning btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <span class="user-id"><i class="fas fa-info-circle"></i>Current user</span>`;
                    } else {
                        actionsHtml = '<span class="user-id"><i class="fas fa-eye"></i>View only</span>';
                    }
                }

                // Build detail fields based on user type
                if (userType === 'students') {
                    detailsHtml = `
                        <div class="user-detail">
                            <i class="fas fa-graduation-cap"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Year</div>
                                <div class="user-detail-value">${escapeHtml(user.year || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-school"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">School</div>
                                <div class="user-detail-value">${escapeHtml(user.school || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Location</div>
                                <div class="user-detail-value">${escapeHtml(user.location || 'N/A')}</div>
                            </div>
                        </div>
                        ${loggedInUserRoleJS === 'admin' ? `
                        <div class="user-detail">
                            <i class="fas fa-user-plus"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Created By</div>
                                <div class="user-detail-value">${escapeHtml(user.created_by_pilote_id || 'Admin/Old')}</div>
                            </div>
                        </div>` : ''}
                    `;
                } else if (userType === 'pilotes') {
                    detailsHtml = `
                        <div class="user-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Location</div>
                                <div class="user-detail-value">${escapeHtml(user.location || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-phone"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Phone</div>
                                <div class="user-detail-value">${escapeHtml(user.phone || 'N/A')}</div>
                            </div>
                        </div>
                    `;
                } else if (userType === 'admins') {
                    detailsHtml = `
                        <div class="user-detail">
                            <i class="fas fa-shield-alt"></i>
                            <div class="user-detail-content">
                                <div class="user-detail-label">Role</div>
                                <div class="user-detail-value">Administrator</div>
                            </div>
                        </div>
                    `;
                }

                // Create user card
                const userCard = document.createElement('div');
                userCard.className = 'user-card';
                userCard.innerHTML = `
                    <div class="user-card-header">
                        <div class="user-avatar">${initials}</div>
                        <div class="user-info">
                            <h3>${escapeHtml(user.name)}</h3>
                            <p>${escapeHtml(user.email)}</p>
                        </div>
                    </div>
                    <div class="user-card-body">
                        ${detailsHtml}
                    </div>
                    <div class="user-card-footer">
                        <div class="user-id"><i class="fas fa-id-card"></i> ID: ${escapeHtml(userId)}</div>
                        <div class="user-actions">${actionsHtml}</div>
                    </div>
                `;
                container.appendChild(userCard);
            });
        }

        // Function to get initials from name
        function getInitials(name) {
            if (!name || typeof name !== 'string') return '?';
            const nameParts = name.trim().split(' ');
            if (nameParts.length === 1) return nameParts[0].charAt(0).toUpperCase();
            return (nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)).toUpperCase();
        }

        // Function to render pagination controls
        function renderPagination(paginationDiv, userType, pagination) {
            paginationDiv.innerHTML = ''; // Clear previous controls
            const { currentPage, totalPages, totalUsers } = pagination;

            if (totalPages <= 0) { paginationDiv.innerHTML = `<span class="page-info">No users found.</span>`; return; }
            if (totalPages === 1) { paginationDiv.innerHTML = `<span class="page-info">Page 1 of 1 (${totalUsers} total)</span>`; return; }

            let paginationHtml = '';
            paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>« Prev</button>`;

            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            if(endPage === totalPages) { startPage = Math.max(1, endPage - maxPagesToShow + 1); }

            if (startPage > 1) {
                 paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', 1)">1</button>`;
                 if (startPage > 2) { paginationHtml += `<span>...</span>`; }
            }
            for (let i = startPage; i <= endPage; i++) {
                 if (i === currentPage) { paginationHtml += `<span class="current-page">${i}</span>`; }
                 else { paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${i})">${i}</button>`; }
            }
            if (endPage < totalPages) {
                  if (endPage < totalPages - 1) { paginationHtml += `<span>...</span>`; }
                 paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${totalPages})">${totalPages}</button>`;
            }

            paginationHtml += `<button type="button" onclick="fetchUsersPage('${userType}', ${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>Next »</button>`;
            paginationHtml += `<span class="page-info">Page ${currentPage} of ${totalPages} (${totalUsers} total users)</span>`;
            paginationDiv.innerHTML = paginationHtml;
        }

        // Helper to escape HTML special characters
        function escapeHtml(unsafe) {
             if (unsafe === null || typeof unsafe === 'undefined') return '';
             const div = document.createElement('div');
             div.textContent = unsafe; // Let the browser handle escaping
             return div.innerHTML;
        }

        // *** JS Helper function to process data (mirroring PHP helper) ***
        function processUserDataForJS(data_array, userType) {
             if (!Array.isArray(data_array)) return [];

             // Define fields potentially containing newlines per user type
             const fieldsToEscapeMap = {
                 students: ['description', 'location', 'school', 'name', 'email'],
                 pilotes: ['location', 'name', 'email'],
                 admins: ['name', 'email']
             };
             const fields_to_escape = fieldsToEscapeMap[userType] || [];

             return data_array.map(item => {
                 if (typeof item !== 'object' || item === null) return item; // Skip non-objects
                 const processed_item = { ...item }; // Shallow copy
                 fields_to_escape.forEach(field => {
                    if (processed_item.hasOwnProperty(field) && typeof processed_item[field] === 'string') {
                         // Replace newlines with escaped newlines for JS strings
                         processed_item[field] = processed_item[field].replace(/\\r\\n|\\r|\\n/g, '\n').replace(/\r\n|\r|\n/g, '\\n');
                     }
                 });
                 return processed_item;
             });
         }


        // --- Initial Setup on DOM Load ---
        document.addEventListener('DOMContentLoaded', () => {
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

            // Toggle fields for Add User form
            toggleUserFields();

            // Attach listeners for Add User password strength
            const addPasswordField = document.getElementById('add_password');
            const addUserForm = document.getElementById('addUserForm');
            if (addPasswordField && addUserForm) {
                 addPasswordField.addEventListener('input', function() {
                     const strengthData = checkPasswordStrength(this.value);
                     updateStrengthIndicator('add_password', strengthData);
                 });
                 addUserForm.addEventListener('submit', function(event) {
                     const strengthData = checkPasswordStrength(addPasswordField.value);
                     if (strengthData.level === 'weak') {
                         event.preventDefault();
                         alert('Password is too weak. Please meet the requirements: Minimum 8 characters, 1 uppercase letter, and 1 number.');
                         updateStrengthIndicator('add_password', strengthData);
                         addPasswordField.focus();
                     }
                 });
             }

            // --- Initial data rendering using PHP variables (processed for JS safety) ---
            try {
                 // Use the PHP-processed variables directly
                 const initialStudents = <?= json_encode($students_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                 const initialStudentPagination = <?= json_encode($studentPagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                 console.log("Initial Students (JS Safe):", initialStudents);
                 renderUserCards(document.getElementById('students-list'), 'students', initialStudents);
                 renderPagination(document.getElementById('students-pagination'), 'students', initialStudentPagination);

                <?php if ($canManagePilotes): ?>
                    const initialPilotes = <?= json_encode($pilotes_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                    const initialPilotePagination = <?= json_encode($pilotePagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                    console.log("Initial Pilotes (JS Safe):", initialPilotes);
                    renderUserCards(document.getElementById('pilotes-list'), 'pilotes', initialPilotes);
                    renderPagination(document.getElementById('pilotes-pagination'), 'pilotes', initialPilotePagination);
                <?php endif; ?>

                <?php if ($canManageAdmins): ?>
                     const initialAdmins = <?= json_encode($admins_js_safe ?: [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
                     const initialAdminPagination = <?= json_encode($adminPagination ?: ['currentPage' => 1, 'totalPages' => 0, 'totalUsers' => 0, 'itemsPerPage' => $itemsPerPage]) ?>;
                     console.log("Initial Admins (JS Safe):", initialAdmins);
                     renderUserCards(document.getElementById('admins-list'), 'admins', initialAdmins);
                     renderPagination(document.getElementById('admins-pagination'), 'admins', initialAdminPagination);
                <?php endif; ?>
                
                // Set current date in footer
                const currentDateElement = document.getElementById('currentDate');
                if (currentDateElement) {
                    const now = new Date();
                    currentDateElement.textContent = now.toLocaleDateString('fr-FR', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            } catch (e) {
                 console.error("Error during initial data rendering:", e);
                 const errorContainer = document.querySelector('.main-container');
                 if (errorContainer) {
                    const initialErrorDiv = document.createElement('div');
                    initialErrorDiv.className = 'message error-message';
                    initialErrorDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation me-2"></i> Error rendering initial user data. Please check console for details.';
                    errorContainer.insertBefore(initialErrorDiv, errorContainer.children[1]);
                 }
            }
        }); // End DOMContentLoaded
    </script>
</body>
</html>