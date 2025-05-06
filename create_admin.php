<?php
require_once 'config/database.php';

// Hapus user admin yang ada (jika ada)
mysqli_query($conn, "DELETE FROM users WHERE username = 'admin'");

// Buat password hash yang benar untuk 'admin123'
$password = password_hash('admin123', PASSWORD_DEFAULT);

// Insert user admin baru
$query = "INSERT INTO users (username, password, email) VALUES ('admin', '$password', 'admin@example.com')";

if (mysqli_query($conn, $query)) {
    echo "Admin user created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123";
} else {
    echo "Error creating admin user: " . mysqli_error($conn);
}
?> 