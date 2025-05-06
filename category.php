<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get category details
$cat_query = "SELECT * FROM categories WHERE id = ?";
$stmt = mysqli_prepare($conn, $cat_query);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$cat_result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($cat_result);

// If category not found, redirect to home
if (!$category) {
    header("Location: index.php");
    exit();
}

// Get posts in this category
$query = "SELECT p.*, c.name as category_name, u.username as author_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.category_id = ? AND p.status = 'published' 
          ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total posts
$total_posts = mysqli_num_rows($result);
?>

<?php include 'includes/header.php'; ?>

<div class="main-content-center mx-auto" style="max-width: 1000px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Category: <?php echo htmlspecialchars($category['name']); ?></h1>
                    <p class="text-muted"><?php echo $total_posts; ?> posts in this category</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <?php if ($total_posts > 0): ?>
                        <?php while ($post = mysqli_fetch_assoc($result)): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h2 class="card-title">
                                        <a href="post.php?id=<?php echo $post['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h2>
                                    <p class="card-text text-muted">
                                        By <?php echo htmlspecialchars($post['author_name']); ?> | 
                                        <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    </p>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars_decode($post['content']); ?>
                                    </p>
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-center">No posts found in this category</p>
                                <p class="text-center">
                                    <a href="index.php" class="btn btn-primary">Back to Home</a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Categories Widget -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Categories</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            $cat_query = "SELECT c.*, COUNT(p.id) as post_count 
                                        FROM categories c 
                                        LEFT JOIN posts p ON c.id = p.category_id 
                                        GROUP BY c.id 
                                        ORDER BY post_count DESC 
                                        LIMIT 5";
                            $cat_result = mysqli_query($conn, $cat_query);
                            ?>
                            <ul class="list-unstyled">
                                <?php while ($cat = mysqli_fetch_assoc($cat_result)): ?>
                                    <li class="mb-2">
                                        <a href="category.php?id=<?php echo $cat['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                            <span class="badge badge-primary float-right"><?php echo $cat['post_count']; ?></span>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Search Widget -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Search</h3>
                        </div>
                        <div class="card-body">
                            <form action="search.php" method="GET">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" placeholder="Search for...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Go!</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 