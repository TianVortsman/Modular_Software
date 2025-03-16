<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    
    // Since we don't have the devices table yet, return placeholder data
    $placeholderMachines = [
        'machines' => [
            [
                'id' => 1,
                'name' => 'Clock Machine 1',
                'type' => 'Biometric',
                'status' => 'active',
                'last_sync' => date('Y-m-d H:i:s'),
                'location' => 'Main Office'
            ],
            [
                'id' => 2,
                'name' => 'Clock Machine 2',
                'type' => 'RFID',
                'status' => 'inactive',
                'last_sync' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'location' => 'Branch Office'
            ]
        ],
        'statistics' => [
            'total_machines' => 2,
            'active_machines' => 1,
            'inactive_machines' => 1,
            'last_sync_time' => date('Y-m-d H:i:s')
        ]
    ];

    echo json_encode([
        'success' => true,
        'machines' => $placeholderMachines['machines'],
        'statistics' => $placeholderMachines['statistics']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 