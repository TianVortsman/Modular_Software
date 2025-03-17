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
    // Get device info from database
    $query = "SELECT * FROM devices WHERE id = $1 AND deleted_at IS NULL";
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
        // Return a placeholder offline image
        header('Content-Type: image/png');
        readfile(__DIR__ . '/../assets/offline.png');
        exit;
    }
    
    // Get device IP, username and password
    $ip_address = $device['ip_address'];
    $username = $device['username'] ?? 'admin';
    $password = $device['password'] ?? '12345';
    
    // Create Hikvision snapshot URL
    $snapshot_url = "http://$ip_address/ISAPI/Streaming/channels/1/picture";
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $snapshot_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    
    // Execute cURL session
    $image_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    // Close cURL session
    curl_close($ch);
    
    // Check if request was successful
    if ($http_code !== 200) {
        // Return a placeholder error image
        header('Content-Type: image/png');
        readfile(__DIR__ . '/../assets/error.png');
        exit;
    }
    
    // Update device last online timestamp
    $update_query = "UPDATE devices SET last_online = CURRENT_TIMESTAMP, status = 'online' WHERE id = $1";
    pg_query_params($conn, $update_query, [$device_id]);
    
    // Return the image
    header('Content-Type: ' . ($content_type ?: 'image/jpeg'));
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo $image_data;
    
} catch (Exception $e) {
    // Return a placeholder error image
    header('Content-Type: image/png');
    readfile(__DIR__ . '/../assets/error.png');
}
?> 