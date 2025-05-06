<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cms_sederhana';

// Buat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?> 