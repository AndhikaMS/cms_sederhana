<?php

class PostModel {
    private $db;
    private $table = 'posts';
    private $perPage = 10;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($page = 1, $filters = []) {
        $offset = ($page - 1) * $this->perPage;
        $where = [];
        $params = [];

        // Build where clause based on filters
        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = "p.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(p.title LIKE ? OR p.content LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Get total records for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} p {$whereClause}";
        $total = $this->db->prepare($countQuery)->execute($params)->single()['total'];

        // Get posts with related data
        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 {$whereClause}
                 ORDER BY p.created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $params[] = $this->perPage;
        $params[] = $offset;

        $posts = $this->db->prepare($query)->execute($params)->resultSet();

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $this->perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $this->perPage)
        ];
    }

    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.id = ?";
        
        return $this->db->prepare($query)->execute([$id])->single();
    }

    public function getBySlug($slug) {
        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.slug = ?";
        
        return $this->db->prepare($query)->execute([$slug])->single();
    }

    public function create($data) {
        // Generate slug from title
        $data['slug'] = Functions::generateSlug($data['title']);
        
        // Set published_at if status is published
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $query = "INSERT INTO {$this->table} (title, slug, content, excerpt, featured_image, 
                 category_id, user_id, status, comment_status, published_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['excerpt'] ?? null,
            $data['featured_image'] ?? null,
            $data['category_id'] ?? null,
            $data['user_id'],
            $data['status'],
            $data['comment_status'] ?? 'open',
            $data['published_at'] ?? null
        ];

        $this->db->prepare($query)->execute($params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        // Generate new slug if title changed
        if (!empty($data['title'])) {
            $data['slug'] = Functions::generateSlug($data['title']);
        }

        // Set published_at if status changed to published
        if ($data['status'] === 'published') {
            $post = $this->getById($id);
            if ($post['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }

        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'slug', 'content', 'excerpt', 'featured_image', 
                               'category_id', 'status', 'comment_status', 'published_at'])) {
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
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->prepare($query)->execute([$id])->rowCount();
    }

    public function updateStatus($id, $status) {
        $data = ['status' => $status];
        if ($status === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        return $this->update($id, $data);
    }

    public function incrementViewCount($id) {
        $query = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?";
        return $this->db->prepare($query)->execute([$id])->rowCount();
    }

    public function getPopularPosts($limit = 5) {
        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.status = 'published' 
                 ORDER BY p.view_count DESC 
                 LIMIT ?";
        
        return $this->db->prepare($query)->execute([$limit])->resultSet();
    }

    public function getLatestPosts($limit = 5) {
        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.status = 'published' 
                 ORDER BY p.published_at DESC 
                 LIMIT ?";
        
        return $this->db->prepare($query)->execute([$limit])->resultSet();
    }

    public function getRelatedPosts($postId, $limit = 3) {
        $post = $this->getById($postId);
        if (!$post) {
            return [];
        }

        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE p.status = 'published' 
                 AND p.id != ? 
                 AND (p.category_id = ? OR p.user_id = ?) 
                 ORDER BY p.published_at DESC 
                 LIMIT ?";
        
        return $this->db->prepare($query)
            ->execute([$postId, $post['category_id'], $post['user_id'], $limit])
            ->resultSet();
    }

    public function search($keyword, $page = 1) {
        return $this->getAll($page, ['search' => $keyword]);
    }

    public function getByCategory($categoryId, $page = 1) {
        return $this->getAll($page, ['category_id' => $categoryId, 'status' => 'published']);
    }

    public function getByUser($userId, $page = 1) {
        return $this->getAll($page, ['user_id' => $userId]);
    }

    public function getByStatus($status, $page = 1) {
        return $this->getAll($page, ['status' => $status]);
    }

    public function getByDateRange($startDate, $endDate, $page = 1) {
        $where = ["p.created_at BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];

        $offset = ($page - 1) * $this->perPage;

        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} p WHERE " . implode(" AND ", $where);
        $total = $this->db->prepare($countQuery)->execute($params)->single()['total'];

        $query = "SELECT p.*, c.name as category_name, u.username as author_name 
                 FROM {$this->table} p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 LEFT JOIN users u ON p.user_id = u.id 
                 WHERE " . implode(" AND ", $where) . "
                 ORDER BY p.created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $params[] = $this->perPage;
        $params[] = $offset;

        $posts = $this->db->prepare($query)->execute($params)->resultSet();

        return [
            'data' => $posts,
            'total' => $total,
            'per_page' => $this->perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $this->perPage)
        ];
    }

    public function getStats() {
        $query = "SELECT 
            COUNT(*) as total_posts,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_posts,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_posts,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_posts,
            SUM(view_count) as total_views
            FROM {$this->table}";
        
        return $this->db->prepare($query)->execute()->single();
    }

    public function getStatsByUser($userId) {
        $query = "SELECT 
            COUNT(*) as total_posts,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_posts,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_posts,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_posts,
            SUM(view_count) as total_views
            FROM {$this->table}
            WHERE user_id = ?";
        
        return $this->db->prepare($query)->execute([$userId])->single();
    }
} 