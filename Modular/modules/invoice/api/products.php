<?php
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;
require_once __DIR__ . '/../controllers/ProductController.php';

try {
    if (!isset($_SESSION['account_number'])) {
        sendApiErrorResponse('User session not found', null, 'Product API Authentication', 'SESSION_NOT_FOUND', 401);
    }
    
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    global $conn;
    
    if (!$conn) {
        sendApiErrorResponse('Database connection failed', null, 'Product API Database Connection', 'DB_CONN_ERROR');
    }
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($action) {
        case 'list_products':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for list_products action', $_GET, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $options = [
                'search'   => $_GET['search'] ?? null,
                'category' => $_GET['category'] ?? null,
                'type'     => $_GET['type'] ?? null,
                'page'     => isset($_GET['page']) ? (int)$_GET['page'] : 1,
                'limit'    => isset($_GET['limit']) ? (int)$_GET['limit'] : 20,
                'sort_by'  => $_GET['sort_by'] ?? 'product_id',
                'sort_dir' => $_GET['sort_dir'] ?? 'desc',
            ];
            
            $result = \App\modules\invoice\controllers\list_products($options);
            echo json_encode($result);
            break;

        case 'get_product':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for get_product action', $_GET, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
                sendApiErrorResponse('Missing or invalid product_id parameter', $_GET, 'Product API Parameter Validation', 'PRODUCT_ID_REQUIRED', 400);
            }
            
            $product_id = (int)$_GET['product_id'];
            $result = \App\modules\invoice\controllers\get_product_details($product_id);
            echo json_encode($result);
            break;

        case 'create_product':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for create_product action', $_POST, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid product data', ['raw_input' => $rawData], 'Product API Data Validation', 'PRODUCT_DATA_REQUIRED', 400);
            }
            
            $result = \App\modules\invoice\controllers\create_product($data);
            echo json_encode($result);
            break;

        case 'update_product':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for update_product action', $_POST, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
                sendApiErrorResponse('Missing or invalid product_id parameter', $_GET, 'Product API Parameter Validation', 'PRODUCT_ID_REQUIRED', 400);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid product data', ['raw_input' => $rawData], 'Product API Data Validation', 'PRODUCT_DATA_REQUIRED', 400);
            }
            
            $product_id = (int)$_GET['product_id'];
            $data['product_id'] = $product_id;
            $result = \App\modules\invoice\controllers\update_product($data);
            echo json_encode($result);
            break;

        case 'delete_product':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for delete_product action', $_POST, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
                sendApiErrorResponse('Missing or invalid product_id parameter', $_GET, 'Product API Parameter Validation', 'PRODUCT_ID_REQUIRED', 400);
            }
            
            $product_id = (int)$_GET['product_id'];
            $deleted_by = $_SESSION['user_id'] ?? null;
            
            if (!$deleted_by) {
                sendApiErrorResponse('User session invalid - cannot determine deleted_by', $_SESSION, 'Product API Session Validation', 'INVALID_SESSION', 401);
            }
            
            $result = \App\modules\invoice\controllers\delete_product($product_id, $deleted_by);
            echo json_encode($result);
            break;

        case 'adjust_stock':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for adjust_stock action', $_POST, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid stock adjustment data', ['raw_input' => $rawData], 'Product API Data Validation', 'STOCK_DATA_REQUIRED', 400);
            }
            
            $result = \App\modules\invoice\controllers\adjust_product_stock($data);
            echo json_encode($result);
            break;

        case 'get_categories':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for get_categories action', $_GET, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $result = \App\modules\invoice\controllers\get_product_categories();
            echo json_encode($result);
            break;

        case 'create_category':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for create_category action', $_POST, 'Product API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid category data', ['raw_input' => $rawData], 'Product API Data Validation', 'CATEGORY_DATA_REQUIRED', 400);
            }
            
            $result = \App\modules\invoice\controllers\create_product_category($data);
            echo json_encode($result);
            break;

        default:
            sendApiErrorResponse("Invalid action: $action", [
                'action' => $action, 
                'available_actions' => [
                    'list_products', 'get_product', 'create_product', 'update_product', 'delete_product',
                    'adjust_stock', 'get_categories', 'create_category'
                ]
            ], 'Product API Action Validation', 'INVALID_ACTION', 400);
    }
    
} catch (PDOException $e) {
    $formData = [
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown',
        'session_data' => $_SESSION ?? [],
    ];
    sendApiErrorResponse('Database error: ' . $e->getMessage(), $formData, 'Product API Database Error');
    
} catch (Exception $e) {
    $formData = [
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown',
        'session_data' => $_SESSION ?? [],
    ];
    sendApiErrorResponse('Unexpected error: ' . $e->getMessage(), $formData, 'Product API General Error');
}
  