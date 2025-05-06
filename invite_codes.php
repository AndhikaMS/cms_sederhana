<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Generate invite code baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $role = clean($_POST['role']);
    $expires_in = (int)$_POST['expires_in']; // dalam hari
    
    // Generate random code
    $code = bin2hex(random_bytes(16));
    
    // Hitung expiry date
    $expires_at = date('Y-m-d H:i:s', strtotime("+$expires_in days"));
    
    $query = "INSERT INTO invite_codes (code, role, created_by, expires_at) 
              VALUES ('$code', '$role', {$_SESSION['user_id']}, '$expires_at')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Invite code generated successfully!";
    } else {
        $error = "Error generating invite code!";
    }
}

// Hapus invite code
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "DELETE FROM invite_codes WHERE id = $id AND is_used = 0";
    
    if (mysqli_query($conn, $query)) {
        $success = "Invite code deleted successfully!";
    } else {
        $error = "Error deleting invite code!";
    }
}

// Ambil semua invite codes
$query = "SELECT ic.*, u1.username as created_by_username, u2.username as used_by_username 
          FROM invite_codes ic 
          LEFT JOIN users u1 ON ic.created_by = u1.id 
          LEFT JOIN users u2 ON ic.used_by = u2.id 
          ORDER BY ic.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<?php include 'includes/header.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Invite Codes</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Generate New Invite Code</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" class="form-control" required>
                                        <option value="author">Author</option>
                                        <option value="editor">Editor</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Expires In (days)</label>
                                    <input type="number" name="expires_in" class="form-control" value="7" min="1" required>
                                </div>
                                <button type="submit" name="generate" class="btn btn-primary">Generate Code</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Invite Codes List</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%">Code</th>
                                            <th style="width: 10%">Role</th>
                                            <th style="width: 15%">Created By</th>
                                            <th style="width: 15%">Used By</th>
                                            <th style="width: 10%">Status</th>
                                            <th style="width: 15%">Expires</th>
                                            <th style="width: 10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td class="text-break"><?php echo $row['code']; ?></td>
                                                <td><?php echo ucfirst($row['role']); ?></td>
                                                <td><?php echo $row['created_by_username']; ?></td>
                                                <td><?php echo $row['used_by_username'] ?? '-'; ?></td>
                                                <td>
                                                    <?php if ($row['is_used']): ?>
                                                        <span class="badge badge-success">Used</span>
                                                    <?php elseif (strtotime($row['expires_at']) < time()): ?>
                                                        <span class="badge badge-danger">Expired</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-primary">Active</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('Y-m-d', strtotime($row['expires_at'])); ?></td>
                                                <td>
                                                    <?php if (!$row['is_used']): ?>
                                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this invite code?')">Delete</a>
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
    </section>
</div>

<?php include 'includes/footer.php'; ?> 