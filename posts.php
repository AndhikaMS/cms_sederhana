<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil semua post
$query = "SELECT p.*, c.name as category_name, u.username 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Include header
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Posts</h3>
                <div class="card-tools">
                    <a href="add_post.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Post
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($post = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($post['username']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <?php if ($role === 'admin' || $role === 'editor') : ?>
                                        <?php if ($post['user_id'] == $user_id): ?>
                                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php elseif ($role === 'author' && $post['user_id'] == $user_id): ?>
                                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 