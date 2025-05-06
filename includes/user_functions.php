<?php
// User Management Functions

// Check if user has permission
function hasPermission($permission) {
    global $conn;
    if (!isset($_SESSION['user_id'])) return false;
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT u.role, p.permission 
              FROM users u 
              JOIN user_permissions p ON u.role = p.role 
              WHERE u.id = '$user_id' AND p.permission = '$permission'";
    $result = mysqli_query($conn, $query);
    
    return mysqli_num_rows($result) > 0;
}

// Log user activity
function logActivity($user_id, $activity_type, $description = '') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO user_activities (user_id, activity_type, description, ip_address, user_agent) 
              VALUES ('$user_id', '$activity_type', '$description', '$ip_address', '$user_agent')";
    mysqli_query($conn, $query);
}

// Update last login
function updateLastLogin($user_id) {
    global $conn;
    $query = "UPDATE users SET last_login = NOW() WHERE id = '$user_id'";
    mysqli_query($conn, $query);
}

// Generate remember token
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

// Set remember token
function setRememberToken($user_id, $token) {
    global $conn;
    $query = "UPDATE users SET remember_token = '$token' WHERE id = '$user_id'";
    mysqli_query($conn, $query);
}

// Get user by remember token
function getUserByRememberToken($token) {
    global $conn;
    $query = "SELECT * FROM users WHERE remember_token = '$token'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Generate password reset token
function generatePasswordResetToken($user_id) {
    global $conn;
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $query = "INSERT INTO password_resets (user_id, token, expires_at) 
              VALUES ('$user_id', '$token', '$expires')";
    mysqli_query($conn, $query);
    
    return $token;
}

// Verify password reset token
function verifyPasswordResetToken($token) {
    global $conn;
    $query = "SELECT * FROM password_resets 
              WHERE token = '$token' 
              AND used = 0 
              AND expires_at > NOW()";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Mark password reset token as used
function markPasswordResetTokenAsUsed($token) {
    global $conn;
    $query = "UPDATE password_resets SET used = 1 WHERE token = '$token'";
    mysqli_query($conn, $query);
}

// Get user activities
function getUserActivities($user_id, $limit = 10) {
    global $conn;
    $query = "SELECT * FROM user_activities 
              WHERE user_id = '$user_id' 
              ORDER BY created_at DESC 
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activities[] = $row;
    }
    
    return $activities;
}

// Update user profile
function updateUserProfile($user_id, $data) {
    global $conn;
    
    $updates = [];
    $allowed_fields = ['username', 'email', 'profile_picture'];
    
    foreach ($data as $field => $value) {
        if (in_array($field, $allowed_fields)) {
            $value = clean($value);
            $updates[] = "$field = '$value'";
        }
    }
    
    if (!empty($updates)) {
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = '$user_id'";
        return mysqli_query($conn, $query);
    }
    
    return false;
}

// Change password
function changePassword($user_id, $current_password, $new_password) {
    global $conn;
    
    // Verify current password
    $query = "SELECT password FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
    
    if (password_verify($current_password, $user['password'])) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        return mysqli_query($conn, $query);
    }
    
    return false;
}

// Get user profile
function getUserProfile($user_id) {
    global $conn;
    $query = "SELECT id, username, email, role, profile_picture, last_login, created_at 
              FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
} 