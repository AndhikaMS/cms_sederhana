<?php
namespace App\Core;

use App\Helpers\Functions;
use App\Core\Auth;

abstract class Controller {
    protected $db;
    protected $view;
    protected $auth;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->view = new View();
        $this->auth = Auth::getInstance();
    }

    /**
     * Check if request method is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request method is GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Get POST data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get FILES data
     */
    protected function files($key = null) {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key] ?? null;
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn() {
        return Functions::isLoggedIn();
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission($permission) {
        return Functions::hasPermission($permission);
    }

    /**
     * Require login
     */
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            Functions::setFlash('error', 'Please login to access this page.');
            $this->redirect('/login');
        }
    }

    /**
     * Require permission
     */
    protected function requirePermission($permission) {
        $this->requireLogin();
        if (!$this->hasPermission($permission)) {
            Functions::setFlash('error', 'You do not have permission to access this page.');
            $this->redirect('/');
        }
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        Functions::redirect($url);
    }

    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        Functions::setFlash($type, $message);
    }

    /**
     * Get flash message
     */
    protected function getFlash($type) {
        return Functions::getFlash($type);
    }

    /**
     * Check if flash message exists
     */
    protected function hasFlash($type) {
        return Functions::hasFlash($type);
    }

    /**
     * Clean input data
     */
    protected function clean($data) {
        return Functions::clean($data);
    }

    /**
     * Render view
     */
    protected function view($view, $data = []) {
        // Add common data
        $data['title'] = $data['title'] ?? 'CMS Sederhana';
        $data['current_user'] = Functions::getCurrentUser();
        
        // Render view
        $this->view->render($view, $data);
    }

    /**
     * Check if current URL matches
     */
    protected function isCurrentUrl($url) {
        $current_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $current_url === $url;
    }

    /**
     * Get current URL
     */
    protected function getCurrentUrl() {
        return Functions::getCurrentUrl();
    }

    /**
     * Get base URL
     */
    protected function getBaseUrl() {
        return Functions::getBaseUrl();
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf() {
        if (!$this->isPost()) {
            return true;
        }

        $token = $this->post('csrf_token');
        if (!$token || $token !== $_SESSION['csrf_token']) {
            $this->setFlash('error', 'Invalid CSRF token.');
            $this->redirect($this->getCurrentUrl());
        }

        return true;
    }

    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Get CSRF token
     */
    protected function getCsrfToken() {
        return $this->generateCsrfToken();
    }

    /**
     * Get CSRF token field
     */
    protected function csrfField() {
        return '<input type="hidden" name="csrf_token" value="' . $this->getCsrfToken() . '">';
    }

    /**
     * Validate and sanitize input
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Required
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required.';
                continue;
            }
            
            if (empty($value)) {
                continue;
            }
            
            // Email
            if (strpos($rule, 'email') !== false && !Functions::validateEmail($value)) {
                $errors[$field] = 'Invalid email format.';
            }
            
            // URL
            if (strpos($rule, 'url') !== false && !Functions::validateUrl($value)) {
                $errors[$field] = 'Invalid URL format.';
            }
            
            // Integer
            if (strpos($rule, 'int') !== false && !Functions::validateInt($value)) {
                $errors[$field] = ucfirst($field) . ' must be an integer.';
            }
            
            // Float
            if (strpos($rule, 'float') !== false && !Functions::validateFloat($value)) {
                $errors[$field] = ucfirst($field) . ' must be a number.';
            }
            
            // Boolean
            if (strpos($rule, 'bool') !== false && !Functions::validateBool($value)) {
                $errors[$field] = ucfirst($field) . ' must be a boolean.';
            }
            
            // Min length
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int)$matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $min . ' characters.';
                }
            }
            
            // Max length
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int)$matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $max . ' characters.';
                }
            }
            
            // Min value
            if (preg_match('/min_value:(\d+)/', $rule, $matches)) {
                $min = (int)$matches[1];
                if ($value < $min) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $min . '.';
                }
            }
            
            // Max value
            if (preg_match('/max_value:(\d+)/', $rule, $matches)) {
                $max = (int)$matches[1];
                if ($value > $max) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $max . '.';
                }
            }
            
            // Match
            if (preg_match('/match:(\w+)/', $rule, $matches)) {
                $field_to_match = $matches[1];
                if ($value !== ($data[$field_to_match] ?? null)) {
                    $errors[$field] = ucfirst($field) . ' must match ' . $field_to_match . '.';
                }
            }
            
            // In
            if (preg_match('/in:([^,]+)/', $rule, $matches)) {
                $allowed_values = explode(',', $matches[1]);
                if (!in_array($value, $allowed_values)) {
                    $errors[$field] = ucfirst($field) . ' must be one of: ' . implode(', ', $allowed_values) . '.';
                }
            }
            
            // Sanitize
            if (strpos($rule, 'sanitize') !== false) {
                if (strpos($rule, 'email') !== false) {
                    $data[$field] = Functions::sanitizeEmail($value);
                } elseif (strpos($rule, 'url') !== false) {
                    $data[$field] = Functions::sanitizeUrl($value);
                } elseif (strpos($rule, 'int') !== false) {
                    $data[$field] = Functions::sanitizeInt($value);
                } elseif (strpos($rule, 'float') !== false) {
                    $data[$field] = Functions::sanitizeFloat($value);
                } else {
                    $data[$field] = Functions::sanitizeString($value);
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $data
        ];
    }
} 