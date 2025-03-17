<?php
// Start session and check authentication
session_start();

// Check if technician is logged in
if (!isset($_SESSION['tech_logged_in']) || $_SESSION['tech_logged_in'] !== true) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include main database connection
require_once '../../php/main-db.php';

// Get account number from query parameter
$account_number = $_GET['account_number'] ?? null;

if (!$account_number) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Account number is required'
    ]);
    exit;
}

try {
    // First, check if the account number exists in the main database
    $query = "SELECT * FROM customers WHERE account_number = $1";
    $result = pg_query_params($conn, $query, [$account_number]);
    
    if (!$result || pg_num_rows($result) === 0) {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
        exit;
    }
    
    // Connect to customer database
    $customer_conn_string = "host=$db_host port=$db_port dbname=$account_number user=$db_user password=$db_pass";
    $customer_conn = pg_connect($customer_conn_string);
    
    if (!$customer_conn) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to connect to customer database'
        ]);
        exit;
    }
    
    // Check if the devices table exists
    $check_table_query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'devices'
    )";
    
    $table_result = pg_query($customer_conn, $check_table_query);
    $table_exists = pg_fetch_result($table_result, 0, 0);
    
    if ($table_exists === 'f') {
        // Table doesn't exist, return empty list
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'devices' => [],
            'message' => 'Devices table not found in customer database'
        ]);
        exit;
    }
    
    // Get all devices from the customer database
    $devices_query = "SELECT * FROM devices WHERE deleted_at IS NULL ORDER BY last_online DESC";
    $devices_result = pg_query($customer_conn, $devices_query);
    
    if (!$devices_result) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch devices: ' . pg_last_error($customer_conn)
        ]);
        exit;
    }
    
    // Prepare devices array
    $devices = [];
    
    while ($device = pg_fetch_assoc($devices_result)) {
        // Don't include sensitive data like passwords
        unset($device['password']);
        
        // Convert timestamp strings to proper format
        if (!empty($device['last_online'])) {
            $device['last_online'] = date('Y-m-d H:i:s', strtotime($device['last_online']));
        }
        if (!empty($device['created_at'])) {
            $device['created_at'] = date('Y-m-d H:i:s', strtotime($device['created_at']));
        }
        if (!empty($device['updated_at'])) {
            $device['updated_at'] = date('Y-m-d H:i:s', strtotime($device['updated_at']));
        }
        
        $devices[] = $device;
    }
    
    // Return devices list
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'devices' => $devices
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 