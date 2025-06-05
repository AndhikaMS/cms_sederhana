<?php $this->layout('layouts/admin', ['title' => $title]) ?>

<div class="container-fluid">
    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= isset($category) ? 'Edit Category' : 'Create Category' ?></h1>
        <a href="<?= url('category') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>

    <form action="<?= url(isset($category) ? 'category/edit/' . $category['id'] : 'category/create') ?>" 
          method="post" 
          id="categoryForm">
        <?= $this->csrfField() ?>

        <div class="row">
            <!-- Main content -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="name" 
                                   name="name" 
                                   value="<?= $this->escape($category['name'] ?? '') ?>" 
                                   required 
                                   minlength="2" 
                                   maxlength="50"
                                   placeholder="Enter category name">
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="slug" 
                                   name="slug" 
                                   value="<?= $this->escape($category['slug'] ?? '') ?>" 
                                   minlength="2" 
                                   maxlength="50"
                                   pattern="[a-z0-9-]+"
                                   placeholder="category-name">
                            <div class="form-text">
                                Leave empty to generate automatically from name. 
                                Only lowercase letters, numbers, and hyphens are allowed.
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      maxlength="255"
                                      placeholder="Enter category description (optional)"><?= $this->escape($category['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <!-- Parent Category -->
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (Top Level)</option>
                                <?php foreach ($parentCategories as $parent): ?>
                                <?php if (!isset($category) || $parent['id'] != $category['id']): ?>
                                <option value="<?= $parent['id'] ?>" 
                                        <?= (isset($category) && $category['parent_id'] == $parent['id']) ? 'selected' : '' ?>>
                                    <?= str_repeat('— ', $parent['level']) . $this->escape($parent['name']) ?>
                                </option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= (isset($category) && $category['status'] === 'active') ? 'selected' : '' ?>>
                                    Active
                                </option>
                                <option value="inactive" <?= (isset($category) && $category['status'] === 'inactive') ? 'selected' : '' ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <?php if (isset($category)): ?>
                        <div class="mb-3">
                            <label class="form-label">Created</label>
                            <div class="form-text">
                                <?= $this->formatDate($category['created_at'], 'M j, Y H:i:s') ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= isset($category) ? 'Update Category' : 'Create Category' ?>
                            </button>
                            <?php if (isset($category)): ?>
                            <a href="<?= url('category/delete/' . $category['id']) ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete Category
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Form validation and slug generation -->
<script>
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    var name = document.getElementById('name').value.trim();
    var slug = document.getElementById('slug').value.trim();
    
    if (name.length < 2) {
        e.preventDefault();
        alert('Name must be at least 2 characters long');
        return;
    }
    
    if (slug && !/^[a-z0-9-]+$/.test(slug)) {
        e.preventDefault();
        alert('Slug can only contain lowercase letters, numbers, and hyphens');
        return;
    }
});

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    var slugInput = document.getElementById('slug');
    if (!slugInput.value) {
        slugInput.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
</script> 