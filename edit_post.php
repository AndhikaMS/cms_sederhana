<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Cek apakah ada ID post
if (!isset($_GET['id'])) {
    header("Location: posts.php");
    exit();
}

$post_id = clean($_GET['id']);

// Ambil data post
$query = "SELECT * FROM posts WHERE id = '$post_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: posts.php");
    exit();
}

$post = mysqli_fetch_assoc($result);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean($_POST['title']);
    $content = clean($_POST['content']);
    $category_id = clean($_POST['category_id']);
    $status = clean($_POST['status']);
    $slug = strtolower(str_replace(' ', '-', $title));
    
    // Validasi input
    if (empty($title) || empty($content)) {
        $error = "Title and content are required!";
    } else {
        // Cek apakah slug sudah ada (kecuali untuk post ini sendiri)
        $check_slug = mysqli_query($conn, "SELECT id FROM posts WHERE slug = '$slug' AND id != '$post_id'");
        if (mysqli_num_rows($check_slug) > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Update post
        $query = "UPDATE posts SET 
                 title = '$title',
                 slug = '$slug',
                 content = '$content',
                 category_id = '$category_id',
                 status = '$status'
                 WHERE id = '$post_id'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Post updated successfully!";
            // Update data post yang ditampilkan
            $post['title'] = $title;
            $post['content'] = $content;
            $post['category_id'] = $category_id;
            $post['status'] = $status;
        } else {
            $error = "Error updating post: " . mysqli_error($conn);
        }
    }
}

// Ambil semua kategori
$categories = getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - CMS Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">CMS Sederhana</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="posts.php">Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Categories</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Post</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ($post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo $post['content']; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft" <?php echo ($post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo ($post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Update Post</button>
                                <a href="posts.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#content').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html> 