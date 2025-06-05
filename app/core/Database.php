<?php
namespace App\Core;

class Database {
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct() {
        $this->config = require_once __DIR__ . '/../config/database.php';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $this->connection = new \mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database'],
                $this->config['port']
            );

            if ($this->connection->connect_error) {
                throw new \Exception("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset($this->config['charset']);
        } catch (\Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }

    public function getLastId() {
        return $this->connection->insert_id;
    }

    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
} 