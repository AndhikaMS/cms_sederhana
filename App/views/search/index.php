<?php $this->layout('layouts/main', ['title' => 'Search Results for "' . htmlspecialchars($search) . '"']); ?>

<div class="main-content-center mx-auto" style="max-width: 1000px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Search Results for &quot;<?php echo htmlspecialchars($search); ?>&quot;</h1>
                    <p class="text-muted">Found <?php echo $totalResults; ?> results</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if ($totalResults > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h2 class="card-title">
                                        <a href="/post/<?php echo $post['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($post['title']); ?>
                                        </a>
                                    </h2>
                                    <p class="card-text text-muted">
                                        By <?php echo htmlspecialchars($post['author_name']); ?> | 
                                        Category: <?php echo htmlspecialchars($post['category_name']); ?> | 
                                        <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                    </p>
                                    <p class="card-text">
                                        <?php echo htmlspecialchars_decode($post['content']); ?>
                                    </p>
                                    <a href="/post/<?php echo $post['id']; ?>" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body">
                                <p class="text-center">No results found for &quot;<?php echo htmlspecialchars($search); ?>&quot;</p>
                                <p class="text-center">
                                    <a href="/" class="btn btn-primary">Back to Home</a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Search Widget -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Search</h3>
                        </div>
                        <div class="card-body">
                            <form action="/search" method="GET">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" 
                                           placeholder="Search for..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Go!</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Categories Widget -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Categories</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <?php foreach ($categories as $category): ?>
                                    <li class="mb-2">
                                        <a href="/category/<?php echo $category['id']; ?>" class="text-dark">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                            <span class="badge badge-primary float-right"><?php echo $category['post_count']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div> 