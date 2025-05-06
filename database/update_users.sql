-- Update users table
ALTER TABLE users
ADD COLUMN role ENUM('admin', 'editor', 'author') DEFAULT 'author' AFTER email,
ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER role,
ADD COLUMN last_login DATETIME DEFAULT NULL AFTER profile_picture,
ADD COLUMN remember_token VARCHAR(100) DEFAULT NULL AFTER last_login,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Create user_activities table
CREATE TABLE user_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create password_resets table
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create user_permissions table
CREATE TABLE user_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role VARCHAR(50) NOT NULL,
    permission VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role, permission)
);

-- Insert default permissions
INSERT INTO user_permissions (role, permission) VALUES
('admin', 'manage_users'),
('admin', 'manage_roles'),
('admin', 'manage_posts'),
('admin', 'manage_categories'),
('admin', 'manage_settings'),
('editor', 'manage_posts'),
('editor', 'manage_categories'),
('author', 'create_posts'),
('author', 'edit_own_posts'); 