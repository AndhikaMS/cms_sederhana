<?php
// Fungsi untuk membersihkan input
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan data user
function getUserData($user_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan semua kategori
function getAllCategories() {
    global $conn;
    $query = "SELECT * FROM categories ORDER BY name ASC";
    $result = mysqli_query($conn, $query);
    $categories = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    return $categories;
}

// Fungsi untuk mendapatkan post berdasarkan ID
function getPostById($post_id) {
    global $conn;
    $query = "SELECT * FROM posts WHERE id = '$post_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan semua post
function getAllPosts() {
    global $conn;
    $query = "SELECT * FROM posts ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    $posts = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}
?> 