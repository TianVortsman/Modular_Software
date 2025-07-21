<?php
// Start session if not already started
session_start();

// Include autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Check for CSRF token
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Invalid CSRF token'
    ]);
    exit;
}

// Check if technician is logged in - support both new and old session formats
$isTechnicianLoggedIn = 
    // New format
    (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'technician') ||
    // Old format
    (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true);

if (!$isTechnicianLoggedIn) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'You must be logged in as a technician to access this feature'
    ]);
    exit;
}

// Create technician auth manager
$techAuthManager = new \App\Core\Auth\TechnicianAuthManager();

// Check if account number is provided
if (!isset($_POST['account_number']) || empty($_POST['account_number'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Account number is required'
    ]);
    exit;
}

// Get the technician ID and account number
$technicianId = $_SESSION['tech_id']; // Support both formats
$accountNumber = $_POST['account_number'];

// Attempt to login
$result = $techAuthManager->loginToCustomerAccount($technicianId, $accountNumber);

// Return the result
header('Content-Type: application/json');
if (isset($result['success']) && !$result['success'] && !empty($result['error'])) {
    require_once __DIR__ . '/../Utils/errorHandler.php';
    $aiMessage = getFriendlyMessageFromAI($result['error']);
    if ($aiMessage) $result['error'] = $aiMessage;
}
echo json_encode($result);
exit; 