<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/DocumentSendingService.php';

use App\Services\DocumentSendingService;
use Exception;

/**
 * Document Sending Controller
 * Provides a clean interface for other modules to send documents
 */
class DocumentSendingController
{
    private $documentService;
    
    public function __construct()
    {
        $this->documentService = new DocumentSendingService();
    }
    
    /**
     * Send document by email
     * 
     * @param int $document_id Document ID to send
     * @param array $options Additional options (custom subject, body, etc.)
     * @return array Result with success status and message
     */
    public function sendByEmail($document_id, $options = [])
    {
        return $this->documentService->sendDocumentByEmail($document_id, $options);
    }
    
    /**
     * Send document by WhatsApp
     * 
     * @param int $document_id Document ID to send
     * @param array $options Additional options (custom message, etc.)
     * @return array Result with success status and message
     */
    public function sendByWhatsApp($document_id, $options = [])
    {
        return $this->documentService->sendDocumentByWhatsApp($document_id, $options);
    }
    
    /**
     * Send document with custom options
     * 
     * @param int $document_id Document ID to send
     * @param string $method 'email' or 'whatsapp'
     * @param array $options Additional options
     * @return array Result with success status and message
     */
    public function sendDocument($document_id, $method = 'email', $options = [])
    {
        switch (strtolower($method)) {
            case 'whatsapp':
                return $this->sendByWhatsApp($document_id, $options);
            case 'email':
            default:
                return $this->sendByEmail($document_id, $options);
        }
    }
} 