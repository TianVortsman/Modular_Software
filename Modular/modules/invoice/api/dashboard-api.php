<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';

use App\Core\Database\ClientDatabase;

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session
error_log("API Session contents: " . print_r($_SESSION, true));

// Validate session variables
if (!isset($_SESSION['account_number'])) {
    error_log("No account_number in session");
    http_response_code(401);
    echo json_encode(['error' => 'No account number found in session']);
    exit;
}

// Get database connection
try {
    error_log("Attempting database connection for account: " . $_SESSION['account_number']);
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();

    if (!$conn) {
        error_log("Database connection failed - connection object is null");
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Exception during database connection: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$range = $_GET['range'] ?? ($_POST['range'] ?? 'this_month');
require_once __DIR__ . '/../controllers/dashboardController.php';
use App\modules\invoice\controllers\DashboardController;
$controller = new DashboardController($conn);

switch ($action) {
    case 'get_dashboard_cards':
        echo json_encode($controller->getDashboardCards($range));
        break;
    case 'get_recent_invoices':
        echo json_encode($controller->getRecentInvoices($range));
        break;
    case 'get_recurring_invoices':
        echo json_encode($controller->getRecurringInvoices($range));
        break;
    case 'get_invoice_chart_data':
        echo json_encode($controller->getInvoiceChartData($range));
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}