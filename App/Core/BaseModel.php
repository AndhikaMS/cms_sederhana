<?php

namespace App\Core;

class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // You can add common model methods here, e.g., for fetching all records, by ID, etc.
    public function getAll() {
        // Implement logic to get all records from the model's table
        // This is a placeholder
        return [];
    }

    public function getById($id) {
        // Implement logic to get a single record by ID
        // This is a placeholder
        return null;
    }
    
    // Placeholder for a generic 'clean' method, if it was intended to be in BaseModel
    // If not, remove this or implement it based on your original design.
    protected function clean($data) {
        // Basic sanitization
        return htmlspecialchars(strip_tags(trim($data)));
    }
}