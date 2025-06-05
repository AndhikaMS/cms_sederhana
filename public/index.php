<?php
// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Start session
session_start();

// Create router instance
$router = new \App\Core\Router();

// Define routes
$router->get('/', 'HomeController', 'index');
$router->get('/login', 'AuthController', 'loginForm');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'registerForm');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');

// Posts routes
$router->get('/posts', 'PostController', 'index');
$router->get('/posts/create', 'PostController', 'create');
$router->post('/posts/store', 'PostController', 'store');
$router->get('/posts/{id}', 'PostController', 'show');
$router->get('/posts/{id}/edit', 'PostController', 'edit');
$router->post('/posts/{id}/update', 'PostController', 'update');
$router->post('/posts/{id}/delete', 'PostController', 'delete');

// Categories routes
$router->get('/categories', 'CategoryController', 'index');
$router->get('/categories/create', 'CategoryController', 'create');
$router->post('/categories/store', 'CategoryController', 'store');
$router->get('/categories/{id}', 'CategoryController', 'show');
$router->get('/categories/{id}/edit', 'CategoryController', 'edit');
$router->post('/categories/{id}/update', 'CategoryController', 'update');
$router->post('/categories/{id}/delete', 'CategoryController', 'delete');

// Users routes
$router->get('/users', 'UserController', 'index');
$router->get('/profile', 'UserController', 'profile');
$router->post('/profile/update', 'UserController', 'updateProfile');

// Set 404 handler
$router->setNotFound(function() {
    header("HTTP/1.0 404 Not Found");
    echo "404 - Page Not Found";
});

// Dispatch the request
$router->dispatch(); 