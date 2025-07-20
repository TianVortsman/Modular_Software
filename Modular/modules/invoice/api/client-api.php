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

header('Content-Type: application/json');

function clean_numeric($value) {
    return is_null($value) ? 0 : floatval(preg_replace('/[^\d.\-]/', '', $value));
}

function clean_int($value) {
    return (is_numeric($value) && $value !== '') ? intval($value) : null;
}

try {
    if (!isset($_SESSION['account_number'])) {
        throw new Exception('User session not found');
    }
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    global $conn;
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // Route actions
    switch ($action) {
        case 'list_clients':
            // GET: list clients with options from query params
            $options = [
                'search'   => $_GET['search'] ?? null,
                'type'     => $_GET['type'] ?? null,
                'page'     => isset($_GET['page']) ? (int)$_GET['page'] : 1,
                'limit'    => isset($_GET['limit']) ? (int)$_GET['limit'] : 10,
                'sort_by'  => $_GET['sort_by'] ?? null,
                'sort_dir' => $_GET['sort_dir'] ?? null
            ];
            $result = list_clients($options);
            echo json_encode($result);
            exit;
        case 'get_client_details':
            // GET: get details for a single client
            $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
            if (!$client_id) {
                echo json_encode(['success' => false, 'message' => 'Missing client_id', 'data' => null, 'error_code' => 'CLIENT_ID_REQUIRED']);
                exit;
            }
            $result = get_client_details($client_id);
            echo json_encode($result);
            exit;
        case 'create_client':
            // POST: create a new client
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method', 'data' => null, 'error_code' => 'INVALID_METHOD']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Missing client data', 'data' => null, 'error_code' => 'CLIENT_DATA_REQUIRED']);
                exit;
            }
            $result = create_client($data);
            if (!$result['success'] && !empty($result['error'])) {
                require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
                $aiMessage = getFriendlyMessageFromAI($result['error']);
                if ($aiMessage) $result['error'] = $aiMessage;
            }
            echo json_encode($result);
            exit;
        case 'update_client':
            // POST: update an existing client
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method', 'data' => null, 'error_code' => 'INVALID_METHOD']);
                exit;
            }
            $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
            if (!$client_id) {
                echo json_encode(['success' => false, 'message' => 'Missing client_id', 'data' => null, 'error_code' => 'CLIENT_ID_REQUIRED']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Missing client data', 'data' => null, 'error_code' => 'CLIENT_DATA_REQUIRED']);
                exit;
            }
            $result = update_client($client_id, $data);
            if (!$result['success'] && !empty($result['error'])) {
                require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
                $aiMessage = getFriendlyMessageFromAI($result['error']);
                if ($aiMessage) $result['error'] = $aiMessage;
            }
            echo json_encode($result);
            exit;
        case 'delete_client':
            // POST: delete a client
            if ($method !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method', 'data' => null, 'error_code' => 'INVALID_METHOD']);
                exit;
            }
            $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
            $deleted_by = isset($_GET['deleted_by']) ? (int)$_GET['deleted_by'] : null;
            if (!$client_id || !$deleted_by) {
                echo json_encode(['success' => false, 'message' => 'Missing client_id or deleted_by', 'data' => null, 'error_code' => 'CLIENT_ID_REQUIRED']);
                exit;
            }
            $result = delete_client($client_id, $deleted_by);
            if (!$result['success'] && !empty($result['error'])) {
                require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
                $aiMessage = getFriendlyMessageFromAI($result['error']);
                if ($aiMessage) $result['error'] = $aiMessage;
            }
            echo json_encode($result);
            exit;
        case 'search':
            // Use list_clients to search the real DB
            $options = [
                'search' => $_GET['query'] ?? '',
                'limit' => 10,
                'page' => 1
            ];
            $result = list_clients($options);
            // Return only the data array for dropdown compatibility
            echo json_encode($result['data'] ?? []);
            exit;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action', 'data' => null, 'error_code' => 'INVALID_ACTION']);
            exit;
    }
} catch (Exception $e) {
    require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
    $aiMessage = getFriendlyMessageFromAI($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $aiMessage ?: 'Please contact Modular Software Support.',
        'error' => $e->getMessage(),
        'data' => null,
        'error_code' => 'API_ERROR'
    ]);
    exit;
}
