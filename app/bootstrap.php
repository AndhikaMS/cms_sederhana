<?php

// Load Composer's autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Start session
session_start();
session_regenerate_id(true);

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
$config_path = __DIR__ . '/config/app.php';
$config = []; // Initialize as empty array
if (file_exists($config_path)) {
    $loaded_config = require_once $config_path;
    if (is_array($loaded_config)) {
        $config = $loaded_config;
    }
}

// Set error reporting
if (($config['debug'] ?? false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($config['timezone'] ?? 'UTC');

// Set locale
setlocale(LC_ALL, ($config['locale'] ?? 'en') . '.UTF-8');

// Create required directories
$directories = [
    $config['session']['files'] ?? null,
    $config['upload']['path'] ?? null,
    $config['cache']['path'] ?? null,
    $config['log']['path'] ?? null,
];

foreach ($directories as $directory) {
    if ($directory && !file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Initialize database
try {
    $db = \App\Core\Database::getInstance();
} catch (\Exception $e) {
    if (($config['debug'] ?? false)) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Initialize router
$router = new \App\Core\Router($config);

// Set 404 handler
$router->notFound(function() use ($config) {
    header("HTTP/1.0 404 Not Found");
    if (($config['debug'] ?? false)) {
        echo "404 Not Found";
    } else {
        echo "An error occurred. Please try again later."; // Generic message for production
    }
});

// Load routes
require_once __DIR__ . '/routes.php';

// Return router instance and config
return ['router' => $router, 'config' => $config]; 