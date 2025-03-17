<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    
    // Include database connection
    require_once '../php/db.php';
    
    // Get account number from request
    $account_number = $_GET['account_number'] ?? null;
    
    if (!$account_number) {
        throw new Exception('Account number is required');
    }
    
    // Check if the devices table exists
    $check_table_query = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'devices'
    )";
    
    $check_result = $conn->query($check_table_query);
    $table_exists = $check_result->fetchColumn();

    
    // Fetch actual devices from the devices table
    $query = "SELECT * FROM devices WHERE deleted_at IS NULL ORDER BY CASE WHEN status = 'online' THEN 0 ELSE 1 END, last_online DESC NULLS LAST";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format devices as machines for the techlogin interface
    $machines = [];
    $active_count = 0;
    $inactive_count = 0;
    $last_sync = null;
    
    foreach ($devices as $device) {
        // Map device status to machine status
        $status = ($device['status'] === 'online') ? 'active' : 'inactive';
        
        // Count active/inactive devices
        if ($status === 'active') {
            $active_count++;
        } else {
            $inactive_count++;
        }
        
        // Track the most recent sync time
        if ($device['last_online'] && (!$last_sync || strtotime($device['last_online']) > strtotime($last_sync))) {
            $last_sync = $device['last_online'];
        }
        
        // Map device fields to machine format
        $machines[] = [
            'id' => $device['id'],
            'name' => $device['device_name'],
            'type' => $device['model'] ?? 'Biometric', // Default to Biometric if no model specified
            'status' => $status,
            'last_sync' => $device['last_online'] ?? date('Y-m-d H:i:s'),
            'location' => 'Office', // Default location since we don't store this yet
            'ip_address' => $device['ip_address'],
            'device_id' => $device['device_id'],
            'serial_number' => $device['serial_number']
        ];
    }
    
    // Prepare statistics
    $statistics = [
        'total_machines' => count($machines),
        'active_machines' => $active_count,
        'inactive_machines' => $inactive_count,
        'last_sync_time' => $last_sync ?? date('Y-m-d H:i:s')
    ];

    echo json_encode([
        'success' => true,
        'machines' => $machines,
        'statistics' => $statistics
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 