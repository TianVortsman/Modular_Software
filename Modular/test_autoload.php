<?php
// Include autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test CustomerController
try {
    echo "Testing autoloading CustomerController...\n";
    $controller = new \App\Controllers\CustomerController();
    var_dump($controller);
    echo "Success! CustomerController loaded successfully.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 