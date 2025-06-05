<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $params = [];
    private $notFoundCallback;

    public function add($method, $route, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $route,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function get($route, $controller, $action) {
        $this->add('GET', $route, $controller, $action);
    }

    public function post($route, $controller, $action) {
        $this->add('POST', $route, $controller, $action);
    }

    public function setNotFound($callback) {
        $this->notFoundCallback = $callback;
    }

    public function dispatch() {
        $url = $this->getUrl();
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertRouteToRegex($route['route']);
            if (preg_match($pattern, $url, $matches)) {
                array_shift($matches); // Remove full match
                $this->params = $matches;
                
                $controller = "App\\Controllers\\" . $route['controller'];
                $action = $route['action'];
                
                if (class_exists($controller)) {
                    $controllerInstance = new $controller();
                    if (method_exists($controllerInstance, $action)) {
                        return call_user_func_array([$controllerInstance, $action], $this->params);
                    }
                }
            }
        }

        // No route found
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found";
        }
    }

    private function getUrl() {
        $url = $_SERVER['REQUEST_URI'];
        $url = strtok($url, '?');
        $url = rtrim($url, '/');
        return $url ?: '/';
    }

    private function convertRouteToRegex($route) {
        $route = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $route);
        return '#^' . $route . '$#';
    }

    public function getParams() {
        return $this->params;
    }
} 