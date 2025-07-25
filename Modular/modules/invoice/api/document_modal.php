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
    if ($action === 'update_document' && $method === 'POST') {
        $document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;
        if (!$document_id) {
            echo json_encode(['success' => false, 'message' => 'Missing document_id', 'data' => null]);
            exit;
        }
        
        $rawData = json_decode(file_get_contents('php://input'), true);
        
        // Map frontend data to backend format
        $items = [];
        if (isset($rawData['items']) && is_array($rawData['items'])) {
            foreach ($rawData['items'] as $item) {
                $mappedItem = [
                    'item_id' => $item['item_id'] ?? null,
                    'product_id' => !empty($item['product_id']) ? (int)$item['product_id'] : null,
                    'product_description' => $item['product_description'] ?? ($item['description'] ?? ''),
                    'quantity' => (float)($item['quantity'] ?? 1),
                    'unit_price' => (float)str_replace(['R', ','], '', $item['unit_price'] ?? '0'),
                    'discount_percentage' => 0, // Frontend doesn't send this yet
                    'tax_rate_id' => !empty($item['tax_percentage']) ? (int)$item['tax_percentage'] : null,
                    'line_total' => (float)str_replace(['R', ','], '', $item['line_total'] ?? '0')
                ];
                $items[] = $mappedItem;
            }
        }
        
        // Map main document data
        $mappedDocumentData = [
            'client_id' => (int)($rawData['client_id'] ?? 0),
            'document_type' => $rawData['document_type'] ?? 'invoice',
            'issue_date' => $rawData['issue_date'] ?? $rawData['invoice_date'] ?? date('Y-m-d'),
            'due_date' => $rawData['due_date'] ?? null,
            'salesperson_id' => !empty($rawData['salesperson_id']) ? (int)$rawData['salesperson_id'] : null,
            'subtotal' => (float)str_replace(['R', ','], '', $rawData['subtotal'] ?? '0'),
            'discount_amount' => 0, // Not implemented in frontend yet
            'tax_amount' => (float)str_replace(['R', ','], '', $rawData['tax_amount'] ?? '0'),
            'total_amount' => (float)str_replace(['R', ','], '', $rawData['total_amount'] ?? '0'),
            'balance_due' => (float)str_replace(['R', ','], '', $rawData['total_amount'] ?? '0'),
            'client_purchase_order_number' => $rawData['client_purchase_order_number'] ?? null,
            'notes' => $rawData['public_note'] ?? $rawData['notes'] ?? null,
            'terms_conditions' => $rawData['private_note'] ?? null,
            'is_recurring' => !empty($rawData['is_recurring']),
            'recurring_template_id' => null,
            'requires_approval' => false,
            'updated_by' => $_SESSION['user_id'] ?? null,
            // Additional fields for frontend compatibility
            'client_name' => $rawData['client_name'] ?? '',
            'client_email' => $rawData['client_email'] ?? '',
            'client_phone' => $rawData['client_phone'] ?? '',
            'address1' => $rawData['address1'] ?? '',
            'address2' => $rawData['address2'] ?? '',
            'vat_number' => $rawData['vat_number'] ?? '',
            'registration_number' => $rawData['registration_number'] ?? '',
            'salesperson_name' => $rawData['salesperson_name'] ?? ''
        ];
        
        $documentStatus = $rawData['document_status'] ?? 'draft';
        $mode = (strtolower($documentStatus) === 'draft') ? 'draft' : 'finalize';
        
        // Prepare the structured data
        $structuredData = [
            'documentData' => $mappedDocumentData,
            'items' => $items,
            'mode' => $mode,
            'status' => $documentStatus
        ];
        
        $result = App\modules\invoice\controllers\update_document($document_id, $structuredData);
        echo json_encode($result);
        exit;
    }
    if ($action === 'fetch_document' && $method === 'GET') {
        $document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;
        if (!$document_id) {
            echo json_encode(['success' => false, 'message' => 'Missing document_id', 'data' => null]);
            exit;
        }
        $result = App\modules\invoice\controllers\get_document_details($document_id);
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