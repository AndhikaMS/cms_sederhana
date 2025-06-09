<?php $this->layout('layouts/main', ['title' => 'My Profile']); ?>

<div class="row">
    <div class="col-md-4">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle"
                         src="<?php echo $user['avatar'] ?? 'https://via.placeholder.com/150'; ?>"
                         alt="User profile picture">
                </div>
                <h3 class="profile-username text-center"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p class="text-muted text-center"><?php echo ucfirst($user['role_name']); ?></p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right"><?php echo htmlspecialchars($user['email']); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> <a class="float-right"><?php echo date('d M Y', strtotime($user['created_at'])); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Last Login</b> <a class="float-right"><?php echo $user['last_login_at'] ? date('d M Y H:i', strtotime($user['last_login_at'])) : 'Never'; ?></a>
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
                        <?php if (isset($_SESSION['flash']['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['flash']['error']; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['flash']['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="/profile/update" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['name']); ?>" required>
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
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                    
                    <!-- Password Tab -->
                    <div class="tab-pane" id="password">
                        <form method="POST" action="/profile/change-password">
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
                            
                            <button type="submit" class="btn btn-primary">
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
                                    <h3 class="timeline-header"><?php echo ucfirst($activity['action']); ?></h3>
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