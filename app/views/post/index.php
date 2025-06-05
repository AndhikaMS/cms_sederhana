<?php $this->layout('layouts/admin', ['title' => $title]) ?>

<div class="container-fluid">
    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Posts</h1>
        <?php if (hasPermission('create_posts')): ?>
        <a href="<?= url('post/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Post
        </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="<?= url('post') ?>" method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= $this->escape($filters['search'] ?? '') ?>" 
                           placeholder="Search posts...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($filters['category'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= $this->escape($category['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?= url('post') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($posts)): ?>
            <div class="text-center py-4">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <p class="text-muted">No posts found</p>
                <?php if (hasPermission('create_posts')): ?>
                <a href="<?= url('post/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Post
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($post['featured_image']): ?>
                                    <img src="<?= url('uploads/' . $post['featured_image']) ?>" 
                                         alt="<?= $this->escape($post['title']) ?>" 
                                         class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div>
                                        <a href="<?= url('post/view/' . $post['slug']) ?>" class="text-decoration-none" target="_blank">
                                            <?= $this->escape($post['title']) ?>
                                        </a>
                                        <?php if ($post['comment_status'] === 'closed'): ?>
                                        <i class="fas fa-comment-slash text-muted ms-1" title="Comments closed"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($post['category']): ?>
                                <a href="<?= url('category/view/' . $post['category']['slug']) ?>" class="text-decoration-none">
                                    <?= $this->escape($post['category']['name']) ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">Uncategorized</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('user/view/' . $post['user']['id']) ?>" class="text-decoration-none">
                                    <?= $this->escape($post['user']['name']) ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'draft' => 'secondary',
                                    'published' => 'success',
                                    'archived' => 'danger'
                                ][$post['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst($post['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="fas fa-eye"></i> <?= number_format($post['view_count']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" title="<?= $this->formatDate($post['created_at'], 'Y-m-d H:i:s') ?>">
                                    <?= $this->formatDate($post['created_at'], 'M j, Y') ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= url('post/edit/' . $post['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (hasPermission('manage_posts')): ?>
                                    <?php if ($post['status'] === 'draft'): ?>
                                    <a href="<?= url('post/status/' . $post['id'] . '/published') ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Publish"
                                       onclick="return confirm('Are you sure you want to publish this post?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php elseif ($post['status'] === 'published'): ?>
                                    <a href="<?= url('post/status/' . $post['id'] . '/archived') ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Archive"
                                       onclick="return confirm('Are you sure you want to archive this post?')">
                                        <i class="fas fa-archive"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('post/delete/' . $post['id']) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['last_page'] > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <?= $this->pagination(
                    $pagination['total'],
                    $pagination['per_page'],
                    $pagination['current_page'],
                    url('post?page=%d' . ($filters['status'] ? '&status=' . $filters['status'] : '') . 
                        ($filters['category'] ? '&category=' . $filters['category'] : '') . 
                        ($filters['search'] ? '&search=' . urlencode($filters['search']) : ''))
                ) ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div> 