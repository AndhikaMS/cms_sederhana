<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;
    private $statement;
    private $debug = false;

    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=utf8mb4",
                getenv('DB_HOST') ?: 'localhost',
                getenv('DB_NAME') ?: 'cms_sederhana'
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO(
                $dsn,
                getenv('DB_USER') ?: 'root',
                getenv('DB_PASS') ?: '',
                $options
            );
        } catch (PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enable/disable debug mode
     */
    public function setDebug($debug) {
        $this->debug = $debug;
    }

    /**
     * Prepare statement
     */
    public function prepare($sql) {
        $this->statement = $this->pdo->prepare($sql);
        return $this;
    }

    /**
     * Bind value
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    /**
     * Execute statement
     */
    public function execute() {
        try {
            if ($this->debug) {
                echo $this->statement->queryString;
            }
            
            return $this->statement->execute();
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Get single record
     */
    public function single() {
        $this->execute();
        return $this->statement->fetch();
    }

    /**
     * Get all records
     */
    public function resultSet() {
        $this->execute();
        return $this->statement->fetchAll();
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->statement->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /**
     * Query builder: Select
     */
    public function select($table, $columns = '*', $where = null, $orderBy = null, $limit = null, $offset = null) {
        $sql = "SELECT {$columns} FROM {$table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        return $this->prepare($sql);
    }

    /**
     * Query builder: Insert
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        
        $this->prepare($sql);
        
        $i = 1;
        foreach ($data as $value) {
            $this->bind($i++, $value);
        }
        
        return $this->execute();
    }

    /**
     * Query builder: Update
     */
    public function update($table, $data, $where) {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $this->prepare($sql);
        
        $i = 1;
        foreach ($data as $value) {
            $this->bind($i++, $value);
        }
        
        return $this->execute();
    }

    /**
     * Query builder: Delete
     */
    public function delete($table, $where) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        return $this->prepare($sql)->execute();
    }

    /**
     * Query builder: Count
     */
    public function count($table, $where = null) {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->prepare($sql)->single();
        return $result['count'];
    }

    /**
     * Query builder: Exists
     */
    public function exists($table, $where) {
        return $this->count($table, $where) > 0;
    }

    /**
     * Query builder: Join
     */
    public function join($table, $join, $type = 'INNER') {
        $sql = "{$type} JOIN {$table} ON {$join}";
        return $this->prepare($sql);
    }

    /**
     * Query builder: Left Join
     */
    public function leftJoin($table, $join) {
        return $this->join($table, $join, 'LEFT');
    }

    /**
     * Query builder: Right Join
     */
    public function rightJoin($table, $join) {
        return $this->join($table, $join, 'RIGHT');
    }

    /**
     * Query builder: Group By
     */
    public function groupBy($columns) {
        $sql = "GROUP BY {$columns}";
        return $this->prepare($sql);
    }

    /**
     * Query builder: Having
     */
    public function having($condition) {
        $sql = "HAVING {$condition}";
        return $this->prepare($sql);
    }

    /**
     * Query builder: Union
     */
    public function union($sql) {
        $this->statement = $this->pdo->prepare("({$this->statement->queryString}) UNION ({$sql})");
        return $this;
    }

    /**
     * Query builder: Union All
     */
    public function unionAll($sql) {
        $this->statement = $this->pdo->prepare("({$this->statement->queryString}) UNION ALL ({$sql})");
        return $this;
    }

    /**
     * Query builder: Subquery
     */
    public function subquery($sql) {
        return "({$sql})";
    }

    /**
     * Query builder: Raw SQL
     */
    public function raw($sql) {
        return $this->prepare($sql);
    }

    /**
     * Query builder: Get SQL
     */
    public function getSql() {
        return $this->statement->queryString;
    }

    /**
     * Query builder: Get Params
     */
    public function getParams() {
        return $this->statement->getBoundParams();
    }

    /**
     * Query builder: Get Debug Info
     */
    public function getDebugInfo() {
        return [
            'sql' => $this->getSql(),
            'params' => $this->getParams(),
            'error' => $this->statement->errorInfo()
        ];
    }
} 