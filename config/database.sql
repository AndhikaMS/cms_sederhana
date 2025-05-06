-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'editor', 'author') NOT NULL DEFAULT 'author',
    profile_picture VARCHAR(255) DEFAULT NULL,
    last_login TIMESTAMP NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update user admin menjadi role admin
UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Tabel untuk invite codes
CREATE TABLE IF NOT EXISTS invite_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(32) NOT NULL UNIQUE,
    role ENUM('author', 'editor') NOT NULL DEFAULT 'author',
    created_by INT NOT NULL,
    used_by INT DEFAULT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (used_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 