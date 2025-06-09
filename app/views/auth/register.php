<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CMS Sederhana</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="register-logo">
        <a href="<?php echo App\Helpers\Functions::url('/'); ?>"><b>CMS</b> Sederhana</a>
    </div>
    <!-- /.register-logo -->
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Register a new account</p>

            <?php if ($this->hasFlash('error')): ?>
                <div class="alert alert-danger"><?php echo $this->getFlash('error'); ?></div>
            <?php endif; ?>
            
            <?php if ($this->hasFlash('success')): ?>
                <div class="alert alert-success"><?php echo $this->getFlash('success'); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo App\Helpers\Functions::url('register'); ?>">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="invite_code" placeholder="Invite Code" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-key"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="agreeTerms" name="terms" required>
                            <label for="agreeTerms">
                                I agree to the <a href="#">terms</a>
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="<?php echo App\Helpers\Functions::url('/'); ?>" class="btn btn-outline-secondary">Kembali ke Home</a>
            </div>

            <p class="mt-3 mb-1">
                <a href="<?php echo App\Helpers\Functions::url('login'); ?>">I already have an account</a>
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