<?php
session_start();
// Include configuration file if not already included
if (!function_exists('isLoggedIn')) {
    include_once 'config.php';
}

// Get current page for meta description
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$meta_description = '';
$meta_keywords = 'factories, industrial listing, manufacturing, industry directory';

switch ($current_page) {
    case 'index':
        $meta_description = 'Find and connect with manufacturing factories worldwide. The leading platform for industrial connections and factory listings.';
        break;
    case 'factories':
        $meta_description = 'Browse our comprehensive directory of factories and manufacturing facilities across various industries.';
        break;
    case 'factory-details':
        $meta_description = 'Detailed information about manufacturing facilities, capabilities, and contact details.';
        break;
    case 'about':
        $meta_description = 'Learn more about Factories Listing, the premier platform connecting businesses with manufacturing facilities.';
        break;
    case 'contact':
        $meta_description = 'Contact Factories Listing for inquiries, support, or to list your manufacturing facility.';
        break;
    case 'register':
        $meta_description = 'Register your business or factory on our platform to gain visibility and connect with potential clients.';
        break;
    case 'profile':
        $meta_description = 'Manage your Factories Listing profile and subscription details.';
        break;
    default:
        $meta_description = 'Factories Listing - Your Gateway to Industrial Connections. Find and connect with manufacturing facilities worldwide.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Factories Listing">
    <meta property="og:title" content="<?php echo htmlspecialchars(ucfirst($current_page) . ' - Factories Listing'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo htmlspecialchars(ucfirst($current_page) . ' - Factories Listing'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <title>Factories Listing - Your Gateway to Industrial Connections</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Leaflet Map CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script src="/js/main.js"></script>
</head>
<body>
    <!-- Two-tiered Navigation -->
    <header class="header-fixed-top">
        <!-- Top Navigation Row -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary top-navbar">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <i class="fas fa-industry mr-2"></i>Factories Listing
                </a>
                
                <?php if (!isLoggedIn()): ?>
                    <!-- Move Register/Login button outside collapsible area -->
                    <a class="btn btn-outline-light btn-sm d-inline-block register-btn-fixed" href="/register.php">
                        <i class="fas fa-user-plus mr-1"></i> Register / Login
                    </a>
                <?php endif; ?>
                
                <!-- Removed the toggle button from first row -->
                <div class="collapse navbar-collapse" id="topNavbarContent">
                    <ul class="navbar-nav ml-auto">
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item custom-dropdown">
                                <!-- Simple dropdown toggle with inline JavaScript -->
                                <a class="nav-link user-menu-toggle" href="#" onclick="toggleUserMenu(); return false;">
                                    <i class="fas fa-user-circle mr-1"></i> 
                                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                    <?php if (!hasActiveSubscription()): ?>
                                        <span class="badge badge-warning">Inactive</span>
                                    <?php endif; ?>
                                    <i class="fas fa-caret-down ml-1"></i>
                                </a>
                                <!-- Custom dropdown menu -->
                                <div class="custom-dropdown-menu" id="userDropdownMenu">
                                    <?php if (isAdmin()): ?>
                                        <a class="dropdown-item" href="/admin/dashboard.php">
                                            <i class="fas fa-tachometer-alt mr-2"></i>Admin Dashboard
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="/profile.php">
                                        <i class="fas fa-user-cog mr-2"></i>My Profile
                                    </a>
                                    <a class="dropdown-item" href="/logout.php">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </a>
                                </div>
                                
                                <!-- Inline script for immediate toggle functionality -->
                                <script>
                                function toggleUserMenu() {
                                    const menu = document.getElementById('userDropdownMenu');
                                    if (menu) {
                                        menu.classList.toggle('show');
                                    }
                                }
                                
                                // Close menu when clicking elsewhere
                                document.addEventListener('click', function(e) {
                                    const menu = document.getElementById('userDropdownMenu');
                                    const toggle = document.querySelector('.user-menu-toggle');
                                    if (menu && menu.classList.contains('show') && 
                                        e.target !== toggle && !toggle.contains(e.target) && 
                                        e.target !== menu && !menu.contains(e.target)) {
                                        menu.classList.remove('show');
                                    }
                                });
                                </script>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Second Navigation Row -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-secondary second-navbar">
            <div class="container">
                <!-- Using custom ID for menu toggle button -->
                <button id="menuToggleBtn" class="navbar-toggler" type="button" aria-controls="secondNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="secondNavbarContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="/index.php"><i class="fas fa-home mr-1"></i>Home</a>
                        </li>
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'factories.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="/factories.php"><i class="fas fa-industry mr-1"></i>Factories</a>
                        </li>
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="/about.php"><i class="fas fa-info-circle mr-1"></i>About</a>
                        </li>
                        <li class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">
                            <a class="nav-link" href="/contact.php"><i class="fas fa-envelope mr-1"></i>Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Message display section -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="container mt-4">
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php 
        // Clear the message after displaying
        unset($_SESSION['message']); 
        unset($_SESSION['message_type']); 
        ?>
    <?php endif; ?>
    <main>
