<?php
// send-document-email.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/Services/DocumentSendingService.php';

use App\Services\DocumentSendingService;

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['account_number'])) {
        throw new Exception('User session not found');
    }

    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST method required');
    }

    // Get request data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received');
    }

    // Validate required fields
    if (empty($data['document_id'])) {
        throw new Exception('Document ID is required');
    }

    $document_id = (int) $data['document_id'];
    $action = $data['action'] ?? 'email'; // 'email' or 'whatsapp'

    // Initialize service
    $documentService = new DocumentSendingService();

    // Send document based on action
    if ($action === 'whatsapp') {
        $result = $documentService->sendDocumentByWhatsApp($document_id);
    } else {
        $result = $documentService->sendDocumentByEmail($document_id);
    }

    // Return result
    echo json_encode($result);

} catch (Exception $e) {
    error_log('[SEND DOCUMENT EMAIL ERROR] ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send document: ' . $e->getMessage(),
        'error_code' => 'SEND_DOCUMENT_ERROR'
    ]);
} 