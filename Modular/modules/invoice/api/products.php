<?php
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/ImageController.php';

use App\Core\Database\ClientDatabase;
use function App\modules\product\controllers\handleImageUpload;

// Set CORS headers
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Debug session
error_log("API Session contents: " . print_r($_SESSION, true));

try {
    // Log the incoming request
    error_log("Received request - Method: " . $_SERVER['REQUEST_METHOD'] . ", Action: " . ($_GET['action'] ?? 'none'));

    // Parse PUT request data
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $input = file_get_contents("php://input");
        error_log("Raw PUT data: " . $input);
        
        // Parse the multipart form data
        $boundary = substr($input, 0, strpos($input, "\r\n"));
        if ($boundary) {
            $parts = array_slice(explode($boundary, $input), 1);
            foreach ($parts as $part) {
                if ($part == "--\r\n") continue;
                
                $part = ltrim($part, "\r\n");
                list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);
                
                $raw_headers = explode("\r\n", $raw_headers);
                $headers = array();
                foreach ($raw_headers as $header) {
                    $name = strtolower(trim(substr($header, 0, strpos($header, ':'))));
                    $value = trim(substr($header, strpos($header, ':') + 1));
                    $headers[$name] = $value;
                }
                
                if (isset($headers['content-disposition'])) {
                    $filename = null;
                    preg_match('/name="([^"]+)"/', $headers['content-disposition'], $matches);
                    $name = $matches[1];
                    
                    if (isset($headers['content-type'])) {
                        $_FILES[$name] = array(
                            'name' => $filename,
                            'type' => $headers['content-type'],
                            'tmp_name' => null,
                            'error' => 0,
                            'size' => strlen($body)
                        );
                    } else {
                        $_POST[$name] = substr($body, 0, strlen($body) - 2);
                    }
                }
            }
        }
        error_log("Parsed PUT data: " . print_r($_POST, true));
    }

    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    // Validate session
    if (!isset($_SESSION['account_number'])) {
        throw new Exception('User session not found');
    }

    // Get database connection
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();

    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize controller
    // Remove: $controller = new ProductController($conn);

    // Get and validate action
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    error_log("Processing action: $action with method: $method");

    // Handle the request based on action and method
    switch ($action) {
        case 'list_categories':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_categories action');
            }
            $productTypeId = isset($_GET['product_type_id']) ? (int)$_GET['product_type_id'] : null;
            $result = \App\modules\invoice\controllers\get_product_categories($productTypeId);
            break;

        case 'list_subcategories':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_subcategories action');
            }
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
            $result = \App\modules\invoice\controllers\get_product_subcategories($categoryId);
            break;

        case 'list_types':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_types action');
            }
            $result = \App\modules\invoice\controllers\get_product_types();
            break;

        case 'list_tax_rates':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_tax_rates action');
            }
            $result = \App\modules\invoice\controllers\getTaxRates();
            break;

        case 'add':
            if ($method !== 'POST') {
                throw new Exception('POST method required for add action');
            }
            $user_id = $_SESSION['user_id'] ?? null;
            $result = \App\modules\invoice\controllers\add_product($_POST, $user_id);
            // In add action, after product is created, handle supplier_id
            // On add: insert into inventory.product_supplier (product_id, supplier_id)
            if (isset($_POST['supplier_id']) && $_POST['supplier_id']) {
                $supplier_id = $_POST['supplier_id'];
                $product_id = $result['data']['product_id'];
                try {
                    $sql = "INSERT INTO inventory.product_supplier (product_id, supplier_id) VALUES (:product_id, :supplier_id) ON CONFLICT DO NOTHING";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':product_id' => $product_id, ':supplier_id' => $supplier_id]);
                } catch (PDOException $e) {
                    require_once __DIR__ . '/../../../src/Helpers/helpers.php';
                    $result = [
                        'success' => false,
                        'message' => get_friendly_error($e->getMessage()),
                        'data' => null
                    ];
                }
            }
            break;

        case 'edit':
            if ($method !== 'PUT') {
                throw new Exception('PUT method required for edit action');
            }
            $user_id = $_SESSION['user_id'] ?? null;
            $result = \App\modules\invoice\controllers\update_product($_POST, $user_id);
            // In edit action, after product is updated, handle supplier_id
            // On edit: update or insert as needed
            if (isset($_POST['supplier_id']) && $_POST['supplier_id']) {
                $supplier_id = $_POST['supplier_id'];
                $product_id = $result['data']['product_id'];
                try {
                    $sql = "SELECT * FROM inventory.product_supplier WHERE product_id = :product_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':product_id' => $product_id]);
                    $existing_supplier = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($existing_supplier && $existing_supplier['supplier_id'] == $supplier_id) {
                        // The link already exists, do nothing
                        $result['message'] = 'This supplier is already linked to the product.';
                    } elseif ($existing_supplier) {
                        // Update to a new supplier
                        $sql = "UPDATE inventory.product_supplier SET supplier_id = :supplier_id WHERE product_id = :product_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([':product_id' => $product_id, ':supplier_id' => $supplier_id]);
                    } else {
                        // Insert new link
                        $sql = "INSERT INTO inventory.product_supplier (product_id, supplier_id) VALUES (:product_id, :supplier_id)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([':product_id' => $product_id, ':supplier_id' => $supplier_id]);
                    }
                } catch (PDOException $e) {
                    require_once __DIR__ . '/../../../src/Helpers/helpers.php';
                    $result = [
                        'success' => false,
                        'message' => get_friendly_error($e->getMessage()),
                        'data' => null
                    ];
                }
            }
            break;

        case 'upload_image':
            if ($method !== 'POST') {
                throw new Exception('POST method required for upload_image action');
            }
            // handleImageUpload is defined in ImageController.php and only used here
            $result = \App\modules\product\controllers\handleImageUpload();
            break;

        case 'delete':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete action');
            }
            // Fix: Accept product_id from query string for DELETE
            if (isset($_GET['id'])) {
                $_POST['product_id'] = $_GET['id'];
            }
            $user_id = $_SESSION['user_id'] ?? null;
            $result = \App\modules\invoice\controllers\delete_product($_POST['product_id'], $user_id);
            break;

        case 'list':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list action');
            }
            $result = \App\modules\invoice\controllers\list_products([]); // Use snake_case to match function definition
            break;

        case 'get':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get action');
            }
            // Accept both 'id' and 'product_id' as valid query params
            if (isset($_GET['id']) && !isset($_GET['product_id'])) {
                $_GET['product_id'] = $_GET['id'];
            }
            $result = \App\modules\invoice\controllers\get_product_details($_GET['product_id']);
            break;

        case 'search':
            if ($method !== 'GET') {
                throw new Exception('GET method required for search action');
            }
            $query = $_GET['query'] ?? '';
            $field = $_GET['field'] ?? '';
            if (strlen($query) < 2) throw new Exception('Query too short');
            $sql = "SELECT 
                        p.product_id, 
                        p.product_name, 
                        p.sku, 
                        p.barcode, 
                        p.product_description, 
                        p.product_price, 
                        p.tax_rate_id, 
                        tr.rate AS tax_rate, 
                        pi.product_stock_quantity
                    FROM core.products p
                    LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
                    LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
                    WHERE (p.product_name ILIKE :q OR p.sku ILIKE :q OR p.barcode ILIKE :q OR p.product_description ILIKE :q)
                    LIMIT 20";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':q' => "%$query%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
            break;

        case 'update_status':
            if ($method !== 'POST') {
                throw new Exception('POST method required for update_status action');
            }
            $user_id = $_SESSION['user_id'] ?? null;
            $result = \App\modules\invoice\controllers\update_product_status($_POST['product_id'], $_POST['status'], $user_id);
            break;

        case 'get_product_suppliers_and_stock':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_product_suppliers_and_stock action');
            }
            $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
            if (!$productId) throw new Exception('Missing product_id');
            $result = \App\modules\invoice\controllers\get_product_suppliers_and_stock($productId);
            break;

        case 'adjust_stock':
            if ($method !== 'POST') {
                throw new Exception('POST method required for adjust_stock action');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $product_supplier_id = $input['product_supplier_id'] ?? null;
            $quantity = $input['quantity'] ?? null;
            $cost_per_unit = $input['cost_per_unit'] ?? null;
            $notes = $input['notes'] ?? null;
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$product_supplier_id || !$quantity) {
                throw new Exception('Missing required fields');
            }
            $result = \App\modules\invoice\controllers\adjust_product_stock($product_supplier_id, $quantity, $cost_per_unit, $notes, $user_id);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }

    // Log successful response
    error_log("Request processed successfully for action: $action");
    if (isset($result)) {
        echo json_encode($result);
    }
} catch (PDOException $e) {
    error_log("Database error details: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("Error Info: " . print_r($e->errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => null
    ]);
} catch (Exception $e) {
    error_log("Error processing request: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => null
    ]);
}
  