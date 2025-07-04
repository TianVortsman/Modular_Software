<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
require_once __DIR__ . '/../controllers/ProductController.php';

use App\Core\Database\ClientDatabase;
use App\modules\invoice\controllers\ProductController;

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
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
                    list($name, $value) = explode(':', $header, 2);
                    $headers[strtolower(trim($name))] = trim($value);
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
    $controller = new ProductController($conn);

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
            $result = $controller->getProductCategories();
            break;

        case 'list_subcategories':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_subcategories action');
            }
            $result = $controller->getProductSubcategories();
            break;

        case 'list_types':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_types action');
            }
            $result = $controller->getProductTypes();
            break;

        case 'add':
            if ($method !== 'POST') {
                throw new Exception('POST method required for add action');
            }
            $result = $controller->addProduct();
            break;

        case 'edit':
            if ($method !== 'PUT') {
                throw new Exception('PUT method required for edit action');
            }
            $result = $controller->updateProduct();
            break;

        case 'upload_image':
            if ($method !== 'POST') {
                throw new Exception('POST method required for upload_image action');
            }
            $result = $controller->handleImageUploadAPI();
            break;

        case 'delete':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete action');
            }
            $result = $controller->deleteProduct();
            break;

        case 'list':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list action');
            }
            $result = $controller->getAllProducts();
            break;

        case 'get':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get action');
            }
            $result = $controller->getProduct();
            break;

        default:
            throw new Exception("Invalid action: $action");
    }

    // Log successful response
    error_log("Request processed successfully for action: $action");
    echo json_encode($result);

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
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
  