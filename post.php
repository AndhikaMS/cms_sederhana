<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get post details
$query = "SELECT p.*, c.name as category_name, u.username as author_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.id = ? AND p.status = 'published'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

// If post not found, redirect to home
if (!$post) {
    header("Location: index.php");
    exit();
}

// Get related posts
$related_query = "SELECT p.*, c.name as category_name 
                 FROM posts p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = ? AND p.id != ? AND p.status = 'published' 
                 ORDER BY p.created_at DESC 
                 LIMIT 3";
$stmt = mysqli_prepare($conn, $related_query);
mysqli_stmt_bind_param($stmt, "ii", $post['category_id'], $post_id);
mysqli_stmt_execute($stmt);
$related_result = mysqli_stmt_get_result($stmt);
?>

<?php include 'includes/header.php'; ?>

<div class="main-content-center mx-auto" style="max-width: 1000px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="post-meta text-muted mb-3">
                                By <?php echo htmlspecialchars($post['author_name']); ?> | 
                                Category: <?php echo htmlspecialchars($post['category_name']); ?> | 
                                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                            </div>
                            
                            <?php if (isset($post['featured_image']) && $post['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                     class="img-fluid mb-4" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>

                            <div class="post-content">
                                <?php echo htmlspecialchars_decode($post['content']); ?>
                            </div>

                            <?php if (isset($post['tags']) && $post['tags']): ?>
                                <div class="post-tags mt-4">
                                    <h4>Tags:</h4>
                                    <?php
                                    $tags = explode(',', $post['tags']);
                                    foreach ($tags as $tag):
                                        $tag = trim($tag);
                                    ?>
                                        <a href="tag.php?tag=<?php echo urlencode($tag); ?>" 
                                           class="badge badge-info mr-2">
                                            <?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Related Posts -->
                    <?php if (mysqli_num_rows($related_result) > 0): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Related Posts</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <?php if (!empty($related['featured_image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                                         class="card-img-top" 
                                                         alt="<?php echo htmlspecialchars($related['title']); ?>">
                                                <?php endif; ?>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <a href="post.php?id=<?php echo $related['id']; ?>" class="text-dark">
                                                            <?php echo htmlspecialchars($related['title']); ?>
                                                        </a>
                                                    </h5>
                                                    <p class="card-text text-muted">
                                                        <?php echo date('M j, Y', strtotime($related['created_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
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