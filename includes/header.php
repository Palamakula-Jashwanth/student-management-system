<?php
// includes/header.php
// Ensure session is started in the page including this file, BEFORE any HTML output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }
        
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #212529; /* Dark Bootstrap theme */
            color: #fff;
            transition: all 0.3s;
            min-height: 100vh;
        }

        #sidebar.active {
            margin-left: -250px;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: #1a1e21;
        }

        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #47748b;
        }

        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }

        #sidebar ul li a {
            padding: 10px;
            font-size: 1.1em;
            display: block;
            color: rgba(255,255,255,.75);
            text-decoration: none;
        }

        #sidebar ul li a:hover {
            color: #fff;
            background: #343a40;
        }

        #sidebar ul li.active > a, a[aria-expanded="true"] {
            color: #fff;
            background: #0d6efd;
        }

        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* Top Navigation Bar */
        .top-navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            margin-bottom: 30px;
            border-radius: 5px;
        }
        
    </style>
</head>
<body>
    
<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light top-navbar">
            <div class="container-fluid">

                <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary d-md-none">
                    <i class="bi bi-list"></i>
                    <span>Menu</span>
                </button>

                <div class="ms-auto d-flex align-items-center">
                   <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 me-1"></i> 
                            <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
                            <small class="text-muted d-block" style="font-size: 0.7em; margin-top: -5px; text-align: right;">
                                <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>
                            </small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Application Content Starts Here -->
