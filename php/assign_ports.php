<?php
require_once('main-db.php');
require_once('port_management.php');

header('Content-Type: application/json');

try {
    $results = assignPortsToExistingCustomers($conn);
    
    echo json_encode([
        'success' => true,
        'message' => count($results['success']) . ' customers assigned ports successfully',
        'failed_count' => count($results['failed']),
        'details' => $results
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error assigning ports: ' . $e->getMessage()
    ]);
} 