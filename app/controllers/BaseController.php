<?php
namespace App\Controllers;

class BaseController {
    protected $db;
    protected $view;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    protected function view($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            throw new \Exception("View {$view} not found");
        }
        
        // Get the contents of the buffer
        $content = ob_get_clean();
        
        // Include the layout
        require_once __DIR__ . '/../views/layouts/header.php';
        echo $content;
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function getPost($key = null) {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    protected function getQuery($key = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }
} 