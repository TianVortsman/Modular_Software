<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    
    // Since we don't have the modules table yet, return placeholder data
    $placeholderModules = [
        'core_modules' => [
            ['name' => 'User Management', 'status' => 'active'],
            ['name' => 'Account Settings', 'status' => 'active'],
            ['name' => 'Basic Reports', 'status' => 'active']
        ],
        'additional_features' => [
            ['name' => 'Advanced Analytics', 'status' => 'inactive'],
            ['name' => 'Custom Reports', 'status' => 'inactive'],
            ['name' => 'API Access', 'status' => 'inactive']
        ],
        'mobile_features' => [
            ['name' => 'Mobile App', 'status' => 'inactive'],
            ['name' => 'Push Notifications', 'status' => 'inactive'],
            ['name' => 'Offline Access', 'status' => 'inactive']
        ]
    ];

    echo json_encode([
        'success' => true,
        'core_modules' => $placeholderModules['core_modules'],
        'additional_features' => $placeholderModules['additional_features'],
        'mobile_features' => $placeholderModules['mobile_features']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 