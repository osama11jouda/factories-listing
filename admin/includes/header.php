<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Include database connection
include_once '../includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Syrian Factories</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <style>
        body {
            padding-top: 56px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        .table th {
            background-color: #f8f9fa;
        }
        
        /* Map styling */
        #map {
            height: 400px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-industry"></i> Factory Admin
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTop" aria-controls="navbarTop" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTop">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_name']; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user-cog fa-fw"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt fa-fw"></i> Logout
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php" target="_blank" title="View Website">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="factories.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'factories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-industry"></i> Factories
                    </a>
                    <a href="add_factory.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'add_factory.php' ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle"></i> Add Factory
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="categories.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                    <a href="subscriptions.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'subscriptions.php' ? 'active' : ''; ?>">
                        <i class="fas fa-credit-card"></i> Subscriptions
                    </a>
                    <a href="messages.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                    <a href="settings.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-10 ml-sm-auto col-lg-10 px-4 main-content">