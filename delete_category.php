<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada ID kategori
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    header("Location: categories.php");
    exit();
}

$category_id = clean($_GET['id']);

// Hapus kategori
$query = "DELETE FROM categories WHERE id = '$category_id'";
if (mysqli_query($conn, $query)) {
    $_SESSION['success'] = "Category deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting category: " . mysqli_error($conn);
}

header("Location: categories.php");
exit(); 