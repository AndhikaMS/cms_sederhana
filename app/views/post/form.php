<?php $this->layout('layouts/admin', ['title' => $title]) ?>

<div class="container-fluid">
    <!-- Page header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= isset($post) ? 'Edit Post' : 'Create Post' ?></h1>
        <a href="<?= url('post') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Posts
        </a>
    </div>

    <form action="<?= url(isset($post) ? 'post/edit/' . $post['id'] : 'post/create') ?>" 
          method="post" 
          enctype="multipart/form-data"
          id="postForm">
        <?= $this->csrfField() ?>

        <div class="row">
            <!-- Main content -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="title" 
                                   name="title" 
                                   value="<?= $this->escape($post['title'] ?? '') ?>" 
                                   required 
                                   minlength="3" 
                                   maxlength="255"
                                   placeholder="Enter post title">
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" 
                                      id="content" 
                                      name="content" 
                                      rows="15" 
                                      required 
                                      minlength="10"
                                      placeholder="Write your post content here..."><?= $this->escape($post['content'] ?? '') ?></textarea>
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <?php if (isset($post) && $post['featured_image']): ?>
                            <div class="mb-2">
                                <img src="<?= url('uploads/' . $post['featured_image']) ?>" 
                                     alt="<?= $this->escape($post['title']) ?>" 
                                     class="img-thumbnail" 
                                     style="max-height: 200px;">
                                <div class="form-text">
                                    Upload a new image to replace the current one
                                </div>
                            </div>
                            <?php endif; ?>
                            <input type="file" 
                                   class="form-control" 
                                   id="featured_image" 
                                   name="featured_image" 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">
                                Recommended size: 1200x630 pixels. Max file size: 2MB.
                                Supported formats: JPG, PNG, GIF, WebP
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Publish -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Publish</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft" <?= (isset($post) && $post['status'] === 'draft') ? 'selected' : '' ?>>
                                    Draft
                                </option>
                                <option value="published" <?= (isset($post) && $post['status'] === 'published') ? 'selected' : '' ?>>
                                    Published
                                </option>
                                <?php if (isset($post) && $post['status'] === 'archived'): ?>
                                <option value="archived" selected>Archived</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comment_status" class="form-label">Comment Status</label>
                            <select class="form-select" id="comment_status" name="comment_status" required>
                                <option value="open" <?= (isset($post) && $post['comment_status'] === 'open') ? 'selected' : '' ?>>
                                    Open
                                </option>
                                <option value="closed" <?= (isset($post) && $post['comment_status'] === 'closed') ? 'selected' : '' ?>>
                                    Closed
                                </option>
                            </select>
                        </div>

                        <?php if (isset($post)): ?>
                        <div class="mb-3">
                            <label class="form-label">Last Modified</label>
                            <div class="form-text">
                                <?= $this->formatDate($post['updated_at'], 'M j, Y H:i:s') ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 
                                <?= isset($post) ? 'Update Post' : 'Create Post' ?>
                            </button>
                            <?php if (isset($post)): ?>
                            <a href="<?= url('post/delete/' . $post['id']) ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete Post
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Select Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Uncategorized</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= (isset($post) && $post['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= str_repeat('— ', $category['level']) . $this->escape($category['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#content',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    height: 500,
    images_upload_url: '<?= url('upload/image') ?>',
    images_upload_handler: function (blobInfo, success, failure) {
        var xhr, formData;
        xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.open('POST', '<?= url('upload/image') ?>');
        xhr.setRequestHeader('X-CSRF-TOKEN', '<?= $this->getCsrfToken() ?>');
        xhr.onload = function() {
            var json;
            if (xhr.status != 200) {
                failure('HTTP Error: ' + xhr.status);
                return;
            }
            json = JSON.parse(xhr.responseText);
            if (!json || typeof json.location != 'string') {
                failure('Invalid JSON: ' + xhr.responseText);
                return;
            }
            success(json.location);
        };
        formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());
        xhr.send(formData);
    }
});
</script>

<!-- Form validation -->
<script>
document.getElementById('postForm').addEventListener('submit', function(e) {
    var title = document.getElementById('title').value.trim();
    var content = tinymce.get('content').getContent().trim();
    
    if (title.length < 3) {
        e.preventDefault();
        alert('Title must be at least 3 characters long');
        return;
    }
    
    if (content.length < 10) {
        e.preventDefault();
        alert('Content must be at least 10 characters long');
        return;
    }
});
</script> 