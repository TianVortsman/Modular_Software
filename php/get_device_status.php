<?php
// Include database connection
include('db.php');

// Set headers for JSON response
header('Content-Type: application/json');

// Check if account parameter is provided
if (!isset($_GET['account'])) {
    echo json_encode(['error' => 'Account parameter is required']);
    exit;
}

$account = $_GET['account'];

try {
    // Query to get the clock devices for the account
    $query = "SELECT d.device_id, d.device_name, d.ip_address, d.last_heartbeat, d.status 
              FROM clock_devices d 
              JOIN customers c ON d.account_id = c.id 
              WHERE c.account_number = $1";
    
    $stmt = pg_prepare($conn, "get_devices", $query);
    $result = pg_execute($conn, "get_devices", array($account));
    
    $devices = [];
    
    if (pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            // Determine device status based on last heartbeat
            $status = $row['status'];
            
            // If no explicit status, determine based on heartbeat
            if (!$status) {
                if ($row['last_heartbeat']) {
                    $lastHeartbeat = strtotime($row['last_heartbeat']);
                    $now = time();
                    $diff = $now - $lastHeartbeat;
                    
                    // If last heartbeat was within 5 minutes, consider online
                    if ($diff < 300) {
                        $status = 'online';
                    } else {
                        $status = 'offline';
                    }
                } else {
                    $status = 'unknown';
                }
            }
            
            $devices[] = [
                'device_id' => $row['device_id'],
                'name' => $row['device_name'],
                'ip_address' => $row['ip_address'],
                'last_check' => $row['last_heartbeat'],
                'status' => $status
            ];
        }
        
        echo json_encode(['devices' => $devices]);
    } else {
        // If no devices found, check if we have any recent clock events
        $query = "SELECT DISTINCT device_id, MAX(created_at) as last_event 
                  FROM attendance_records 
                  WHERE created_at > NOW() - INTERVAL '24 HOURS' 
                  GROUP BY device_id";
        
        $stmt = pg_prepare($conn, "get_recent_events", $query);
        $result = pg_execute($conn, "get_recent_events", array());
        
        if (pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $devices[] = [
                    'device_id' => $row['device_id'],
                    'name' => 'Unknown Device',
                    'ip_address' => null,
                    'last_check' => $row['last_event'],
                    'status' => 'online'
                ];
            }
            
            echo json_encode(['devices' => $devices]);
        } else {
            echo json_encode(['devices' => []]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 