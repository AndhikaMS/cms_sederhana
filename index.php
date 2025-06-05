<?php
// Load bootstrap file
$router = require_once __DIR__ . '/app/bootstrap.php';

// Dispatch route
try {
    $router->dispatch();
} catch (\Exception $e) {
    // Get configuration
    $config = require_once __DIR__ . '/app/config/app.php';
    
    // Show error if in debug mode
    if ($config['debug']) {
        echo "<h1>Error</h1>";
        echo "<p>{$e->getMessage()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    } else {
        // Log error
        $log_file = $config['log']['path'] . '/error.log';
        $message = date('Y-m-d H:i:s') . " - {$e->getMessage()}\n";
        $message .= "File: {$e->getFile()}\n";
        $message .= "Line: {$e->getLine()}\n";
        $message .= "Trace:\n{$e->getTraceAsString()}\n\n";
        file_put_contents($log_file, $message, FILE_APPEND);
        
        // Show generic error
        header("HTTP/1.0 500 Internal Server Error");
        echo "An error occurred. Please try again later.";
    }
} 