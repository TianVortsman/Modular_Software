<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
session_start();

// Verify CSRF token if not a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (!isset($_SESSION['csrf_token']) || !isset($_SERVER['HTTP_X_CSRF_TOKEN']) || 
        $_SESSION['csrf_token'] !== $_SERVER['HTTP_X_CSRF_TOKEN']) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

try {
    // Check if port parameter is provided
    if (!isset($_GET['port']) || empty($_GET['port'])) {
        throw new Exception("Port parameter is required");
    }
    
    $port = intval($_GET['port']);
    
    // Validate port number
    if ($port < 1024 || $port > 65535) {
        throw new Exception("Invalid port number. Port must be between 1024 and 65535.");
    }
    
    // Check if port is in use
    $isRunning = false;
    
    // Try to check port status using various methods
    
    // Method 1: Try to check via local socket connection
    $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
    if (is_resource($connection)) {
        fclose($connection);
        $isRunning = true;
    } 
    
    // Method 2: Check via system command if Method 1 fails and we're on Linux
    if (!$isRunning && stripos(PHP_OS, 'linux') !== false) {
        $command = "netstat -tuln | grep ':$port '";
        exec($command, $output, $return);
        $isRunning = count($output) > 0;
    }
    
    // Method 3: Check via system command if on Windows
    if (!$isRunning && stripos(PHP_OS, 'win') !== false) {
        $command = "netstat -an | findstr :$port";
        exec($command, $output, $return);
        $isRunning = count($output) > 0;
    }
    
    // Return success response with the port's running status
    echo json_encode([
        'success' => true,
        'port' => $port,
        'is_running' => $isRunning,
        'message' => $isRunning ? "Server is running on port $port" : "No server running on port $port"
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("check-clock-server-status.php error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 