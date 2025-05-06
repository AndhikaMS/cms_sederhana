<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get search query
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// If search is empty, redirect to home
if (empty($search)) {
    header("Location: index.php");
    exit();
}

// Search in posts
$query = "SELECT p.*, c.name as category_name, u.username as author_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'published' 
          AND (p.title LIKE ? OR p.content LIKE ?)
          ORDER BY p.created_at DESC";
$search_term = "%{$search}%";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    echo '<div class="alert alert-danger">Error in search query: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
    include "includes/footer.php";
    exit;
}
mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get total results
$total_results = mysqli_num_rows($result);
?>

<?php include 'includes/header.php'; ?>

<div class="main-content-center mx-auto" style="max-width: 1000px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Search Results for &quot;<?php echo htmlspecialchars($search); ?>&quot;</h1>
                    <p class="text-muted">Found <?php echo $total_results; ?> results</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if ($total_results > 0): ?>
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
                                        <?php echo htmlspecialchars_decode($post['content']); ?>
                                    </p>
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-center">No results found for &quot;<?php echo htmlspecialchars($search); ?>&quot;</p>
                                <p class="text-center">
                                    <a href="home.php" class="btn btn-primary">Back to Home</a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Search Widget -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Search</h3>
                        </div>
                        <div class="card-body">
                            <form action="search.php" method="GET">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" 
                                           placeholder="Search for..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Go!</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

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
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 