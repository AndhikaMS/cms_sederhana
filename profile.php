<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/user_functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Ambil data profil user
$user = getUserProfile($_SESSION['user_id']);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email']
        ];
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['profile_picture']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                $upload_path = 'uploads/profiles/' . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                    $data['profile_picture'] = $upload_path;
                }
            }
        }
        
        if (updateUserProfile($_SESSION['user_id'], $data)) {
            $success = "Profile updated successfully!";
            $user = getUserProfile($_SESSION['user_id']); // Refresh user data
        } else {
            $error = "Error updating profile!";
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = "New passwords do not match!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long!";
        } else {
            if (changePassword($_SESSION['user_id'], $current_password, $new_password)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Current password is incorrect!";
            }
        }
    }
}

// Ambil aktivitas user
$activities = getUserActivities($_SESSION['user_id']);

// Include header
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                         src="<?php echo $user['profile_picture'] ?? 'https://via.placeholder.com/150'; ?>"
                         alt="User profile picture">
                </div>
                <h3 class="profile-username text-center"><?php echo htmlspecialchars($user['username']); ?></h3>
                <p class="text-muted text-center"><?php echo ucfirst($user['role']); ?></p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right"><?php echo htmlspecialchars($user['email']); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> <a class="float-right"><?php echo date('d M Y', strtotime($user['created_at'])); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Last Login</b> <a class="float-right"><?php echo $user['last_login'] ? date('d M Y H:i', strtotime($user['last_login'])) : 'Never'; ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    <li class="nav-item"><a class="nav-link active" href="#profile" data-toggle="tab">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="#password" data-toggle="tab">Password</a></li>
                    <li class="nav-item"><a class="nav-link" href="#activity" data-toggle="tab">Activity</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="active tab-pane" id="profile">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_picture">Profile Picture</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="profile_picture" name="profile_picture">
                                        <label class="custom-file-label" for="profile_picture">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                    
                    <!-- Password Tab -->
                    <div class="tab-pane" id="password">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                    
                    <!-- Activity Tab -->
                    <div class="tab-pane" id="activity">
                        <div class="timeline timeline-inverse">
                            <?php foreach ($activities as $activity): ?>
                            <div class="time-label">
                                <span class="bg-primary">
                                    <?php echo date('d M Y', strtotime($activity['created_at'])); ?>
                                </span>
                            </div>
                            <div>
                                <i class="fas fa-user bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="far fa-clock"></i> 
                                        <?php echo date('H:i', strtotime($activity['created_at'])); ?>
                                    </span>
                                    <h3 class="timeline-header"><?php echo ucfirst($activity['activity_type']); ?></h3>
                                    <div class="timeline-body">
                                        <?php echo htmlspecialchars($activity['description']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 