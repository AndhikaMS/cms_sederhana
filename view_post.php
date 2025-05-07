<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: read_posts.php');
    exit();
}
$post_id = intval($_GET['id']);

// Ambil data post
$query = "SELECT p.*, c.name as category_name, u.username 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.id = $post_id AND p.status = 'published'";
$result = mysqli_query($conn, $query);
$post = mysqli_fetch_assoc($result);

// Include header
require_once 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?php if ($post): ?>
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <p>
                        <span class="badge badge-info"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span>
                        &nbsp;|&nbsp;
                        <strong>By:</strong> <?php echo htmlspecialchars($post['username']); ?>
                        &nbsp;|&nbsp;
                        <strong>Date:</strong> <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                    </p>
                    <hr>
                    <div><?php echo htmlspecialchars_decode($post['content']); ?></div>
                <?php else: ?>
                    <div class="alert alert-warning">Post not found or not published.</div>
                <?php endif; ?>
                <a href="read_posts.php" class="btn btn-secondary mt-3">&larr; Back to Posts</a>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?> 