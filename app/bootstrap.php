<?php
// Start session
session_start();

// Load environment variables
$env_file = dirname(__DIR__) . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load configuration
$config = require_once __DIR__ . '/config/app.php';

// Set error reporting
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($config['timezone']);

// Set locale
setlocale(LC_ALL, $config['locale'] . '.UTF-8');

// Register autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});

// Create required directories
$directories = [
    $config['session']['files'],
    $config['upload']['path'],
    $config['cache']['path'],
    $config['log']['path'],
];

foreach ($directories as $directory) {
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Initialize database
try {
    $db = \App\Core\Database::getInstance();
} catch (\Exception $e) {
    if ($config['debug']) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Initialize router
$router = new \App\Core\Router();

// Set 404 handler
$router->notFound(function() {
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
});

// Load routes
require_once __DIR__ . '/routes.php';

// Return router instance
return $router; 