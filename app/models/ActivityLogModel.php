<?php
namespace App\Models;

class ActivityLogModel extends BaseModel {
    public function __construct() {
        parent::__construct();
    }

    public function getAll($page = 1, $filters = []) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $where = ['1=1'];
        $params = [];
        
        // Filter by user
        if (!empty($filters['user_id'])) {
            $where[] = 'al.user_id = ?';
            $params[] = $filters['user_id'];
        }
        
        // Filter by action
        if (!empty($filters['action'])) {
            $where[] = 'al.action = ?';
            $params[] = $filters['action'];
        }
        
        // Filter by date range
        if (!empty($filters['start_date'])) {
            $where[] = 'al.created_at >= ?';
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $where[] = 'al.created_at <= ?';
            $params[] = $filters['end_date'] . ' 23:59:59';
        }
        
        // Search in description
        if (!empty($filters['search'])) {
            $where[] = 'al.description LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Get total records
        $sql = "SELECT COUNT(*) as total 
                FROM activity_logs al 
                WHERE $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Get logs with user info
        $sql = "SELECT al.*, u.name as user_name, u.email as user_email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE $whereClause
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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

    public function getById($id) {
        $sql = "SELECT al.*, u.name as user_name, u.email as user_email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['action'],
            $data['description'],
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'],
            $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT']
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM activity_logs WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function deleteOldLogs($days = 30) {
        $sql = "DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$days]);
    }

    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total_logs,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT action) as unique_actions,
                MIN(created_at) as first_log,
                MAX(created_at) as last_log
                FROM activity_logs";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getActionStats() {
        $sql = "SELECT action, COUNT(*) as count
                FROM activity_logs
                GROUP BY action
                ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserStats($userId) {
        $sql = "SELECT 
                COUNT(*) as total_logs,
                COUNT(DISTINCT action) as unique_actions,
                MIN(created_at) as first_log,
                MAX(created_at) as last_log
                FROM activity_logs
                WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getUserActions($userId) {
        $sql = "SELECT action, COUNT(*) as count
                FROM activity_logs
                WHERE user_id = ?
                GROUP BY action
                ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getRecentActivity($limit = 10) {
        $sql = "SELECT al.*, u.name as user_name, u.email as user_email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getActivityByDateRange($startDate, $endDate) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM activity_logs
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);
        return $stmt->fetchAll();
    }

    public function getActivityByHour() {
        $sql = "SELECT HOUR(created_at) as hour, COUNT(*) as count
                FROM activity_logs
                GROUP BY HOUR(created_at)
                ORDER BY hour ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActivityByDay() {
        $sql = "SELECT DAYNAME(created_at) as day, COUNT(*) as count
                FROM activity_logs
                GROUP BY DAYNAME(created_at)
                ORDER BY FIELD(DAYNAME(created_at), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActivityByMonth() {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                FROM activity_logs
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopUsers($limit = 10) {
        $sql = "SELECT u.id, u.name, u.email, COUNT(*) as activity_count
                FROM activity_logs al
                JOIN users u ON al.user_id = u.id
                GROUP BY u.id, u.name, u.email
                ORDER BY activity_count DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getTopActions($limit = 10) {
        $sql = "SELECT action, COUNT(*) as count
                FROM activity_logs
                GROUP BY action
                ORDER BY count DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getTopIPs($limit = 10) {
        $sql = "SELECT ip_address, COUNT(*) as count
                FROM activity_logs
                GROUP BY ip_address
                ORDER BY count DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function search($keyword, $page = 1) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get total records
        $sql = "SELECT COUNT(*) as total 
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.description LIKE ? 
                OR al.action LIKE ?
                OR u.name LIKE ?
                OR u.email LIKE ?";
        
        $keyword = "%$keyword%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$keyword, $keyword, $keyword, $keyword]);
        $total = $stmt->fetch()['total'];
        
        // Get logs
        $sql = "SELECT al.*, u.name as user_name, u.email as user_email
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.description LIKE ? 
                OR al.action LIKE ?
                OR u.name LIKE ?
                OR u.email LIKE ?
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$keyword, $keyword, $keyword, $keyword, $perPage, $offset]);
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
} 