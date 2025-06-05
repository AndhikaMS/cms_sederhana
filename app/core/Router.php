<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $params = [];
    private $notFoundCallback;

    /**
     * Add route
     */
    public function add($method, $route, $controller, $action = null) {
        // Convert route to regex pattern
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[^/]+)', $route);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        // Store route
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];
        
        return $this;
    }

    /**
     * Add GET route
     */
    public function get($route, $controller, $action = null) {
        return $this->add('GET', $route, $controller, $action);
    }

    /**
     * Add POST route
     */
    public function post($route, $controller, $action = null) {
        return $this->add('POST', $route, $controller, $action);
    }

    /**
     * Add PUT route
     */
    public function put($route, $controller, $action = null) {
        return $this->add('PUT', $route, $controller, $action);
    }

    /**
     * Add DELETE route
     */
    public function delete($route, $controller, $action = null) {
        return $this->add('DELETE', $route, $controller, $action);
    }

    /**
     * Add PATCH route
     */
    public function patch($route, $controller, $action = null) {
        return $this->add('PATCH', $route, $controller, $action);
    }

    /**
     * Add OPTIONS route
     */
    public function options($route, $controller, $action = null) {
        return $this->add('OPTIONS', $route, $controller, $action);
    }

    /**
     * Add ANY route
     */
    public function any($route, $controller, $action = null) {
        return $this->add('ANY', $route, $controller, $action);
    }

    /**
     * Set 404 callback
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
        return $this;
    }

    /**
     * Match route
     */
    public function match($url, $method) {
        // Remove query string
        $url = parse_url($url, PHP_URL_PATH);
        
        // Remove trailing slash
        $url = rtrim($url, '/');
        
        // Add leading slash if not present
        if (empty($url)) {
            $url = '/';
        }
        
        foreach ($this->routes as $route) {
            // Check method
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                continue;
            }
            
            // Check pattern
            if (preg_match($route['pattern'], $url, $matches)) {
                // Get named parameters
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                
                return [
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $this->params
                ];
            }
        }
        
        return false;
    }

    /**
     * Dispatch route
     */
    public function dispatch() {
        $url = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Handle PUT, DELETE, PATCH methods
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        $route = $this->match($url, $method);
        
        if ($route) {
            $controller = $route['controller'];
            $action = $route['action'];
            $params = $route['params'];
            
            // Check if controller exists
            if (!class_exists($controller)) {
                throw new \Exception("Controller not found: {$controller}");
            }
            
            // Create controller instance
            $controller = new $controller();
            
            // Check if action exists
            if ($action && !method_exists($controller, $action)) {
                throw new \Exception("Action not found: {$action}");
            }
            
            // Call action
            if ($action) {
                return call_user_func_array([$controller, $action], $params);
            } else {
                return $controller();
            }
        }
        
        // Handle 404
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }
        
        throw new \Exception('No route matched.');
    }

    /**
     * Get current route
     */
    public function getCurrentRoute() {
        $url = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Handle PUT, DELETE, PATCH methods
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        return $this->match($url, $method);
    }

    /**
     * Get route parameters
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Get route parameter
     */
    public function getParam($key, $default = null) {
        return $this->params[$key] ?? $default;
    }

    /**
     * Generate URL
     */
    public function url($route, $params = []) {
        foreach ($this->routes as $r) {
            if ($r['controller'] === $route[0] && $r['action'] === $route[1]) {
                $url = $r['pattern'];
                
                // Replace named parameters
                foreach ($params as $key => $value) {
                    $url = str_replace('{' . $key . '}', $value, $url);
                }
                
                // Remove regex pattern
                $url = preg_replace('/\([^)]+\)/', '', $url);
                $url = str_replace('\/', '/', $url);
                $url = rtrim($url, '/');
                
                return $url;
            }
        }
        
        throw new \Exception("Route not found: {$route[0]}::{$route[1]}");
    }

    /**
     * Redirect to route
     */
    public function redirect($route, $params = []) {
        $url = $this->url($route, $params);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect to URL
     */
    public function redirectTo($url) {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back
     */
    public function back() {
        $url = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$url}");
        exit;
    }

    /**
     * Refresh current page
     */
    public function refresh() {
        $url = $_SERVER['REQUEST_URI'];
        header("Location: {$url}");
        exit;
    }
} 