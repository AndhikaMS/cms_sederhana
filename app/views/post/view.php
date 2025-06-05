<?php $this->layout('layouts/main', ['title' => $title]) ?>

<div class="container py-4">
    <div class="row">
        <!-- Main content -->
        <div class="col-lg-8">
            <!-- Post header -->
            <header class="mb-4">
                <h1 class="display-4 mb-3"><?= $this->escape($post['title']) ?></h1>
                
                <div class="d-flex align-items-center text-muted mb-3">
                    <div class="me-3">
                        <i class="fas fa-user"></i>
                        <a href="<?= url('user/view/' . $post['user']['id']) ?>" class="text-decoration-none">
                            <?= $this->escape($post['user']['name']) ?>
                        </a>
                    </div>
                    <div class="me-3">
                        <i class="fas fa-calendar"></i>
                        <?= $this->formatDate($post['published_at'] ?? $post['created_at'], 'M j, Y') ?>
                    </div>
                    <?php if ($post['category']): ?>
                    <div class="me-3">
                        <i class="fas fa-folder"></i>
                        <a href="<?= url('category/view/' . $post['category']['slug']) ?>" class="text-decoration-none">
                            <?= $this->escape($post['category']['name']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div>
                        <i class="fas fa-eye"></i>
                        <?= number_format($post['view_count']) ?> views
                    </div>
                </div>

                <?php if ($post['featured_image']): ?>
                <img src="<?= url('uploads/' . $post['featured_image']) ?>" 
                     alt="<?= $this->escape($post['title']) ?>" 
                     class="img-fluid rounded mb-4">
                <?php endif; ?>
            </header>

            <!-- Post content -->
            <article class="mb-5">
                <?= $post['content'] ?>
            </article>

            <!-- Post footer -->
            <footer class="mb-5">
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <?php if ($post['comment_status'] === 'open'): ?>
                        <a href="#comments" class="btn btn-outline-primary">
                            <i class="fas fa-comments"></i> Leave a Comment
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted">
                        Last updated: <?= $this->formatDate($post['updated_at'], 'M j, Y') ?>
                    </div>
                </div>
            </footer>

            <!-- Comments -->
            <?php if ($post['comment_status'] === 'open'): ?>
            <section id="comments" class="mb-5">
                <h3 class="h4 mb-4">Comments</h3>
                <!-- Comment form and list will be added here -->
            </section>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Related posts -->
            <?php if (!empty($relatedPosts)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Related Posts</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($relatedPosts as $related): ?>
                        <a href="<?= url('post/view/' . $related['slug']) ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <?php if ($related['featured_image']): ?>
                                <img src="<?= url('uploads/' . $related['featured_image']) ?>" 
                                     alt="<?= $this->escape($related['title']) ?>" 
                                     class="rounded me-3" 
                                     style="width: 64px; height: 64px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-1"><?= $this->escape($related['title']) ?></h6>
                                    <small class="text-muted">
                                        <?= $this->formatDate($related['published_at'], 'M j, Y') ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Category list -->
            <?php if (!empty($post['category'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Category</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-folder fa-2x text-primary me-3"></i>
                        <div>
                            <h6 class="mb-1">
                                <a href="<?= url('category/view/' . $post['category']['slug']) ?>" 
                                   class="text-decoration-none">
                                    <?= $this->escape($post['category']['name']) ?>
                                </a>
                            </h6>
                            <?php if ($post['category']['description']): ?>
                            <p class="text-muted mb-0">
                                <?= $this->escape($post['category']['description']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Author info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">About the Author</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <?php if ($post['user']['avatar']): ?>
                        <img src="<?= url('uploads/' . $post['user']['avatar']) ?>" 
                             alt="<?= $this->escape($post['user']['name']) ?>" 
                             class="rounded-circle me-3" 
                             style="width: 64px; height: 64px; object-fit: cover;">
                        <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                             style="width: 64px; height: 64px;">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-1">
                                <a href="<?= url('user/view/' . $post['user']['id']) ?>" 
                                   class="text-decoration-none">
                                    <?= $this->escape($post['user']['name']) ?>
                                </a>
                            </h6>
                            <?php if ($post['user']['bio']): ?>
                            <p class="text-muted mb-0">
                                <?= $this->escape($post['user']['bio']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 