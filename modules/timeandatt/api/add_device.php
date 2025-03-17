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
if (!isset($data['device_id']) || !isset($data['device_name']) || !isset($data['ip_address'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$device_id = $data['device_id'];
$device_name = $data['device_name'];
$ip_address = $data['ip_address'];
$mac_address = $data['mac_address'] ?? '';
$username = $data['username'] ?? 'admin';
$password = $data['password'] ?? '12345';

try {
    // Check if the devices table exists, create it if not
    $check_table_query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'devices'
    )";
    
    $table_result = pg_query($conn, $check_table_query);
    $table_exists = pg_fetch_result($table_result, 0, 0);
    
    if ($table_exists === 'f') {
        // Table doesn't exist, create it
        $create_table_query = "
            CREATE TABLE devices (
                id SERIAL PRIMARY KEY,
                device_id VARCHAR(255) NOT NULL,
                serial_number VARCHAR(255) NOT NULL,
                device_name VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                mac_address VARCHAR(255),
                username VARCHAR(255) DEFAULT 'admin',
                password VARCHAR(255) DEFAULT '12345',
                firmware_version VARCHAR(100),
                model VARCHAR(100),
                status VARCHAR(50) DEFAULT 'offline',
                last_online TIMESTAMP,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP,
                deleted_at TIMESTAMP,
                CONSTRAINT unique_device_id UNIQUE (device_id),
                CONSTRAINT unique_serial_number UNIQUE (serial_number)
            );
            
            CREATE INDEX idx_devices_device_id ON devices(device_id);
            CREATE INDEX idx_devices_serial_number ON devices(serial_number);
            CREATE INDEX idx_devices_status ON devices(status);
            
            CREATE TABLE IF NOT EXISTS device_actions (
                id SERIAL PRIMARY KEY,
                device_id VARCHAR(255) NOT NULL,
                action_type VARCHAR(50) NOT NULL,
                status VARCHAR(50) NOT NULL,
                details JSONB,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE INDEX idx_device_actions_device_id ON device_actions(device_id);
            CREATE INDEX idx_device_actions_created_at ON device_actions(created_at);
        ";
        
        $create_result = pg_query($conn, $create_table_query);
        
        if (!$create_result) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create devices table: ' . pg_last_error($conn)
            ]);
            exit;
        }
    }
    
    // Check if device already exists
    $check_device_query = "SELECT id FROM devices WHERE device_id = $1 OR serial_number = $1";
    $check_result = pg_query_params($conn, $check_device_query, [$device_id]);
    
    if (pg_num_rows($check_result) > 0) {
        // Device exists, update it
        $update_query = "
            UPDATE devices SET 
                device_name = $1,
                ip_address = $2,
                mac_address = $3,
                username = $4,
                password = $5,
                status = 'online',
                last_online = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            WHERE device_id = $6 OR serial_number = $6
            RETURNING id
        ";
        
        $result = pg_query_params(
            $conn,
            $update_query,
            [$device_name, $ip_address, $mac_address, $username, $password, $device_id]
        );
        
        if (!$result) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update device: ' . pg_last_error($conn)
            ]);
            exit;
        }
        
        $device_record = pg_fetch_assoc($result);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Device updated successfully',
            'device_id' => $device_record['id']
        ]);
    } else {
        // Device doesn't exist, insert it
        $insert_query = "
            INSERT INTO devices (
                device_id,
                serial_number,
                device_name,
                ip_address,
                mac_address,
                username,
                password,
                status,
                last_online,
                created_at
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            RETURNING id
        ";
        
        $result = pg_query_params(
            $conn,
            $insert_query,
            [$device_id, $device_id, $device_name, $ip_address, $mac_address, $username, $password, 'online']
        );
        
        if (!$result) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add device: ' . pg_last_error($conn)
            ]);
            exit;
        }
        
        $device_record = pg_fetch_assoc($result);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Device added successfully',
            'device_id' => $device_record['id']
        ]);
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