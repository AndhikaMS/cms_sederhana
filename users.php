<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/user_functions.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle delete user
if (isset($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $user_id = (int)$_GET['delete'];
    $query = "DELETE FROM users WHERE id = $user_id";
    if (mysqli_query($conn, $query)) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user!";
    }
}

// Handle change role
if (isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = clean($_POST['role']);
    
    if ($user_id != $_SESSION['user_id']) { // Prevent changing own role
        $query = "UPDATE users SET role = '$new_role' WHERE id = $user_id";
        if (mysqli_query($conn, $query)) {
            $success = "User role updated successfully!";
        } else {
            $error = "Error updating user role!";
        }
    } else {
        $error = "You cannot change your own role!";
    }
}

// Get all users
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as total_posts,
          (SELECT COUNT(*) FROM posts WHERE user_id = u.id AND status = 'published') as published_posts
          FROM users u 
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage Users</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Users List</h3>
                    <div class="card-tools">
                        <a href="invite_codes.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Generate Invite Code
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Posts</th>
                                    <th>Published</th>
                                    <th>Last Login</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="badge badge-info">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="form-control form-control-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="author" <?php echo $user['role'] == 'author' ? 'selected' : ''; ?>>Author</option>
                                                <option value="editor" <?php echo $user['role'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="change_role" value="1">
                                        </form>
                                        <?php else: ?>
                                            <span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['total_posts']; ?></td>
                                    <td><?php echo $user['published_posts']; ?></td>
                                    <td><?php echo $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user? All their posts will also be deleted.')">
                                            <i class="fas fa-trash"></i>
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
</div>

<?php include 'includes/footer.php'; ?> 