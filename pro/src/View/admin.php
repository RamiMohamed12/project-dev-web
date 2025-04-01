<?php
require_once __DIR__ . '/../Auth/AuthCheck.php';
require_once __DIR__ . '/../Model/UserModel.php';
require_once __DIR__ . '/../Model/CompanyModel.php';
require_once __DIR__ . '/../Model/InternshipModel.php';
require_once __DIR__ . '/../Model/ApplicationModel.php';

AuthCheck::checkUserAuth('admin');

// Récupérer les statistiques depuis la base de données
$userModel = new UserModel();
$companyModel = new CompanyModel();
$internshipModel = new InternshipModel();
$applicationModel = new ApplicationModel();

// Nombre total d'utilisateurs
$totalUsers = $userModel->countUsers();
// Pourcentage d'augmentation (exemple: comparaison avec le mois précédent)
$userIncrease = $userModel->getUserGrowthPercentage();

// Nombre total d'entreprises
$totalCompanies = $companyModel->countCompanies();
$companyIncrease = $companyModel->getCompanyGrowthPercentage();

// Nombre total de stages
$totalInternships = $internshipModel->countInternships();
$internshipIncrease = $internshipModel->getInternshipGrowthPercentage();

// Nombre total de candidatures
$totalApplications = $applicationModel->countApplications();
$applicationIncrease = $applicationModel->getApplicationGrowthPercentage();

// Récupérer les activités récentes
$recentActivities = $userModel->getRecentActivities(5);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        secondary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                },
            },
        }
    </script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .dark .glass {
            background: rgba(17, 25, 40, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        
        .gradient-border {
            position: relative;
            border-radius: 0.75rem;
            overflow: hidden;
        }
        
        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 0.75rem;
            padding: 2px;
            background: linear-gradient(45deg, #38bdf8, #8b5cf6, #ec4899);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .float {
            animation: float 3s ease-in-out infinite;
        }
        
        .float-delay-1 {
            animation-delay: 0.5s;
        }
        
        .float-delay-2 {
            animation-delay: 1s;
        }
        
        .float-delay-3 {
            animation-delay: 1.5s;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Main container with flex layout -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar - Fixed width, no margin -->
        <aside class="w-64 bg-white dark:bg-gray-800 shadow-lg h-full flex flex-col z-50" id="sidebar">
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="relative w-10 h-10">
                        <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-secondary-600 rounded-lg opacity-70 animate-pulse-slow"></div>
                        <div class="absolute inset-0.5 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cube text-xl bg-gradient-to-r from-primary-500 to-secondary-600 bg-clip-text text-transparent"></i>
                        </div>
                    </div>
                    <h1 class="text-xl font-bold bg-gradient-to-r from-primary-500 to-secondary-600 bg-clip-text text-transparent">Admin Panel</h1>
                </div>
                <button class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="p-4 space-y-2 flex-grow overflow-y-auto">
                <a href="admin.php" class="flex items-center space-x-3 p-3 rounded-lg bg-gradient-to-r from-primary-50 to-secondary-50 dark:from-primary-900/30 dark:to-secondary-900/30 text-primary-700 dark:text-primary-300 font-medium">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md bg-primary-100 dark:bg-primary-800/50 text-primary-600 dark:text-primary-300">
                        <i class="fas fa-home"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
                
                <a href="manage_users.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-colors">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-users"></i>
                    </div>
                    <span>Users</span>
                </a>
                
                <a href="manage_companies.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-colors">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-building"></i>
                    </div>
                    <span>Companies</span>
                </a>
                
                <a href="manage_internships.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-colors">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <span>Internships</span>
                </a>
                
                <a href="manage_applications.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-colors">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <span>Applications</span>
                </a>
            </nav>
            <div class="p-4 mt-auto border-t dark:border-gray-700">
                <a href="../Controller/logoutController.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 font-medium transition-colors">
                    <div class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content - Flex grow to fill remaining space -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="sticky top-0 z-40 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border-b dark:border-gray-700 shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center space-x-3">
                        <button class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" id="openSidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Welcome, hmed na9a!</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="theme-toggle" class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block"></i>
                        </button>
                        <div class="relative">
                            <button class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                <i class="fas fa-bell"></i>
                                <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">3</span>
                            </button>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 flex items-center justify-center text-white font-medium">
                                H
                            </div>
                            <span class="hidden md:block font-medium text-gray-700 dark:text-gray-300">hmed na9a</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="gradient-border">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm h-full">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($totalUsers); ?></h3>
                                    <p class="text-sm font-medium <?php echo $userIncrease >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-1">
                                        <i class="fas fa-arrow-<?php echo $userIncrease >= 0 ? 'up' : 'down'; ?> mr-1"></i> <?php echo abs($userIncrease); ?>% <?php echo $userIncrease >= 0 ? 'increase' : 'decrease'; ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="gradient-border">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm h-full">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Companies</p>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($totalCompanies); ?></h3>
                                    <p class="text-sm font-medium <?php echo $companyIncrease >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-1">
                                        <i class="fas fa-arrow-<?php echo $companyIncrease >= 0 ? 'up' : 'down'; ?> mr-1"></i> <?php echo abs($companyIncrease); ?>% <?php echo $companyIncrease >= 0 ? 'increase' : 'decrease'; ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center text-secondary-600 dark:text-secondary-400">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="gradient-border">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm h-full">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Internships</p>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($totalInternships); ?></h3>
                                    <p class="text-sm font-medium <?php echo $internshipIncrease >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-1">
                                        <i class="fas fa-arrow-<?php echo $internshipIncrease >= 0 ? 'up' : 'down'; ?> mr-1"></i> <?php echo abs($internshipIncrease); ?>% <?php echo $internshipIncrease >= 0 ? 'increase' : 'decrease'; ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="gradient-border">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm h-full">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Applications</p>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo number_format($totalApplications); ?></h3>
                                    <p class="text-sm font-medium <?php echo $applicationIncrease >= 0 ? 'text-green-500' : 'text-red-500'; ?> mt-1">
                                        <i class="fas fa-arrow-<?php echo $applicationIncrease >= 0 ? 'up' : 'down'; ?> mr-1"></i> <?php echo abs($applicationIncrease); ?>% <?php echo $applicationIncrease >= 0 ? 'increase' : 'decrease'; ?>
                                    </p>
                                </div>
                                <div class="w-12 h-12 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-pink-600 dark:text-pink-400">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="manage_users.php?action=add" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-4 rounded-full bg-gradient-to-r from-primary-500 to-primary-600 flex items-center justify-center text-white float">
                                <i class="fas fa-user-plus text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Add User</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Create a new user account</p>
                        </a>
                        
                        <a href="manage_companies.php?action=add" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-4 rounded-full bg-gradient-to-r from-secondary-500 to-secondary-600 flex items-center justify-center text-white float float-delay-1">
                                <i class="fas fa-building text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Add Company</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Register a new company</p>
                        </a>
                        
                        <a href="manage_internships.php?action=add" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-4 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 flex items-center justify-center text-white float float-delay-2">
                                <i class="fas fa-briefcase text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Post Internship</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Create a new internship offer</p>
                        </a>
                        
                        <a href="manage_applications.php" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-4 rounded-full bg-gradient-to-r from-pink-500 to-pink-600 flex items-center justify-center text-white float float-delay-3">
                                <i class="fas fa-clipboard-check text-xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Review Applications</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Check pending applications</p>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Recent Activity</h2>
                                <a href="#" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:underline">View All</a>
                            </div>
                            
                            <div class="space-y-5">
                                <?php if (empty($recentActivities)): ?>
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent activities found.</p>
                                <?php else: ?>
                                    <?php foreach ($recentActivities as $index => $activity): ?>
                                        <div class="flex items-start space-x-4">
                                            <div class="relative">
                                                <div class="w-10 h-10 rounded-full 
                                                    <?php 
                                                    switch($activity['type']) {
                                                        case 'user': echo 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'; break;
                                                        case 'company': echo 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400'; break;
                                                        case 'internship': echo 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'; break;
                                                        case 'application': echo 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400'; break;
                                                        default: echo 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
                                                    }
                                                    ?> 
                                                    flex items-center justify-center">
                                                    <i class="fas <?php 
                                                    switch($activity['type']) {
                                                        case 'user': echo 'fa-user-plus'; break;
                                                        case 'company': echo 'fa-building'; break;
                                                        case 'internship': echo 'fa-briefcase'; break;
                                                        case 'application': echo 'fa-file-alt'; break;
                                                        default: echo 'fa-bell';
                                                    }
                                                    ?>"></i>
                                                </div>
                                                <?php if ($index < count($recentActivities) - 1): ?>
                                                    <div class="absolute top-10 left-5 w-0.5 h-full bg-gray-200 dark:bg-gray-700"></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <h3 class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($activity['title']); ?></h3>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($activity['time_ago']); ?></span>
                                                </div>
                                                <p class="text-gray-600 dark:text-gray-300 mt-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                                                <?php if (!empty($activity['link'])): ?>
                                                <div class="mt-2">
                                                    <a href="<?php echo htmlspecialchars($activity['link']); ?>" class="inline-flex items-center text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                                        <span><?php echo htmlspecialchars($activity['link_text']); ?></span>
                                                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                                    </a>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">System Status</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Server Load</span>
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">Healthy</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 25%"></div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Database Status</span>
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">Connected</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Storage Usage</span>
                                        <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Moderate</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: 65%"></div>
                                    </div>
                                </div>
                                
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Memory Usage</span>
                                        <span class="text-sm font-medium text-green-600 dark:text-green-400">Optimal</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: 40%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">Last System Updates</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Database Backup</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Today, 03:45 AM</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">System Scan</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Yesterday, 11:30 PM</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Security Update</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Jul 15, 2023</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;
        
        // Check if a theme is already saved in localStorage
        const savedTheme = localStorage.getItem('dashboard-theme');
        if (savedTheme) {
            htmlElement.classList.toggle('dark', savedTheme === 'dark');
            if (savedTheme === 'dark') {
                document.querySelector('#theme-toggle i.fa-moon').classList.add('hidden');
                document.querySelector('#theme-toggle i.fa-sun').classList.remove('hidden');
            }
        }
        
        themeToggle.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            const isDark = htmlElement.classList.contains('dark');
            
            document.querySelector('#theme-toggle i.fa-moon').classList.toggle('hidden', isDark);
            document.querySelector('#theme-toggle i.fa-sun').classList.toggle('hidden', !isDark);
            
            localStorage.setItem('dashboard-theme', isDark ? 'dark' : 'light');
        });
        
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        
        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
            });
        }
        
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
            });
        }
    </script>
</body>
</html>

