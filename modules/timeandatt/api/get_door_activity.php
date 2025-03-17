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

// Get device ID from query parameter
$device_id = $_GET['device_id'] ?? null;

if (!$device_id) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Device ID is required'
    ]);
    exit;
}

try {
    // Check if the device_actions table exists
    $check_table_query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'device_actions'
    )";
    
    $table_result = pg_query($conn, $check_table_query);
    $table_exists = pg_fetch_result($table_result, 0, 0);
    
    if ($table_exists === 'f') {
        // Table doesn't exist, return empty list
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'activities' => [],
            'message' => 'Device actions table not found in database'
        ]);
        exit;
    }
    
    // Get recent door activities
    $query = "SELECT * FROM device_actions 
              WHERE device_id = $1 
              ORDER BY created_at DESC 
              LIMIT 20";
    
    $result = pg_query_params($conn, $query, [$device_id]);
    
    if (!$result) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch door activities: ' . pg_last_error($conn)
        ]);
        exit;
    }
    
    // Prepare activities array
    $activities = [];
    
    while ($activity = pg_fetch_assoc($result)) {
        // Convert details from JSON to array
        if (!empty($activity['details'])) {
            $activity['details'] = json_decode($activity['details'], true);
        }
        
        // Convert timestamp to proper format
        if (!empty($activity['created_at'])) {
            $activity['created_at'] = date('Y-m-d H:i:s', strtotime($activity['created_at']));
        }
        
        $activities[] = $activity;
    }
    
    // Return activities list
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'activities' => $activities
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