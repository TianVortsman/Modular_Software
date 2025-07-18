<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
require_once __DIR__ . '/../controllers/ProductController.php';

use App\Core\Database\ClientDatabase;
use function App\modules\invoice\controllers\get_product_categories;
use function App\modules\invoice\controllers\get_product_subcategories;

header('Content-Type: application/json');

// Validate session/account
if (!isset($_SESSION['account_number'])) {
    echo json_encode(['success' => false, 'message' => 'User session not found']);
    exit;
}

// Get database connection
$db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
global $conn;
$conn = $db->connect();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'getCategories':
        $result = get_product_categories();
        echo json_encode($result);
        break;
    case 'getSubcategories':
        $result = get_product_subcategories();
        echo json_encode($result);
        break;
    case 'saveCategory':
        // Accept POST only
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            break;
        }
        $result = App\modules\invoice\controllers\save_product_category($_POST);
        echo json_encode($result);
        break;
    case 'saveSubcategory':
        // Accept POST only
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'POST required']);
            break;
        }
        $result = App\modules\invoice\controllers\save_product_subcategory($_POST);
        echo json_encode($result);
        break;
    case 'getSuppliers':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $result = App\modules\product\controllers\list_suppliers();
        echo json_encode($result);
        break;
    case 'getSupplier':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $supplier_id = $_GET['supplier_id'] ?? null;
        $result = $supplier_id ? App\modules\product\controllers\get_supplier((int)$supplier_id) : ['success' => false, 'message' => 'Missing supplier_id'];
        echo json_encode($result);
        break;
    case 'saveSupplier':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $user_id = $_SESSION['user_id'] ?? ($_SESSION['tech_id'] ?? null);
        $supplier_id = $_POST['supplier_id'] ?? null;
        if ($supplier_id) {
            $result = App\modules\product\controllers\update_supplier((int)$supplier_id, $_POST, $user_id);
        } else {
            $result = App\modules\product\controllers\add_supplier($_POST, $user_id);
        }
        echo json_encode($result);
        break;
    case 'getSupplierContacts':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $supplier_id = $_GET['supplier_id'] ?? null;
        if ($supplier_id) {
            $result = App\modules\product\controllers\get_supplier_contacts((int)$supplier_id);
        } else {
            $result = ['success' => false, 'message' => 'Missing supplier_id'];
        }
        echo json_encode($result);
        break;
    case 'addSupplierContact':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $user_id = $_SESSION['user_id'] ?? ($_SESSION['tech_id'] ?? null);
        $result = App\modules\product\controllers\add_supplier_contact($_POST, $user_id);
        echo json_encode($result);
        break;
    case 'updateSupplierContact':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $user_id = $_SESSION['user_id'] ?? ($_SESSION['tech_id'] ?? null);
        $contact_id = $_POST['contact_person_id'] ?? null;
        if ($contact_id) {
            $result = App\modules\product\controllers\update_supplier_contact((int)$contact_id, $_POST, $user_id);
        } else {
            $result = ['success' => false, 'message' => 'Missing contact_person_id'];
        }
        echo json_encode($result);
        break;
    case 'deleteSupplierContact':
        require_once __DIR__ . '/../controllers/SupplierController.php';
        $user_id = $_SESSION['user_id'] ?? ($_SESSION['tech_id'] ?? null);
        $contact_id = $_POST['contact_person_id'] ?? null;
        if ($contact_id) {
            $result = App\modules\product\controllers\delete_supplier_contact((int)$contact_id, $user_id);
        } else {
            $result = ['success' => false, 'message' => 'Missing contact_person_id'];
        }
        echo json_encode($result);
        break;
    // Add other actions as needed...
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
