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
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
        header("Location: categories.php");
        exit();
    }
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
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
        header("Location: categories.php");
        exit();
    }
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

// Include header
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add New Category</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Categories</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCategory<?php echo $category['id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="delete_category.php?id=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal Edit Category -->
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
                            <div class="modal fade" id="editCategory<?php echo $category['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editCategoryLabel<?php echo $category['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editCategoryLabel<?php echo $category['id']; ?>">Edit Category</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                <div class="form-group">
                                                    <label for="name">Category Name</label>
                                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" name="edit_category" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?> 