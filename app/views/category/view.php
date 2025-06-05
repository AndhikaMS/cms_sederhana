<?php $this->layout('layouts/main', ['title' => $title]) ?>

<div class="container py-4">
    <div class="row">
        <!-- Main content -->
        <div class="col-lg-8">
            <!-- Category header -->
            <header class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= url() ?>" class="text-decoration-none">Home</a>
                        </li>
                        <?php if ($category['parent']): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= url('category/view/' . $category['parent']['slug']) ?>" class="text-decoration-none">
                                <?= $this->escape($category['parent']['name']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= $this->escape($category['name']) ?>
                        </li>
                    </ol>
                </nav>

                <h1 class="display-4 mb-3"><?= $this->escape($category['name']) ?></h1>
                
                <?php if ($category['description']): ?>
                <div class="lead text-muted mb-4">
                    <?= $this->escape($category['description']) ?>
                </div>
                <?php endif; ?>

                <div class="d-flex align-items-center text-muted mb-3">
                    <div class="me-3">
                        <i class="fas fa-newspaper"></i>
                        <?= number_format($pagination['total']) ?> posts
                    </div>
                    <?php if ($category['parent']): ?>
                    <div>
                        <i class="fas fa-level-up-alt"></i>
                        <a href="<?= url('category/view/' . $category['parent']['slug']) ?>" class="text-decoration-none">
                            Parent: <?= $this->escape($category['parent']['name']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Subcategories -->
            <?php if (!empty($subcategories)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subcategories</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($subcategories as $sub): ?>
                        <div class="col">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?= url('category/view/' . $sub['slug']) ?>" class="text-decoration-none">
                                            <?= $this->escape($sub['name']) ?>
                                        </a>
                                    </h5>
                                    <?php if ($sub['description']): ?>
                                    <p class="card-text text-muted">
                                        <?= $this->escape($this->truncate($sub['description'], 100)) ?>
                                    </p>
                                    <?php endif; ?>
                                    <div class="text-muted small">
                                        <i class="fas fa-newspaper"></i>
                                        <?= number_format($sub['post_count']) ?> posts
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Posts -->
            <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <p class="text-muted">No posts found in this category</p>
            </div>
            <?php else: ?>
            <div class="row row-cols-1 g-4">
                <?php foreach ($posts as $post): ?>
                <div class="col">
                    <article class="card h-100">
                        <div class="row g-0">
                            <?php if ($post['featured_image']): ?>
                            <div class="col-md-4">
                                <img src="<?= url('uploads/' . $post['featured_image']) ?>" 
                                     alt="<?= $this->escape($post['title']) ?>" 
                                     class="img-fluid rounded-start h-100" 
                                     style="object-fit: cover;">
                            </div>
                            <?php endif; ?>
                            <div class="col-md-<?= $post['featured_image'] ? '8' : '12' ?>">
                                <div class="card-body">
                                    <h2 class="card-title h5">
                                        <a href="<?= url('post/view/' . $post['slug']) ?>" class="text-decoration-none">
                                            <?= $this->escape($post['title']) ?>
                                        </a>
                                    </h2>
                                    <p class="card-text text-muted">
                                        <?= $this->escape($this->truncate(strip_tags($post['content']), 150)) ?>
                                    </p>
                                    <div class="d-flex align-items-center text-muted small">
                                        <div class="me-3">
                                            <i class="fas fa-user"></i>
                                            <a href="<?= url('user/view/' . $post['user']['id']) ?>" class="text-decoration-none">
                                                <?= $this->escape($post['user']['name']) ?>
                                            </a>
                                        </div>
                                        <div class="me-3">
                                            <i class="fas fa-calendar"></i>
                                            <?= $this->formatDate($post['published_at'], 'M j, Y') ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-eye"></i>
                                            <?= number_format($post['view_count']) ?> views
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['last_page'] > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <?= $this->pagination(
                    $pagination['total'],
                    $pagination['per_page'],
                    $pagination['current_page'],
                    url('category/view/' . $category['slug'] . '?page=%d')
                ) ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Category info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Category Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Status</h6>
                        <?php
                        $statusClass = [
                            'active' => 'success',
                            'inactive' => 'danger'
                        ][$category['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $statusClass ?>">
                            <?= ucfirst($category['status']) ?>
                        </span>
                    </div>

                    <?php if ($category['parent']): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Parent Category</h6>
                        <a href="<?= url('category/view/' . $category['parent']['slug']) ?>" class="text-decoration-none">
                            <?= $this->escape($category['parent']['name']) ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Created</h6>
                        <div>
                            <?= $this->formatDate($category['created_at'], 'M j, Y') ?>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-muted mb-2">Total Posts</h6>
                        <div class="h4 mb-0">
                            <?= number_format($pagination['total']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular categories -->
            <?php if (!empty($popularCategories)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Popular Categories</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($popularCategories as $popular): ?>
                        <a href="<?= url('category/view/' . $popular['slug']) ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <?= $this->escape($popular['name']) ?>
                            <span class="badge bg-primary rounded-pill">
                                <?= number_format($popular['post_count']) ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div> 