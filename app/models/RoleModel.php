<?php
namespace App\Models;

class RoleModel extends BaseModel {
    public function __construct() {
        parent::__construct();
    }

    public function getAll() {
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
                FROM roles r
                ORDER BY r.id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
                FROM roles r
                WHERE r.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $role = $stmt->fetch();
        
        if ($role) {
            // Get permissions for this role
            $sql = "SELECT p.* FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = ?
                    ORDER BY p.name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $role['permissions'] = $stmt->fetchAll();
        }
        
        return $role;
    }

    public function getBySlug($slug) {
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM users WHERE role_id = r.id) as user_count
                FROM roles r
                WHERE r.slug = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        $role = $stmt->fetch();
        
        if ($role) {
            // Get permissions for this role
            $sql = "SELECT p.* FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = ?
                    ORDER BY p.name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$role['id']]);
            $role['permissions'] = $stmt->fetchAll();
        }
        
        return $role;
    }

    public function create($data) {
        $this->db->beginTransaction();
        
        try {
            // Insert role
            $sql = "INSERT INTO roles (name, slug, description, created_at, updated_at)
                    VALUES (?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['description'] ?? null
            ]);
            
            $roleId = $this->db->lastInsertId();
            
            // Assign permissions if provided
            if (!empty($data['permissions'])) {
                $sql = "INSERT INTO role_permissions (role_id, permission_id)
                        VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                
                foreach ($data['permissions'] as $permissionId) {
                    $stmt->execute([$roleId, $permissionId]);
                }
            }
            
            $this->db->commit();
            return $roleId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        $this->db->beginTransaction();
        
        try {
            // Update role
            $sql = "UPDATE roles 
                    SET name = ?, slug = ?, description = ?, updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $id
            ]);
            
            // Update permissions if provided
            if (isset($data['permissions'])) {
                // Remove existing permissions
                $sql = "DELETE FROM role_permissions WHERE role_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);
                
                // Add new permissions
                if (!empty($data['permissions'])) {
                    $sql = "INSERT INTO role_permissions (role_id, permission_id)
                            VALUES (?, ?)";
                    $stmt = $this->db->prepare($sql);
                    
                    foreach ($data['permissions'] as $permissionId) {
                        $stmt->execute([$id, $permissionId]);
                    }
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        // Check if role has any users
        $sql = "SELECT COUNT(*) as user_count FROM users WHERE role_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['user_count'] > 0) {
            return false; // Cannot delete role with users
        }
        
        $this->db->beginTransaction();
        
        try {
            // Remove role permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            // Delete role
            $sql = "DELETE FROM roles WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAllPermissions() {
        $sql = "SELECT * FROM permissions ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPermissionById($id) {
        $sql = "SELECT * FROM permissions WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createPermission($data) {
        $sql = "INSERT INTO permissions (name, slug, description, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updatePermission($id, $data) {
        $sql = "UPDATE permissions 
                SET name = ?, slug = ?, description = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $id
        ]);
    }

    public function deletePermission($id) {
        // Check if permission is assigned to any roles
        $sql = "SELECT COUNT(*) as role_count FROM role_permissions WHERE permission_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['role_count'] > 0) {
            return false; // Cannot delete permission that is assigned to roles
        }
        
        $sql = "DELETE FROM permissions WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function hasPermission($roleId, $permissionSlug) {
        $sql = "SELECT COUNT(*) as has_permission
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = ? AND p.slug = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $permissionSlug]);
        $result = $stmt->fetch();
        
        return $result['has_permission'] > 0;
    }

    public function getPermissionsByRole($roleId) {
        $sql = "SELECT p.* FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = ?
                ORDER BY p.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    public function assignPermission($roleId, $permissionId) {
        // Check if permission is already assigned
        $sql = "SELECT COUNT(*) as exists FROM role_permissions 
                WHERE role_id = ? AND permission_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId, $permissionId]);
        $result = $stmt->fetch();
        
        if ($result['exists'] > 0) {
            return true; // Permission already assigned
        }
        
        $sql = "INSERT INTO role_permissions (role_id, permission_id)
                VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function removePermission($roleId, $permissionId) {
        $sql = "DELETE FROM role_permissions 
                WHERE role_id = ? AND permission_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function getDefaultRoles() {
        return [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access to all features and settings'
            ],
            [
                'name' => 'Author',
                'slug' => 'author',
                'description' => 'Can create and manage their own posts'
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Can create and manage all posts'
            ],
            [
                'name' => 'Subscriber',
                'slug' => 'subscriber',
                'description' => 'Can read posts and leave comments'
            ]
        ];
    }

    public function getDefaultPermissions() {
        return [
            // User management
            ['name' => 'View Users', 'slug' => 'users.view'],
            ['name' => 'Create Users', 'slug' => 'users.create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete'],
            
            // Role management
            ['name' => 'View Roles', 'slug' => 'roles.view'],
            ['name' => 'Create Roles', 'slug' => 'roles.create'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete'],
            
            // Post management
            ['name' => 'View Posts', 'slug' => 'posts.view'],
            ['name' => 'Create Posts', 'slug' => 'posts.create'],
            ['name' => 'Edit Posts', 'slug' => 'posts.edit'],
            ['name' => 'Delete Posts', 'slug' => 'posts.delete'],
            ['name' => 'Publish Posts', 'slug' => 'posts.publish'],
            
            // Category management
            ['name' => 'View Categories', 'slug' => 'categories.view'],
            ['name' => 'Create Categories', 'slug' => 'categories.create'],
            ['name' => 'Edit Categories', 'slug' => 'categories.edit'],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete'],
            
            // Comment management
            ['name' => 'View Comments', 'slug' => 'comments.view'],
            ['name' => 'Create Comments', 'slug' => 'comments.create'],
            ['name' => 'Edit Comments', 'slug' => 'comments.edit'],
            ['name' => 'Delete Comments', 'slug' => 'comments.delete'],
            ['name' => 'Moderate Comments', 'slug' => 'comments.moderate'],
            
            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit'],
            
            // Activity logs
            ['name' => 'View Activity Logs', 'slug' => 'logs.view'],
            ['name' => 'Delete Activity Logs', 'slug' => 'logs.delete']
        ];
    }

    public function installDefaultRolesAndPermissions() {
        $this->db->beginTransaction();
        
        try {
            // Install default permissions
            $permissions = $this->getDefaultPermissions();
            $permissionMap = [];
            
            foreach ($permissions as $permission) {
                $sql = "INSERT INTO permissions (name, slug, description, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $permission['name'],
                    $permission['slug'],
                    $permission['description'] ?? null
                ]);
                $permissionMap[$permission['slug']] = $this->db->lastInsertId();
            }
            
            // Install default roles with their permissions
            $roles = $this->getDefaultRoles();
            $rolePermissions = [
                'admin' => array_keys($permissionMap), // All permissions
                'editor' => [
                    'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                    'comments.view', 'comments.edit', 'comments.delete', 'comments.moderate'
                ],
                'author' => [
                    'posts.view', 'posts.create', 'posts.edit',
                    'categories.view',
                    'comments.view', 'comments.create'
                ],
                'subscriber' => [
                    'posts.view',
                    'categories.view',
                    'comments.view', 'comments.create'
                ]
            ];
            
            foreach ($roles as $role) {
                $sql = "INSERT INTO roles (name, slug, description, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $role['name'],
                    $role['slug'],
                    $role['description']
                ]);
                
                $roleId = $this->db->lastInsertId();
                
                // Assign permissions to role
                if (isset($rolePermissions[$role['slug']])) {
                    $sql = "INSERT INTO role_permissions (role_id, permission_id)
                            VALUES (?, ?)";
                    $stmt = $this->db->prepare($sql);
                    
                    foreach ($rolePermissions[$role['slug']] as $permissionSlug) {
                        if (isset($permissionMap[$permissionSlug])) {
                            $stmt->execute([$roleId, $permissionMap[$permissionSlug]]);
                        }
                    }
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
} 