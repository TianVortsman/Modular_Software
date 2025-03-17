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

try {
    // Check if the devices table exists
    $check_table_query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'devices'
    )";
    
    $table_result = pg_query($conn, $check_table_query);
    $table_exists = pg_fetch_result($table_result, 0, 0);
    
    if ($table_exists === 'f') {
        // Table doesn't exist, return empty list
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'devices' => [],
            'message' => 'Devices table not found in database'
        ]);
        exit;
    }
    
    // Get all devices from the database
    $devices_query = "SELECT * FROM devices WHERE deleted_at IS NULL ORDER BY CASE WHEN status = 'online' THEN 0 ELSE 1 END, last_online DESC NULLS LAST";
    $devices_result = pg_query($conn, $devices_query);
    
    if (!$devices_result) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch devices: ' . pg_last_error($conn)
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