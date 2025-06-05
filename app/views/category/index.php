<?php $this->layout('layouts/admin', ['title' => $title]) ?>

<div class="container-fluid">
    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Categories</h1>
        <?php if (hasPermission('manage_categories')): ?>
        <div>
            <a href="<?= url('category/tree') ?>" class="btn btn-info me-2">
                <i class="fas fa-sitemap"></i> View Tree
            </a>
            <a href="<?= url('category/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Category
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="<?= url('category') ?>" method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= $this->escape($filters['search'] ?? '') ?>" 
                           placeholder="Search categories...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="parent" class="form-label">Parent Category</label>
                    <select class="form-select" id="parent" name="parent">
                        <option value="">All Categories</option>
                        <?php foreach ($parentCategories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= ($filters['parent'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                            <?= $this->escape($category['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?= url('category') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($categories)): ?>
            <div class="text-center py-4">
                <i class="fas fa-folder fa-3x text-muted mb-3"></i>
                <p class="text-muted">No categories found</p>
                <?php if (hasPermission('manage_categories')): ?>
                <a href="<?= url('category/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Category
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Parent</th>
                            <th>Posts</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-folder text-primary me-2"></i>
                                    <div>
                                        <a href="<?= url('category/view/' . $category['slug']) ?>" 
                                           class="text-decoration-none" 
                                           target="_blank">
                                            <?= $this->escape($category['name']) ?>
                                        </a>
                                        <?php if ($category['description']): ?>
                                        <div class="text-muted small">
                                            <?= $this->escape($this->truncate($category['description'], 50)) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="text-muted"><?= $this->escape($category['slug']) ?></code>
                            </td>
                            <td>
                                <?php if ($category['parent']): ?>
                                <a href="<?= url('category/view/' . $category['parent']['slug']) ?>" 
                                   class="text-decoration-none">
                                    <?= $this->escape($category['parent']['name']) ?>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= number_format($category['post_count']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'success',
                                    'inactive' => 'danger'
                                ][$category['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst($category['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" title="<?= $this->formatDate($category['created_at'], 'Y-m-d H:i:s') ?>">
                                    <?= $this->formatDate($category['created_at'], 'M j, Y') ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= url('category/edit/' . $category['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (hasPermission('manage_categories')): ?>
                                    <?php if ($category['status'] === 'active'): ?>
                                    <a href="<?= url('category/status/' . $category['id'] . '/inactive') ?>" 
                                       class="btn btn-sm btn-outline-warning" 
                                       title="Deactivate"
                                       onclick="return confirm('Are you sure you want to deactivate this category?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= url('category/status/' . $category['id'] . '/active') ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       title="Activate"
                                       onclick="return confirm('Are you sure you want to activate this category?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= url('category/delete/' . $category['id']) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
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
                    url('category?page=%d' . ($filters['status'] ? '&status=' . $filters['status'] : '') . 
                        ($filters['parent'] ? '&parent=' . $filters['parent'] : '') . 
                        ($filters['search'] ? '&search=' . urlencode($filters['search']) : ''))
                ) ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div> 