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

    public function getAll($page = 1, $filters = []) {
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $where = ['1=1'];
        $params = [];
        
        // Filter by role
        if (!empty($filters['role'])) {
            $where[] = 'u.role_id = ?';
            $params[] = $filters['role'];
        }
        
        // Filter by status
        if (isset($filters['status'])) {
            $where[] = 'u.status = ?';
            $params[] = $filters['status'];
        }
        
        // Search by name or email
        if (!empty($filters['search'])) {
            $where[] = '(u.name LIKE ? OR u.email LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total records
        $sql = "SELECT COUNT(*) as total 
                FROM users u 
                WHERE $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Get users with role info
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug,
                       (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count,
                       (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE $whereClause
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        return [
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

    public function getById($id) {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug,
                       (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count,
                       (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEmail($email) {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getByRememberToken($token) {
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.remember_token = ? AND u.remember_token_expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, role_id, status, bio, avatar, 
                                 created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role_id'] ?? 2, // Default to author role
            $data['status'] ?? 'active',
            $data['bio'] ?? null,
            $data['avatar'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'email', 'role_id', 'status', 'bio', 'avatar'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        // Handle password update separately
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        // Check if user has any posts or comments
        $sql = "SELECT 
                (SELECT COUNT(*) FROM posts WHERE user_id = ?) as post_count,
                (SELECT COUNT(*) FROM comments WHERE user_id = ?) as comment_count";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $id]);
        $counts = $stmt->fetch();
        
        if ($counts['post_count'] > 0 || $counts['comment_count'] > 0) {
            return false; // Cannot delete user with posts or comments
        }
        
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    public function updateRole($id, $roleId) {
        $sql = "UPDATE users SET role_id = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$roleId, $id]);
    }

    public function updatePassword($id, $password) {
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    public function updateAvatar($id, $avatar) {
        $sql = "UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$avatar, $id]);
    }

    public function setRememberToken($id, $token, $expiresAt) {
        $sql = "UPDATE users SET remember_token = ?, remember_token_expires_at = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$token, $expiresAt, $id]);
    }

    public function clearRememberToken($id) {
        $sql = "UPDATE users SET remember_token = NULL, remember_token_expires_at = NULL WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function updateLastLogin($id) {
        $sql = "UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
                SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN role_id = 2 THEN 1 ELSE 0 END) as author_count,
                SUM(CASE WHEN role_id = 3 THEN 1 ELSE 0 END) as editor_count,
                SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as subscriber_count
                FROM users";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getActivityLog($userId, $page = 1) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get total records
        $sql = "SELECT COUNT(*) as total FROM activity_logs WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $total = $stmt->fetch()['total'];
        
        // Get activity logs
        $sql = "SELECT * FROM activity_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $perPage, $offset]);
        $logs = $stmt->fetchAll();
        
        return [
            'logs' => $logs,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

    public function logActivity($userId, $action, $description, $ipAddress = null) {
        $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, created_at)
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $action, $description, $ipAddress]);
    }

    public function validatePassword($id, $password) {
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        return $user && password_verify($password, $user['password']);
    }

    public function search($keyword, $page = 1) {
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Get total records
        $sql = "SELECT COUNT(*) as total 
                FROM users u
                WHERE u.name LIKE ? OR u.email LIKE ?";
        
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$keyword, $keyword]);
        $total = $stmt->fetch()['total'];
        
        // Get users
        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.name LIKE ? OR u.email LIKE ?
                ORDER BY u.name ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$keyword, $keyword, $perPage, $offset]);
        $users = $stmt->fetchAll();
        
        return [
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }
} 