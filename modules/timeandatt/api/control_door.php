<?php
session_start();

// Check if user is logged in and has account number
if (!isset($_SESSION['account_number'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

$account_number = $_SESSION['account_number'];

// Include database connection
require_once '../../../php/db.php';

// Get request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check required fields
if (!isset($data['deviceId']) || !isset($data['action'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$device_id = $data['deviceId'];
$action = $data['action'];
$duration = $data['duration'] ?? null;

// Validate action
$valid_actions = ['unlock', 'lock', 'hold'];
if (!in_array($action, $valid_actions)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

try {
    // Get device info from database
    $query = "SELECT * FROM devices WHERE device_id = $1 AND deleted_at IS NULL";
    $result = pg_query_params($conn, $query, [$device_id]);
    
    if (!$result || pg_num_rows($result) === 0) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Device not found'
        ]);
        exit;
    }
    
    $device = pg_fetch_assoc($result);
    
    // Check if device is online
    if ($device['status'] !== 'online') {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Device is offline'
        ]);
        exit;
    }
    
    // Get clock server port from main database to call the clock server API
    $db_host = 'localhost';
    $db_port = '5432';
    $db_user = 'Tian';
    $db_pass = 'Modul@rdev@2024';
    $db_name = 'modular_system';
    
    // Connect to main database
    $main_conn_string = "host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass";
    $main_conn = pg_connect($main_conn_string);
    
    if (!$main_conn) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to connect to main database'
        ]);
        exit;
    }
    
    // Get clock server port
    $port_query = "SELECT clock_server_port FROM customers WHERE account_number = $1";
    $port_result = pg_query_params($main_conn, $port_query, [$account_number]);
    
    if (!$port_result || pg_num_rows($port_result) === 0) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Clock server port not found'
        ]);
        exit;
    }
    
    $port = pg_fetch_result($port_result, 0, 0);
    
    // Prepare the request data
    $request_data = [
        'deviceId' => $device_id,
        'action' => $action
    ];
    
    if ($action === 'hold' && $duration) {
        $request_data['duration'] = $duration;
    }
    
    // Call the clock server API
    $clock_server_url = "http://localhost:$port/door/control";
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $clock_server_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 seconds timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Execute cURL session
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);
    
    // Log the door control action
    $action_status = ($http_code >= 200 && $http_code < 300) ? 'success' : 'failed';
    $details = [
        'action' => $action,
        'http_code' => $http_code
    ];
    
    if ($duration) {
        $details['duration'] = $duration;
    }
    
    $log_query = "INSERT INTO device_actions (device_id, action_type, status, details, created_at) 
                 VALUES ($1, $2, $3, $4, CURRENT_TIMESTAMP)";
    pg_query_params($conn, $log_query, [
        $device_id,
        $action,
        $action_status,
        json_encode($details)
    ]);
    
    // Check if request was successful
    if ($http_code >= 200 && $http_code < 300) {
        // Return success response
        header('Content-Type: application/json');
        echo $response; // Pass through the response from the clock server
    } else {
        // Return error response
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo $response; // Pass through the error from the clock server
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 