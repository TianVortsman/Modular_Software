<?php
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../controllers/DocumentController.php';

global $conn;
if (!isset($conn)) {
    if (!isset($_SESSION['account_number'])) {
        echo json_encode(['success' => false, 'message' => 'User session not found', 'data' => null]);
        exit;
    }
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed', 'data' => null]);
        exit;
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($action === 'save_document' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = App\modules\invoice\controllers\create_document($data);
        echo json_encode($result);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid action or method', 'data' => null]);
} catch (Exception $e) {
    error_log('[DOCUMENT_MODAL_API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => null]);
    exit;
} 