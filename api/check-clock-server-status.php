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

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['account_number'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get the port from query parameters
$port = isset($_GET['port']) ? intval($_GET['port']) : null;

if (!$port) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Port is required']);
    exit;
}

// Validate port range
if ($port < 1024 || $port > 65535) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid port number']);
    exit;
}

// Function to check if a port is in use (server is running)
function isPortInUse($port) {
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}

// Check if server is running on specified port
$isRunning = isPortInUse($port);

// Return result
echo json_encode([
    'success' => true, 
    'is_running' => $isRunning,
    'port' => $port
]);
exit;
?> 