<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View; // Ensure this line is present

class BaseController extends Controller {
    protected $db;
    protected $view; // Declare the $view property

    public function __construct() {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance();
        $this->view = new View(); // Initialize the View class
    }

    protected function view($view, $data = []) {
        // Delegate rendering to the View class, which handles layouts
        $this->view->render($view, $data);
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