<?php
// Include autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../Utils/response.php';

use App\Core\Database\ClientDatabase;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session to get account number
session_start();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Check for test endpoint first (no authentication required)
if ($method === 'GET' && ($_GET['action'] ?? '') === 'test') {
    testWebSocketConnection();
    exit;
}

// Get account number from session (this is how your system works)
$accountNumber = $_SESSION['account_number'] ?? null;

// Validate account number
if (!$accountNumber) {
    errorResponse('No account number found in session. Please log in to a customer account.', 401);
    exit;
}

// Log for debugging
error_log("WhatsApp API called with account number: " . $accountNumber);

// Use account number as customer ID for the WebSocket system
$customerId = $accountNumber;

try {
    // Get customer database connection using account number
    $customerDb = ClientDatabase::getInstance($accountNumber, $_SESSION['user_name'] ?? 'Guest');
    $pdo = $customerDb->connect();
    
    switch ($method) {
        case 'GET':
            handleGetRequest($pdo, $customerId);
            break;
        case 'POST':
            handlePostRequest($pdo, $customerId);
            break;
        case 'DELETE':
            handleDeleteRequest($pdo, $customerId);
            break;
        default:
            errorResponse('Method not allowed', 405);
            break;
    }
    
} catch (Exception $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}

function handleGetRequest($pdo, $customerId) {
    $action = $_GET['action'] ?? 'status';
    
    switch ($action) {
        case 'test':
            // Test endpoint doesn't require authentication
            testWebSocketConnection();
            break;
        case 'qr':
            getQRCode($customerId);
            break;
        case 'status':
            getSessionStatus($pdo, $customerId);
            break;
        default:
            errorResponse('Invalid action', 400);
    }
}

function handlePostRequest($pdo, $customerId) {
    $action = $_POST['action'] ?? 'initialize';
    
    switch ($action) {
        case 'initialize':
            initializeSession($customerId);
            break;
        default:
            errorResponse('Invalid action', 400);
    }
}

function handleDeleteRequest($pdo, $customerId) {
    $action = $_GET['action'] ?? 'logout';
    
    switch ($action) {
        case 'logout':
            logoutSession($customerId);
            break;
        default:
            errorResponse('Invalid action', 400);
    }
}

function getQRCode($customerId) {
    $websocketUrl = getWebSocketServerUrl();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/whatsapp/qr/{$customerId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Add authentication parameters
    $params = http_build_query([
        'user_id' => $_GET['user_id'] ?? 1,
        'session_id' => $_GET['session_id'] ?? session_id()
    ]);
    
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/whatsapp/qr/{$customerId}?" . $params);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        errorResponse('Curl error: ' . $curlError, 500);
        return;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        successResponse('QR code retrieved successfully', $data);
    } else {
        $error = json_decode($response, true);
        errorResponse($error['error'] ?? 'Failed to get QR code', $httpCode);
    }
}

function getSessionStatus($pdo, $customerId) {
    $websocketUrl = getWebSocketServerUrl();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/whatsapp/status/{$customerId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Add authentication parameters
    $params = http_build_query([
        'user_id' => $_GET['user_id'] ?? 1,
        'session_id' => $_GET['session_id'] ?? session_id()
    ]);
    
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/whatsapp/status/{$customerId}?" . $params);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        errorResponse('Curl error: ' . $curlError, 500);
        return;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        successResponse('Session status retrieved successfully', $data);
    } else {
        $error = json_decode($response, true);
        errorResponse($error['error'] ?? 'Failed to get session status', $httpCode);
    }
}

function initializeSession($customerId) {
    $websocketUrl = getWebSocketServerUrl();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Add authentication parameters
    $params = http_build_query([
        'user_id' => $_POST['user_id'] ?? 1,
        'session_id' => $_POST['session_id'] ?? session_id()
    ]);
    
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/whatsapp/qr/{$customerId}?" . $params);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        errorResponse('Curl error: ' . $curlError, 500);
        return;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        successResponse('WhatsApp session initialized successfully', $data);
    } else {
        $error = json_decode($response, true);
        errorResponse($error['error'] ?? 'Failed to initialize session', $httpCode);
    }
}

function logoutSession($customerId) {
    $websocketUrl = getWebSocketServerUrl();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/whatsapp/logout/{$customerId}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 second connection timeout
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'user_id' => $_GET['user_id'] ?? 1,
        'session_id' => $_GET['session_id'] ?? session_id()
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        errorResponse('Curl error: ' . $curlError, 500);
        return;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        successResponse('WhatsApp session logged out successfully', $data);
    } else {
        $error = json_decode($response, true);
        errorResponse($error['error'] ?? 'Failed to logout session', $httpCode);
    }
}

function testWebSocketConnection() {
    $websocketUrl = getWebSocketServerUrl();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $websocketUrl . "/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // 3 second connection timeout
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        errorResponse('WebSocket server connection failed: ' . $curlError, 500);
        return;
    }
    
    if ($httpCode === 200) {
        successResponse('WebSocket server is reachable', [
            'status' => 'connected',
            'response' => $response
        ]);
    } else {
        errorResponse('WebSocket server returned HTTP ' . $httpCode, $httpCode);
    }
}

function getWebSocketServerUrl() {
    // Use the internal Docker network name since both containers are on the same network
    return $_ENV['WEBSOCKET_SERVER_URL'] ?? 'http://websocket-server:3001';
}
?> 