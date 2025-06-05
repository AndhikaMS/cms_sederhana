<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'CMS Sederhana'; ?></title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
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
            <li class="nav-item d-none d-sm-inline-block">
                <a href="/" class="nav-link">Home</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="/posts" class="nav-link">Posts</a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="/categories" class="nav-link">Categories</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto align-items-center flex-row" style="gap: 0.5rem;">
            <?php if (\App\Helpers\Functions::isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="/profile" class="dropdown-item">
                            <i class="fas fa-user-cog mr-2"></i> Profile
                        </a>
                        <?php if (\App\Helpers\Functions::hasPermission('manage_users')): ?>
                            <a href="/users" class="dropdown-item">
                                <i class="fas fa-users mr-2"></i> Users
                            </a>
                        <?php endif; ?>
                        <?php if (\App\Helpers\Functions::hasPermission('manage_invites')): ?>
                            <a href="/invite-codes" class="dropdown-item">
                                <i class="fas fa-key mr-2"></i> Invite Codes
                            </a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="/logout" class="dropdown-item">
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
                    <a href="/login" class="btn btn-outline-primary">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="/" class="brand-link d-flex align-items-center pr-2">
            <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light ml-2">CMS Sederhana</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="/" class="nav-link <?php echo $this->isCurrentUrl('/') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/posts" class="nav-link <?php echo $this->isCurrentUrl('/posts') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Posts</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/categories" class="nav-link <?php echo $this->isCurrentUrl('/categories') ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-folder"></i>
                            <p>Categories</p>
                        </a>
                    </li>
                    <?php if (\App\Helpers\Functions::hasPermission('manage_users')): ?>
                        <li class="nav-item">
                            <a href="/users" class="nav-link <?php echo $this->isCurrentUrl('/users') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Users</p>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (\App\Helpers\Functions::hasPermission('manage_invites')): ?>
                        <li class="nav-item">
                            <a href="/invite-codes" class="nav-link <?php echo $this->isCurrentUrl('/invite-codes') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-key"></i>
                                <p>Invite Codes</p>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <?php if (isset($content_header)): ?>
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?php echo $content_header['title'] ?? ''; ?></h1>
                        </div>
                        <?php if (isset($content_header['breadcrumb'])): ?>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <?php foreach ($content_header['breadcrumb'] as $item): ?>
                                        <li class="breadcrumb-item <?php echo $item['active'] ? 'active' : ''; ?>">
                                            <?php if (!$item['active']): ?>
                                                <a href="<?php echo $item['url']; ?>"><?php echo $item['text']; ?></a>
                                            <?php else: ?>
                                                <?php echo $item['text']; ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <?php if (\App\Helpers\Functions::hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo \App\Helpers\Functions::getFlash('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if (\App\Helpers\Functions::hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo \App\Helpers\Functions::getFlash('success'); ?>
                    </div>
                <?php endif; ?>

                <?php echo $content; ?>
            </div>
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="/">CMS Sederhana</a>.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<?php if (isset($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
        <script src="<?php echo $script; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html> 