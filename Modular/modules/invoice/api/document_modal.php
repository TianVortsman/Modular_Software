<?php
error_log('[DOCUMENT_API] API endpoint accessed - ' . date('Y-m-d H:i:s'));
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controllers/DocumentController.php';

try {
    if (!isset($_SESSION['account_number'])) {
        sendApiErrorResponse('User session not found', null, 'Document API Authentication', 'SESSION_NOT_FOUND', 401);
    }
    
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    global $conn;
    
    if (!$conn) {
        sendApiErrorResponse('Database connection failed', null, 'Document API Database Connection', 'DB_CONN_ERROR');
    }
    
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    error_log('[DOCUMENT_API] Action: ' . $action . ', Method: ' . $method);
    error_log('[DOCUMENT_API] GET params: ' . json_encode($_GET));

    switch ($action) {
        case 'save_document':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for save_document action', $_GET, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid document data', ['raw_input' => $rawData], 'Document API Data Validation', 'DOCUMENT_DATA_REQUIRED', 400);
            }
            
            $result = App\modules\invoice\controllers\create_document($data);
            echo json_encode($result);
            break;

        case 'update_document':
            if ($method !== 'POST') {
                sendApiErrorResponse('POST method required for update_document action', $_GET, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;
            if (!$document_id) {
                sendApiErrorResponse('Missing or invalid document_id parameter', $_GET, 'Document API Parameter Validation', 'DOCUMENT_ID_REQUIRED', 400);
            }
            
            $rawData = file_get_contents('php://input');
            $data = json_decode($rawData, true);
            
            if (!$data) {
                sendApiErrorResponse('Missing or invalid document data', ['raw_input' => $rawData], 'Document API Data Validation', 'DOCUMENT_DATA_REQUIRED', 400);
            }
            
            error_log('[UPDATE_DOCUMENT] Document ID: ' . $document_id . ', Raw data keys: ' . implode(', ', array_keys($data ?? [])));
            
            $result = App\modules\invoice\controllers\update_document($document_id, $data);
            echo json_encode($result);
            break;

        case 'fetch_document':
        case 'get_document':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for get_document action', $_GET, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;
            if (!$document_id) {
                sendApiErrorResponse('Missing or invalid document_id parameter', $_GET, 'Document API Parameter Validation', 'DOCUMENT_ID_REQUIRED', 400);
            }
            
            $result = App\modules\invoice\controllers\get_document_details($document_id);
            echo json_encode($result);
            break;

        case 'delete_document':
            if ($method !== 'DELETE' && $method !== 'POST') {
                sendApiErrorResponse('DELETE or POST method required for delete_document action', $_REQUEST, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;
            if (!$document_id) {
                sendApiErrorResponse('Missing or invalid document_id parameter', $_GET, 'Document API Parameter Validation', 'DOCUMENT_ID_REQUIRED', 400);
            }
            
            $deleted_by = $_SESSION['user_id'] ?? null;
            if (!$deleted_by) {
                sendApiErrorResponse('User session invalid - cannot determine deleted_by', $_SESSION, 'Document API Session Validation', 'INVALID_SESSION', 401);
            }
            
            $result = App\modules\invoice\controllers\delete_document($document_id, $deleted_by);
            echo json_encode($result);
            break;

        case 'get_related_documents':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for get_related_documents action', $_GET, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : null;
            if (!$document_id) {
                sendApiErrorResponse('Missing or invalid document_id parameter', $_GET, 'Document API Parameter Validation', 'DOCUMENT_ID_REQUIRED', 400);
            }
            
            $result = App\modules\invoice\controllers\get_related_documents($document_id);
            echo json_encode($result);
            break;

        case 'get_available_invoices_for_credit_refund':
            if ($method !== 'GET') {
                sendApiErrorResponse('GET method required for get_available_invoices_for_credit_refund action', $_GET, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;
            $result = App\modules\invoice\controllers\get_available_invoices_for_credit_refund($client_id);
            echo json_encode($result);
            break;

        case 'get_next_quotation_number':
        case 'get_next_invoice_number':
        case 'get_next_credit_note_number':
        case 'get_next_refund_number':
        case 'get_next_proforma_number':
            error_log('[DOCUMENT_API] Document numbering action detected: ' . $action);
            error_log('[DOCUMENT_API] Method: ' . $method);
            error_log('[DOCUMENT_API] Request data: ' . json_encode($_GET));
            
            if ($method !== 'GET') {
                error_log('[DOCUMENT_API] Invalid method, sending error response');
                sendApiErrorResponse('GET method required for document numbering action', $_GET, 'Document API Method Validation', 'INVALID_METHOD', 405);
            }
            
            $action = $_GET['action'];
            error_log('[DOCUMENT_API] Getting next document number for action: ' . $action);
            
            try {
                error_log('[DOCUMENT_API] About to call get_next_document_number');
                $result = App\modules\invoice\controllers\get_next_document_number($action);
                error_log('[DOCUMENT_API] Result received: ' . json_encode($result));
                
                if ($result === null) {
                    error_log('[DOCUMENT_API] Result is null, sending fallback');
                    $result = [
                        'success' => true,
                        'message' => 'Next document number generated successfully (fallback)',
                        'data' => [
                            'number' => 'DOC-' . time(),
                            'next_number' => 1,
                            'prefix' => 'DOC'
                        ]
                    ];
                }
                
                error_log('[DOCUMENT_API] About to echo JSON response');
                echo json_encode($result);
                error_log('[DOCUMENT_API] JSON response sent');
                
            } catch (Exception $e) {
                error_log('[DOCUMENT_API] Exception caught: ' . $e->getMessage());
                error_log('[DOCUMENT_API] Exception stack trace: ' . $e->getTraceAsString());
                
                $errorResponse = [
                    'success' => false,
                    'message' => 'Error getting document number: ' . $e->getMessage(),
                    'data' => null,
                    'error_code' => 'DOCUMENT_NUMBER_ERROR'
                ];
                
                error_log('[DOCUMENT_API] Sending error response: ' . json_encode($errorResponse));
                echo json_encode($errorResponse);
            }
            break;

        default:
            error_log('[DOCUMENT_API] Invalid action: ' . $action);
            sendApiErrorResponse("Invalid action: $action", [
                'action' => $action, 
                'available_actions' => ['save_document', 'update_document', 'get_document', 'delete_document', 'get_related_documents', 'get_available_invoices_for_credit_refund', 'get_next_quotation_number', 'get_next_invoice_number', 'get_next_credit_note_number', 'get_next_refund_number', 'get_next_proforma_number']
            ], 'Document API Action Validation', 'INVALID_ACTION', 400);
    }
    
} catch (PDOException $e) {
    $formData = [
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown',
        'session_data' => $_SESSION ?? [],
    ];
    sendApiErrorResponse('Database error: ' . $e->getMessage(), $formData, 'Document API Database Error');
    
} catch (Exception $e) {
    $formData = [
        'action' => $action ?? 'unknown',
        'method' => $method ?? 'unknown',
        'session_data' => $_SESSION ?? [],
    ];
    sendApiErrorResponse('Unexpected error: ' . $e->getMessage(), $formData, 'Document API General Error');
} 