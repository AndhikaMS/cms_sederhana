<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/user_functions.php';

// Get statistics based on role
$stats = [];
if ($_SESSION['role'] === 'admin') {
    // Admin stats
    $query = "SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM posts) as total_posts,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM posts WHERE status = 'published') as published_posts,
        (SELECT COUNT(*) FROM posts WHERE status = 'draft') as draft_posts";
    $result = mysqli_query($conn, $query);
    $stats = mysqli_fetch_assoc($result);
} elseif ($_SESSION['role'] === 'editor') {
    // Editor stats
    $query = "SELECT 
        (SELECT COUNT(*) FROM posts) as total_posts,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM posts WHERE status = 'published') as published_posts,
        (SELECT COUNT(*) FROM posts WHERE status = 'draft') as draft_posts";
    $result = mysqli_query($conn, $query);
    $stats = mysqli_fetch_assoc($result);
} else {
    // Author stats
    $user_id = $_SESSION['user_id'];
    $query = "SELECT 
        (SELECT COUNT(*) FROM posts WHERE user_id = $user_id) as total_posts,
        (SELECT COUNT(*) FROM posts WHERE user_id = $user_id AND status = 'published') as published_posts,
        (SELECT COUNT(*) FROM posts WHERE user_id = $user_id AND status = 'draft') as draft_posts";
    $result = mysqli_query($conn, $query);
    $stats = mysqli_fetch_assoc($result);
}

// Get latest posts based on role
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor') {
    $query = "SELECT p.*, c.name as category_name, u.username as author_name 
              FROM posts p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN users u ON p.user_id = u.id 
              ORDER BY p.created_at DESC 
              LIMIT 5";
} else {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT p.*, c.name as category_name, u.username as author_name 
              FROM posts p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.user_id = $user_id 
              ORDER BY p.created_at DESC 
              LIMIT 5";
}
$latest_posts = mysqli_query($conn, $query);

// Get popular categories
$cat_query = "SELECT c.*, COUNT(p.id) as post_count 
              FROM categories c 
              LEFT JOIN posts p ON c.id = p.category_id 
              GROUP BY c.id 
              ORDER BY post_count DESC 
              LIMIT 5";
$popular_categories = mysqli_query($conn, $cat_query);

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Admin Stats -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="users.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $stats['total_posts']; ?></h3>
                            <p>Total Posts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <a href="posts.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <?php if ($_SESSION['role'] !== 'author'): ?>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $stats['total_categories']; ?></h3>
                            <p>Categories</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <a href="categories.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $stats['published_posts']; ?></h3>
                            <p>Published Posts</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="posts.php?status=published" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Latest Posts -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Posts</h3>
                            <div class="card-tools">
                                <a href="posts.php" class="btn btn-tool">
                                    <i class="fas fa-list"></i> View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($post = mysqli_fetch_assoc($latest_posts)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                                            <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($post['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($post['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular Categories -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Popular Categories</h3>
                            <?php if ($_SESSION['role'] !== 'author'): ?>
                            <div class="card-tools">
                                <a href="categories.php" class="btn btn-tool">
                                    <i class="fas fa-list"></i> View All
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php while ($category = mysqli_fetch_assoc($popular_categories)): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <span class="badge badge-primary badge-pill"><?php echo $category['post_count']; ?></span>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 