<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada ID post
if (!isset($_GET['id'])) {
    header("Location: posts.php");
    exit();
}

$post_id = clean($_GET['id']);

// Hapus post
$query = "DELETE FROM posts WHERE id = '$post_id'";
if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Post deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting post: " . mysqli_error($conn);
}

header("Location: posts.php");
exit(); 