<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get latest posts
$query = "SELECT p.*, c.name as category_name, u.username as author_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'published' 
          ORDER BY p.created_at DESC 
          LIMIT 10";
$result = mysqli_query($conn, $query);

// Get popular categories
$cat_query = "SELECT c.*, COUNT(p.id) as post_count 
              FROM categories c 
              LEFT JOIN posts p ON c.id = p.category_id 
              GROUP BY c.id 
              ORDER BY post_count DESC 
              LIMIT 5";
$cat_result = mysqli_query($conn, $cat_query);
?>

<?php include 'includes/header.php'; ?>

<div class="main-content-center mx-auto" style="max-width: 1000px;">
    <!-- Hero Section -->
    <section class="jumbotron text-center mb-4" style="background: #f8f9fa; border-radius: 0 0 1rem 1rem; padding: 2.5rem 1rem 2rem 1rem;">
        <div class="container col-md-8 mx-auto">
            <h1 class="display-4">Temukan Artikel Menarik di Sini!</h1>
            <p class="lead">Baca berita, tips, dan inspirasi terbaru dari penulis kami. Jelajahi kategori atau gunakan pencarian untuk menemukan topik favoritmu.</p>
            <a class="btn btn-primary btn-lg" href="#articles" role="button">Lihat Artikel Terbaru</a>
        </div>
    </section>
    <section class="content-header" id="articles">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h2 class="mb-3">Artikel Terbaru</h2>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php if ($result && is_object($result)): while ($post = mysqli_fetch_assoc($result)): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h2 class="card-title">
                                    <a href="post.php?id=<?php echo $post['id']; ?>" class="text-dark">
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
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    <?php endwhile; endif; ?>
                </div>
                <!-- Sidebar Guest -->
                <div class="col-md-4">
                    <!-- Tentang Situs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Tentang Situs</h3>
                        </div>
                        <div class="card-body">
                            <p>CMS Sederhana adalah platform blog sederhana untuk berbagi artikel, tips, dan berita terbaru. Mudah digunakan dan cocok untuk semua kalangan.</p>
                        </div>
                    </div>
                    <!-- Kategori Populer -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Kategori Populer</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <?php
                                // Ambil ulang kategori populer (karena $cat_result sudah habis di atas)
                                $cat_query2 = "SELECT c.*, COUNT(p.id) as post_count 
                                    FROM categories c 
                                    LEFT JOIN posts p ON c.id = p.category_id 
                                    GROUP BY c.id 
                                    ORDER BY post_count DESC 
                                    LIMIT 5";
                                $cat_result2 = mysqli_query($conn, $cat_query2);
                                if ($cat_result2 && is_object($cat_result2)):
                                    while ($category = mysqli_fetch_assoc($cat_result2)):
                                ?>
                                <li class="mb-2">
                                    <a href="category.php?id=<?php echo $category['id']; ?>" class="text-dark">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <span class="badge badge-primary float-right"><?php echo $category['post_count']; ?></span>
                                    </a>
                                </li>
                                <?php endwhile; endif; ?>
                            </ul>
                        </div>
                    </div>
                    <!-- Search Widget -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Search</h3>
                        </div>
                        <div class="card-body">
                            <form action="search.php" method="GET">
                                <div class="input-group">
                                    <input type="text" name="q" class="form-control" placeholder="Search for...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Go!</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 