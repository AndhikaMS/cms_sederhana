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

// Proses form submission untuk menambah kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = clean($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    
    // Validasi input
    if (empty($name)) {
        $error = "Category name is required!";
    } else {
        // Cek apakah slug sudah ada
        $check_slug = mysqli_query($conn, "SELECT id FROM categories WHERE slug = '$slug'");
        if (mysqli_num_rows($check_slug) > 0) {
            $error = "Category with this name already exists!";
        } else {
            // Insert kategori
            $query = "INSERT INTO categories (name, slug) VALUES ('$name', '$slug')";
            if (mysqli_query($conn, $query)) {
                $success = "Category added successfully!";
            } else {
                $error = "Error adding category: " . mysqli_error($conn);
            }
        }
    }
}

// Proses form submission untuk mengedit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $category_id = clean($_POST['category_id']);
    $name = clean($_POST['name']);
    $slug = strtolower(str_replace(' ', '-', $name));
    
    // Validasi input
    if (empty($name)) {
        $error = "Category name is required!";
    } else {
        // Cek apakah slug sudah ada (kecuali untuk kategori ini sendiri)
        $check_slug = mysqli_query($conn, "SELECT id FROM categories WHERE slug = '$slug' AND id != '$category_id'");
        if (mysqli_num_rows($check_slug) > 0) {
            $error = "Category with this name already exists!";
        } else {
            // Update kategori
            $query = "UPDATE categories SET name = '$name', slug = '$slug' WHERE id = '$category_id'";
            if (mysqli_query($conn, $query)) {
                $success = "Category updated successfully!";
            } else {
                $error = "Error updating category: " . mysqli_error($conn);
            }
        }
    }
}

// Ambil semua kategori
$query = "SELECT c.*, COUNT(p.id) as post_count 
          FROM categories c 
          LEFT JOIN posts p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - CMS Sederhana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="posts.php">Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="categories.php">Categories</a>
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Add New Category</h3>
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
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Categories</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Posts</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($category = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                        <td><?php echo $category['post_count']; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCategory<?php echo $category['id']; ?>">
                                                Edit
                                            </button>
                                            <a href="delete_category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit Category -->
                                    <div class="modal fade" id="editCategory<?php echo $category['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Category</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="edit_name<?php echo $category['id']; ?>" class="form-label">Category Name</label>
                                                            <input type="text" class="form-control" id="edit_name<?php echo $category['id']; ?>" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="edit_category" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 