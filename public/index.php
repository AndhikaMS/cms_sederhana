<?php

// Load application bootstrap
$app = require_once dirname(__DIR__) . '/app/bootstrap.php';

$router = $app['router'];
$config = $app['config'];

// Dispatch the request
$router->dispatch(); 