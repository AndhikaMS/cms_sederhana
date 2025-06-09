<?php $this->layout('layouts/main', ['title' => 'Manage Users']); ?>

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
            <?php if (isset($_SESSION['flash']['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['flash']['error']; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash']['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Users List</h3>
                    <div class="card-tools">
                        <a href="/users/invite-codes" class="btn btn-primary btn-sm">
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
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <?php if ($user['id'] == $currentUserId): ?>
                                            <span class="badge badge-info">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $currentUserId): ?>
                                        <form method="POST" action="/users/change-role" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" class="form-control form-control-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                <option value="author" <?php echo $user['role'] == 'author' ? 'selected' : ''; ?>>Author</option>
                                                <option value="editor" <?php echo $user['role'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
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
                                        <?php if ($user['id'] != $currentUserId): ?>
                                        <form method="POST" action="/users/delete/<?php echo $user['id']; ?>" class="d-inline">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user? All their posts will also be deleted.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 