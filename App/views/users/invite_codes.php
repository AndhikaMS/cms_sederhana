<?php $this->layout('layouts/main', ['title' => 'Manage Invite Codes']); ?>

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
            <?php if (isset($_SESSION['flash']['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['flash']['error']; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash']['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['flash']['success']; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Generate New Invite Code</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/users/generate-invite-code">
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
                                <button type="submit" class="btn btn-primary">Generate Code</button>
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
                                        <?php foreach ($inviteCodes as $row): ?>
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
                                                        <form method="POST" action="/users/delete-invite-code/<?php echo $row['id']; ?>" class="d-inline">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this invite code?')">Delete</button>
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
    </section>
</div> 