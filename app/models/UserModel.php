<?php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model {
    public function __construct() {
        parent::__construct();
    }

    public function findByUsername($username) {
        $username = $this->clean($username);
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $this->query($query);
        return $result->fetch_assoc();
    }

    public function findByEmail($email) {
        $email = $this->clean($email);
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $this->query($query);
        return $result->fetch_assoc();
    }

    public function findByRememberToken($token) {
        $token = $this->clean($token);
        $query = "SELECT * FROM users WHERE remember_token = '$token'";
        $result = $this->query($query);
        return $result->fetch_assoc();
    }

    public function create($data) {
        $username = $this->clean($data['username']);
        $email = $this->clean($data['email']);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $this->clean($data['role']);

        $query = "INSERT INTO users (username, email, password, role) 
                 VALUES ('$username', '$email', '$password', '$role')";
        
        if ($this->query($query)) {
            return $this->getLastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $id = (int)$id;
        $updates = [];
        $allowed_fields = ['username', 'email', 'role', 'profile_picture', 'remember_token'];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                $value = $this->clean($value);
                $updates[] = "$field = '$value'";
            }
        }

        if (!empty($updates)) {
            $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $id";
            return $this->query($query);
        }

        return false;
    }

    public function updatePassword($id, $password) {
        $id = (int)$id;
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = '$hashed_password' WHERE id = $id";
        return $this->query($query);
    }

    public function updateLastLogin($id) {
        $id = (int)$id;
        $query = "UPDATE users SET last_login = NOW() WHERE id = $id";
        return $this->query($query);
    }

    public function verifyPassword($id, $password) {
        $id = (int)$id;
        $query = "SELECT password FROM users WHERE id = $id";
        $result = $this->query($query);
        $user = $result->fetch_assoc();
        
        return $user && password_verify($password, $user['password']);
    }

    public function getAll($limit = null, $offset = null) {
        $query = "SELECT u.*, 
                 (SELECT COUNT(*) FROM posts WHERE author_id = u.id) as total_posts,
                 (SELECT COUNT(*) FROM posts WHERE author_id = u.id AND status = 'published') as published_posts
                 FROM users u 
                 ORDER BY u.created_at DESC";
        
        if ($limit !== null) {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $query .= " LIMIT $offset, $limit";
        }
        
        $result = $this->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    public function getById($id) {
        $id = (int)$id;
        $query = "SELECT id, username, email, role, profile_picture, last_login, created_at 
                 FROM users WHERE id = $id";
        $result = $this->query($query);
        return $result->fetch_assoc();
    }

    public function getActivities($id, $limit = 10) {
        $id = (int)$id;
        $limit = (int)$limit;
        $query = "SELECT * FROM user_activities 
                 WHERE user_id = $id 
                 ORDER BY created_at DESC 
                 LIMIT $limit";
        $result = $this->query($query);
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        return $activities;
    }

    public function logActivity($user_id, $activity_type, $description = '') {
        $user_id = (int)$user_id;
        $activity_type = $this->clean($activity_type);
        $description = $this->clean($description);
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $query = "INSERT INTO user_activities (user_id, activity_type, description, ip_address, user_agent) 
                 VALUES ($user_id, '$activity_type', '$description', '$ip_address', '$user_agent')";
        return $this->query($query);
    }

    public function hasPermission($user_id, $permission) {
        $user_id = (int)$user_id;
        $permission = $this->clean($permission);
        
        $query = "SELECT u.role, p.permission 
                 FROM users u 
                 JOIN user_permissions p ON u.role = p.role 
                 WHERE u.id = $user_id AND p.permission = '$permission'";
        $result = $this->query($query);
        
        return $result->num_rows > 0;
    }
} 