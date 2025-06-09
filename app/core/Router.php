<?php
    namespace App\Core;

    class Router {
        private $routes = [];
        private $params = [];
        private $notFoundCallback;
        private $baseUrl;

        public function __construct(array $config) {
            // Get the base URL from the global config
            $this->baseUrl = parse_url($config['url'], PHP_URL_PATH);
            $this->baseUrl = rtrim($this->baseUrl, '/'); // Ensure no trailing slash
            error_log("Router Base URL: " . $this->baseUrl);
        }

        /**
         * Add route
         */
        public function add($method, $route, $controller, $action = null) {
            // Convert route to regex pattern
            $pattern = preg_replace('/{([a-zA-Z0-9_]+)}/', '(?P<\\1>[^/]+)', $route);
            $pattern = str_replace('/', '\\/', $pattern);
            $pattern = '/^' . $pattern . '$/';
            
            // Store route
            $this->routes[] = [
                'method' => strtoupper($method),
                'pattern' => $pattern,
                'controller' => $controller,
                'action' => $action
            ];
            error_log("Route added: " . strtoupper($method) . " " . $route . " -> " . $controller . "@" . $action);
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
            error_log("Matching URL: " . $url . " with method: " . $method);
            // Remove query string
            $url = parse_url($url, PHP_URL_PATH);
            error_log("URL after parse_url: " . $url);
            
            // Remove base URL prefix if present
            if (!empty($this->baseUrl) && strpos($url, $this->baseUrl) === 0) {
                $originalUrl = $url;
                $url = substr($url, strlen($this->baseUrl));
                error_log("URL after removing base URL ({$this->baseUrl}): " . $url . " (Original: " . $originalUrl . ")");
            } else {
                error_log("Base URL ({$this->baseUrl}) not found at start of URL: " . $url);
            }

            // Remove trailing slash
            $url = rtrim($url, '/');
            
            // Add leading slash if not present
            if (empty($url)) {
                $url = '/';
            }
            error_log("Final URL for matching: " . $url);

            foreach ($this->routes as $route) {
                // Check method
                if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                    error_log("Skipping route (method mismatch): " . $route['method'] . " vs " . $method);
                    continue;
                }
                
                // Check pattern
                if (preg_match($route['pattern'], $url, $matches)) {
                    error_log("Route matched: " . $route['pattern'] . " for URL: " . $url);
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
                error_log("Route pattern did not match: " . $route['pattern'] . " against URL: " . $url);
            }
            
            error_log("No route matched for URL: " . $url);
            return false;
        }

        /**
         * Dispatch route
         */
        public function dispatch() {
            $url = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            error_log("Dispatching request for URL: " . $url . " and method: " . $method);
            
            // Handle PUT, DELETE, PATCH methods
            if ($method === 'POST' && isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
                error_log("Method changed to: " . $method . " via _method POST parameter.");
            }
            
            $route = $this->match($url, $method);
            
            if ($route) {
                error_log("Route found: " . $route['controller'] . "@" . $route['action']);
                $controller = $route['controller'];
                $action = $route['action'];
                $params = $route['params'];
                
                // Parse controller@action string
                if (strpos($controller, '@') !== false) {
                    list($controllerClass, $action) = explode('@', $controller);
                    $controller = $controllerClass;
                }
                error_log("Controller Class: " . $controller . ", Action: " . $action);
                
                // Check if controller exists
                if (!class_exists($controller)) {
                    // Try with App\\Controllers namespace
                    $fullControllerClass = 'App\\Controllers\\' . $controller;
                    if (!class_exists($fullControllerClass)) {
                        error_log("Controller not found: " . $controller . " or " . $fullControllerClass);
                        throw new \Exception("Controller not found: {$controller}");
                    }
                    $controller = $fullControllerClass;
                    error_log("Using full Controller Class: " . $controller);
                }
                
                // Create controller instance
                $controller = new $controller();
                
                // Check if action exists
                if ($action && !method_exists($controller, $action)) {
                    error_log("Action not found: " . $action . " in controller: " . get_class($controller));
                    throw new \Exception("Action not found: {$action}");
                }
                
                // Call action
                if ($action) {
                    error_log("Calling controller action: " . get_class($controller) . "->" . $action . " with params: " . json_encode($params));
                    return call_user_func_array([$controller, $action], $params);
                } else {
                    error_log("Calling controller as callable: " . get_class($controller));
                    return $controller();
                }
            }
            
            // Handle 404
            error_log("No route matched. Handling 404.");
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
            error_log("Router::url - Input route: " . $route . ", Params: " . json_encode($params));
            error_log("Router::url - Current baseUrl: " . $this->baseUrl);
            $url = $route;
            foreach ($params as $key => $value) {
                $url = str_replace('{' . $key . '}', $value, $url);
            }

            // Ensure the route always starts with a single slash
            // And remove any trailing slash from baseUrl to prevent double slashes
            $finalUrl = rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
            error_log("Router::url - Final URL generated: " . $finalUrl);
            return $finalUrl;
        }

        /**
         * Redirect to route
         */
        public function redirect($route, $params = []) {
            header('Location: ' . $this->url($route, $params));
            exit;
        }

        /**
         * Redirect to URL
         */
        public function redirectTo($url) {
            header('Location: ' . $url);
            exit;
        }

        /**
         * Redirect back
         */
        public function back() {
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: ' . $this->baseUrl . '/'); // Fallback to homepage
            }
            exit;
        }

        /**
         * Refresh page
         */
        public function refresh() {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }