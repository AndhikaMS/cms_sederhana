<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Sederhana</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .main-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            z-index: 9999 !important;
            width: 250px;
            transition: width .3s ease-in-out !important;
        }
        .main-header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1100 !important;
            background: #fff !important;
        }
        .content-wrapper {
            margin-left: 250px !important;
            margin-top: 56px;
            min-height: 100vh;
            transition: margin-left .3s ease-in-out !important;
            width: calc(100% - 250px) !important;
        }
        .sidebar-collapse .content-wrapper {
            margin-left: 4.6rem !important;
            width: calc(100% - 4.6rem) !important;
        }
        .sidebar-collapse .main-sidebar {
            width: 4.6rem !important;
        }
        .sidebar-collapse .main-sidebar:hover {
            width: 250px !important;
            z-index: 9999 !important;
        }
        .brand-link {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            overflow: hidden;
        }
        .container-fluid {
            width: 100% !important;
            padding-right: 1rem !important;
            padding-left: 1rem !important;
            margin-right: auto !important;
            margin-left: auto !important;
        }
        @media (max-width: 991.98px) {
            .content-wrapper {
                margin-left: 0 !important;
                margin-top: 60px;
                width: 100% !important;
            }
            .sidebar-collapse .content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            .main-sidebar {
                width: 250px;
                transform: translateX(-250px);
                transition: transform .3s ease-in-out !important;
            }
            .sidebar-open .main-sidebar {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto align-items-center flex-row" style="gap: 0.5rem;">
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user-cog mr-2"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </li>
            <?php else: ?>
            <li class="nav-item">
                <span class="nav-link disabled" style="cursor: default;">
                    <i class="far fa-user"></i> Guest
                </span>
            </li>
            <li class="nav-item">
                <a href="login.php" class="btn btn-outline-primary">Login</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-link d-flex align-items-center pr-2">
            <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light ml-2">CMS Sederhana</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="read_posts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'read_posts.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-newspaper"></i>
                            <p>Read Posts</p>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="posts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Posts</p>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
                    <li class="nav-item">
                        <a href="categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Categories</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="invite_codes.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'invite_codes.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Invite Codes</p>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <?php
                            if (isset($_SESSION['user_id'])) {
                                $page_title = '';
                                switch(basename($_SERVER['PHP_SELF'])) {
                                    case 'index.php':
                                        $page_title = 'Dashboard';
                                        break;
                                    case 'posts.php':
                                        $page_title = 'Manage Posts';
                                        break;
                                    case 'categories.php':
                                        $page_title = 'Manage Categories';
                                        break;
                                    case 'add_post.php':
                                        $page_title = 'Add New Post';
                                        break;
                                    case 'edit_post.php':
                                        $page_title = 'Edit Post';
                                        break;
                                    case 'read_posts.php':
                                    case 'view_post.php':
                                        $page_title = 'Read Posts';
                                        break;
                                    default:
                                        $page_title = 'CMS Sederhana';
                                }
                                echo $page_title;
                            }
                            ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid"> 