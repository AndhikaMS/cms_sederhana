<?php
namespace App\Models;

class CategoryModel {
    private $db;
    private $table = 'categories';
    private $perPage = 20;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($page = 1, $filters = []) {
        $offset = ($page - 1) * $this->perPage;
        $where = [];
        $params = [];

        // Build where clause based on filters
        if (!empty($filters['status'])) {
            $where[] = "c.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['parent_id'])) {
            $where[] = "c.parent_id = ?";
            $params[] = $filters['parent_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(c.name LIKE ? OR c.description LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Get total records for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} c {$whereClause}";
        $total = $this->db->prepare($countQuery)->execute($params)->single()['total'];

        // Get categories with related data
        $query = "SELECT c.*, p.name as parent_name, 
                 (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count 
                 FROM {$this->table} c 
                 LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                 {$whereClause}
                 ORDER BY c.name ASC 
                 LIMIT ? OFFSET ?";
        
        $params[] = $this->perPage;
        $params[] = $offset;

        $categories = $this->db->prepare($query)->execute($params)->resultSet();

        return [
            'data' => $categories,
            'total' => $total,
            'per_page' => $this->perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $this->perPage)
        ];
    }

    public function getById($id) {
        $query = "SELECT c.*, p.name as parent_name,
                 (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count 
                 FROM {$this->table} c 
                 LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                 WHERE c.id = ?";
        
        return $this->db->prepare($query)->execute([$id])->single();
    }

    public function getBySlug($slug) {
        $query = "SELECT c.*, p.name as parent_name,
                 (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count 
                 FROM {$this->table} c 
                 LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                 WHERE c.slug = ?";
        
        return $this->db->prepare($query)->execute([$slug])->single();
    }

    public function create($data) {
        // Generate slug from name
        $data['slug'] = Functions::generateSlug($data['name']);

        $query = "INSERT INTO {$this->table} (name, slug, description, parent_id, status) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['parent_id'] ?? null,
            $data['status'] ?? 'active'
        ];

        $this->db->prepare($query)->execute($params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        // Generate new slug if name changed
        if (!empty($data['name'])) {
            $data['slug'] = Functions::generateSlug($data['name']);
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'slug', 'description', 'parent_id', 'status'])) {
                $fields[] = "{$key} = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $query = "UPDATE {$this->table} SET " . implode(", ", $fields) . " WHERE id = ?";
        
        return $this->db->prepare($query)->execute($params)->rowCount();
    }

    public function delete($id) {
        // Check if category has posts
        $query = "SELECT COUNT(*) as count FROM posts WHERE category_id = ?";
        $result = $this->db->prepare($query)->execute([$id])->single();
        
        if ($result['count'] > 0) {
            return false; // Cannot delete category with posts
        }

        // Check if category has subcategories
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ?";
        $result = $this->db->prepare($query)->execute([$id])->single();
        
        if ($result['count'] > 0) {
            return false; // Cannot delete category with subcategories
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->prepare($query)->execute([$id])->rowCount();
    }

    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }

    public function getTree() {
        $query = "SELECT c.*, p.name as parent_name,
                 (SELECT COUNT(*) FROM posts WHERE category_id = c.id) as post_count 
                 FROM {$this->table} c 
                 LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                 ORDER BY c.parent_id ASC, c.name ASC";
        
        $categories = $this->db->prepare($query)->execute()->resultSet();
        
        return $this->buildTree($categories);
    }

    private function buildTree($categories, $parentId = null) {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }
        
        return $tree;
    }

    public function getParents() {
        $query = "SELECT * FROM {$this->table} WHERE parent_id IS NULL ORDER BY name ASC";
        return $this->db->prepare($query)->execute()->resultSet();
    }

    public function getChildren($parentId) {
        $query = "SELECT * FROM {$this->table} WHERE parent_id = ? ORDER BY name ASC";
        return $this->db->prepare($query)->execute([$parentId])->resultSet();
    }

    public function getPopularCategories($limit = 5) {
        $query = "SELECT c.*, COUNT(p.id) as post_count 
                 FROM {$this->table} c 
                 LEFT JOIN posts p ON c.id = p.category_id 
                 WHERE c.status = 'active' 
                 GROUP BY c.id 
                 ORDER BY post_count DESC 
                 LIMIT ?";
        
        return $this->db->prepare($query)->execute([$limit])->resultSet();
    }

    public function search($keyword, $page = 1) {
        return $this->getAll($page, ['search' => $keyword]);
    }

    public function getByStatus($status, $page = 1) {
        return $this->getAll($page, ['status' => $status]);
    }

    public function getStats() {
        $query = "SELECT 
            COUNT(*) as total_categories,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_categories,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_categories,
            SUM(CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END) as parent_categories,
            SUM(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END) as child_categories
            FROM {$this->table}";
        
        return $this->db->prepare($query)->execute()->single();
    }

    public function validateParent($id, $parentId) {
        if ($parentId === null) {
            return true;
        }

        // Check if parent exists
        $query = "SELECT id FROM {$this->table} WHERE id = ?";
        $parent = $this->db->prepare($query)->execute([$parentId])->single();
        
        if (!$parent) {
            return false;
        }

        // Check for circular reference
        if ($id == $parentId) {
            return false;
        }

        // Check if parent is not a child of this category
        $query = "WITH RECURSIVE category_tree AS (
            SELECT id, parent_id FROM {$this->table} WHERE id = ?
            UNION ALL
            SELECT c.id, c.parent_id FROM {$this->table} c
            INNER JOIN category_tree ct ON c.id = ct.parent_id
        )
        SELECT COUNT(*) as count FROM category_tree WHERE id = ?";
        
        $result = $this->db->prepare($query)->execute([$parentId, $id])->single();
        
        return $result['count'] == 0;
    }
} 