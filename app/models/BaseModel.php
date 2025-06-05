<?php
namespace App\Models;

class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    public function find($id) {
        $id = $this->db->escape($id);
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = '{$id}'";
        $result = $this->db->query($query);
        return $result->fetch_assoc();
    }

    public function all() {
        $query = "SELECT * FROM {$this->table}";
        $result = $this->db->query($query);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public function create($data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = $this->db->escape($key);
            $values[] = "'" . $this->db->escape($value) . "'";
        }
        
        $fields = implode(', ', $fields);
        $values = implode(', ', $values);
        
        $query = "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
        
        if ($this->db->query($query)) {
            return $this->db->getLastId();
        }
        
        return false;
    }

    public function update($id, $data) {
        $sets = [];
        
        foreach ($data as $key => $value) {
            $key = $this->db->escape($key);
            $value = $this->db->escape($value);
            $sets[] = "{$key} = '{$value}'";
        }
        
        $sets = implode(', ', $sets);
        $id = $this->db->escape($id);
        
        $query = "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = '{$id}'";
        
        return $this->db->query($query);
    }

    public function delete($id) {
        $id = $this->db->escape($id);
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = '{$id}'";
        return $this->db->query($query);
    }

    public function where($conditions) {
        $where = [];
        
        foreach ($conditions as $key => $value) {
            $key = $this->db->escape($key);
            $value = $this->db->escape($value);
            $where[] = "{$key} = '{$value}'";
        }
        
        $where = implode(' AND ', $where);
        $query = "SELECT * FROM {$this->table} WHERE {$where}";
        
        $result = $this->db->query($query);
        $items = [];
        
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        return $items;
    }

    public function count() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['count'];
    }
} 