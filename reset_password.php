<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/user_functions.php';

$error = '';
$success = '';

// Cek apakah ada token
if (isset($_GET['token'])) {
    $token = clean($_GET['token']);
    $reset = verifyPasswordResetToken($token);
    
    if (!$reset) {
        $error = "Invalid or expired reset token!";
    }
} else {
    // Proses form request reset password
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['request_reset'])) {
            $email = clean($_POST['email']);
            
            // Cek apakah email ada di database
            $query = "SELECT id FROM users WHERE email = '$email'";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $token = generatePasswordResetToken($user['id']);
                
                // Kirim email reset password (implementasi email akan ditambahkan nanti)
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $token;
                
                // Untuk sementara, tampilkan link reset di halaman
                $success = "Password reset link has been sent to your email. For testing, here's the link: <a href='$reset_link'>Reset Password</a>";
            } else {
                $error = "Email not found!";
            }
        } elseif (isset($_POST['reset_password'])) {
            $token = clean($_POST['token']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($password !== $confirm_password) {
                $error = "Passwords do not match!";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters long!";
            } else {
                $reset = verifyPasswordResetToken($token);
                
                if ($reset) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET password = '$hashed_password' WHERE id = '{$reset['user_id']}'";
                    
                    if (mysqli_query($conn, $query)) {
                        markPasswordResetTokenAsUsed($token);
                        $success = "Password has been reset successfully! You can now <a href='login.php'>login</a> with your new password.";
                    } else {
                        $error = "Error resetting password!";
                    }
                } else {
                    $error = "Invalid or expired reset token!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CMS Sederhana</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="index.php"><b>CMS</b> Sederhana</a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">
                <?php echo isset($_GET['token']) ? 'Reset your password' : 'Request password reset'; ?>
            </p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['token'])): ?>
                <!-- Reset Password Form -->
                <form method="POST" action="">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="New Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="reset_password" class="btn btn-primary btn-block">Reset Password</button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <!-- Request Reset Form -->
                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="request_reset" class="btn btn-primary btn-block">Request Reset</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <p class="mt-3 mb-1">
                <a href="login.php">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 