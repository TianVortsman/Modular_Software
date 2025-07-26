<?php
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;
use function App\modules\invoice\controllers\list_clients;
use function App\modules\invoice\controllers\get_client_details;
use function App\modules\invoice\controllers\create_client;
use function App\modules\invoice\controllers\update_client;
use function App\modules\invoice\controllers\delete_client;
require_once __DIR__ . '/../controllers/ClientController.php';

function clean_numeric($value) {
    return is_null($value) ? 0 : floatval(preg_replace('/[^\d.\-]/', '', $value));
}

function clean_int($value) {
    return (is_numeric($value) && $value !== '') ? intval($value) : null;
}

try {
    if (!isset($_SESSION['account_number'])) {
        sendApiErrorResponse('User session not found', null, 'Client API Authentication', 'SESSION_NOT_FOUND', 401);
    }
    
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    global $conn;
    
    if (!$conn) {
        sendApiErrorResponse('Database connection failed', null, 'Client API Database Connection', 'DB_CONN_ERROR');
    }
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // Route actions
    switch ($action) {
        case 'search':
        case 'list_clients':
            // GET: list clients with options from query params
            $options = [
                'search'   => $_GET['search'] ?? null,
                'type'     => $_GET['type'] ?? null,
                'page'     => isset($_GET['page']) ? (int)$_GET['page'] : 1,
                'limit'    => isset($_GET['limit']) ? (int)$_GET['limit'] : 10,
                'sort_by'  => $_GET['sort_by'] ?? 'client_id',
                'sort_dir' => $_GET['sort_dir'] ?? 'desc',
            ];
            $result = list_clients($options);
            echo json_encode($result);
            break;

        case 'get_client_details':
        case 'get_client':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for get_client action', $_GET, 'Client API Method Validation', 'INVALID_METHOD', 405);
            }
            
            if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
                sendApiErrorResponse('Missing or invalid client_id parameter', $_GET, 'Client API Parameter Validation', 'CLIENT_ID_REQUIRED', 400);
            }
            
            $client_id = (int)$_GET['client_id'];
            $result = get_client_details($client_id);
            echo json_encode($result);
            break;

        case 'create_client':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for create_client action', $_POST, 'Client API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid client data', ['raw_input' => $rawData], 'Client API Data Validation', 'CLIENT_DATA_REQUIRED', 400);
            }
            
            $result = create_client($data);
            echo json_encode($result);
            break;

        case 'update_client':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for update_client action', $_POST, 'Client API Method Validation', 'INVALID_METHOD', 405);
            }
            
            if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
                sendApiErrorResponse('Missing or invalid client_id parameter', $_GET, 'Client API Parameter Validation', 'CLIENT_ID_REQUIRED', 400);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid client data', ['raw_input' => $rawData], 'Client API Data Validation', 'CLIENT_DATA_REQUIRED', 400);
            }
            
            $client_id = (int)$_GET['client_id'];
            $data['client_id'] = $client_id;
            $result = update_client($data);
            echo json_encode($result);
            break;

        case 'delete_client':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for delete_client action', $_POST, 'Client API Method Validation', 'INVALID_METHOD', 405);
            }
            
            if (!isset($_GET['client_id']) || !is_numeric($_GET['client_id'])) {
                sendApiErrorResponse('Missing or invalid client_id parameter', $_GET, 'Client API Parameter Validation', 'CLIENT_ID_REQUIRED', 400);
            }
            
            $client_id = (int)$_GET['client_id'];
            $deleted_by = $_SESSION['user_id'] ?? null;
            
            if (!$deleted_by) {
                sendApiErrorResponse('User session invalid - cannot determine deleted_by', $_SESSION, 'Client API Session Validation', 'INVALID_SESSION', 401);
            }
            
            $result = delete_client($client_id, $deleted_by);
            echo json_encode($result);
            break;

        default:
            sendApiErrorResponse("Invalid action: $action", [
                'action' => $action, 
                'available_actions' => ['search', 'list_clients', 'get_client_details', 'get_client', 'create_client', 'update_client', 'delete_client']
            ], 'Client API Action Validation', 'INVALID_ACTION', 400);
    }
    
} catch (PDOException $e) {
    $formData = [
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown',
        'session_data' => $_SESSION ?? [],
    ];
    sendApiErrorResponse('Database error: ' . $e->getMessage(), $formData, 'Client API Database Error');
    
} catch (Exception $e) {
    $formData = [
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown',
        'session_data' => $_SESSION ?? [],
    ];
    sendApiErrorResponse('Unexpected error: ' . $e->getMessage(), $formData, 'Client API General Error');
}
