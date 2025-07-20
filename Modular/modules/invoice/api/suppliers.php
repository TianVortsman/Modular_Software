<?php
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/../../../src/Config/Database.php';
require_once __DIR__ . '/../controllers/SupplierController.php';
require_once __DIR__ . '/../../../src/Helpers/helpers.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

// Use the same DB connection logic as client-api.php
if (!isset($_SESSION['account_number'])) {
    echo json_encode(['success' => false, 'message' => 'User session not found', 'data' => null, 'error_code' => 'SESSION_NOT_FOUND']);
    exit;
}
// Use the same DB connection logic as client-api.php
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;

$db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
$conn = $db->connect();
global $conn;
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'data' => null, 'error_code' => 'DB_CONN_ERROR']);
    exit;
}
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$user_id = $_SESSION['user_id'] ?? null;

try {
    switch ($action) {
        case 'list':
            $result = \App\modules\product\controllers\list_suppliers();
            break;
        case 'get':
            $supplier_id = (int)($_GET['id'] ?? 0);
            $result = \App\modules\product\controllers\get_supplier($supplier_id);
            break;
        case 'add':
            if ($method !== 'POST') throw new Exception('POST required');
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $result = \App\modules\product\controllers\add_supplier($data, $user_id);
            break;
        case 'update':
            if ($method !== 'PUT' && $method !== 'POST') throw new Exception('PUT or POST required');
            $supplier_id = (int)($_GET['id'] ?? ($_POST['supplier_id'] ?? 0));
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $result = \App\modules\product\controllers\update_supplier($supplier_id, $data, $user_id);
            break;
        case 'delete':
            if ($method !== 'DELETE' && $method !== 'POST') throw new Exception('DELETE or POST required');
            $supplier_id = (int)($_GET['id'] ?? ($_POST['supplier_id'] ?? 0));
            $result = \App\modules\product\controllers\delete_supplier($supplier_id, $user_id);
            break;
        case 'link_product':
            if ($method !== 'POST') throw new Exception('POST required');
            $product_id = (int)($_POST['product_id'] ?? 0);
            $supplier_id = (int)($_POST['supplier_id'] ?? 0);
            $result = \App\modules\product\controllers\link_product_to_supplier($product_id, $supplier_id, $user_id);
            break;
        case 'unlink_product':
            if ($method !== 'POST') throw new Exception('POST required');
            $product_id = (int)($_POST['product_id'] ?? 0);
            $supplier_id = (int)($_POST['supplier_id'] ?? 0);
            $result = \App\modules\product\controllers\unlink_product_from_supplier($product_id, $supplier_id, $user_id);
            break;
        case 'get_product_suppliers':
            $product_id = (int)($_GET['product_id'] ?? 0);
            $result = \App\modules\product\controllers\get_product_suppliers($product_id);
            break;
        case 'add_price_history':
            if ($method !== 'POST') throw new Exception('POST required');
            $product_id = (int)($_POST['product_id'] ?? 0);
            $supplier_id = (int)($_POST['supplier_id'] ?? 0);
            $purchase_price = (float)($_POST['purchase_price'] ?? 0);
            $result = \App\modules\product\controllers\add_supplier_price_history($product_id, $supplier_id, $purchase_price, $user_id);
            break;
        default:
            throw new Exception('Unknown or missing action');
    }
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null,
        'error_code' => 'SUPPLIER_API_ERROR'
    ]);
} 