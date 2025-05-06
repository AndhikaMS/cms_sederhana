<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get tag from URL
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

// If tag is empty, redirect to home
if (empty($tag)) {
    header("Location: index.php");
    exit();
}

// Search posts with this tag
$query = "SELECT p.*, c.name as category_name, u.username as author_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'published' 
          AND p.tags LIKE ?
          ORDER BY p.created_at DESC";
$tag_term = "%{$tag}%";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $tag_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total posts
$total_posts = mysqli_num_rows($result);
?>

<?php include 'includes/header.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tag: <?php echo htmlspecialchars($tag); ?></h1>
                    <p class="text-muted"><?php echo $total_posts; ?> posts with this tag</p>
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
                                        Category: <?php echo htmlspecialchars($post['category_name']); ?> | 
                                        <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    </p>
                                    <p class="card-text">
                                        <?php echo substr(strip_tags($post['content']), 0, 200) . '...'; ?>
                                    </p>
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-center">No posts found with this tag</p>
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
                                <?php while ($category = mysqli_fetch_assoc($cat_result)): ?>
                                    <li class="mb-2">
                                        <a href="category.php?id=<?php echo $category['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                            <span class="badge badge-primary float-right"><?php echo $category['post_count']; ?></span>
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