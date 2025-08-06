<?php
namespace App\Services;

require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/../Core/Database/ClientDatabase.php';
require_once __DIR__ . '/../Helpers/helpers.php';

use App\Services\EmailService;
use App\Core\Database\ClientDatabase;
use PDO;
use Exception;

/**
 * Document Sending Service
 * Handles sending documents via various channels (email, WhatsApp, etc.)
 * Reusable across the entire application
 */
class DocumentSendingService
{
    private $emailService;
    private $db;
    private $conn;
    
    public function __construct()
    {
        $this->emailService = new EmailService();
        
        // Get client database connection
        if (isset($_SESSION['account_number'])) {
            $this->db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
            $this->conn = $this->db->connect();
        } else {
            throw new Exception('User session not found');
        }
    }
    
    /**
     * Send document by email
     * 
     * @param int $document_id Document ID to send
     * @param array $options Additional options (custom subject, body, etc.)
     * @return array Result with success status and message
     */
    public function sendDocumentByEmail($document_id, $options = [])
    {
        try {
            // Step 1: Get document details
            $documentDetails = $this->getDocumentDetails($document_id);
            if (!$documentDetails['success']) {
                return $documentDetails;
            }
            
            $document = $documentDetails['data'];
            
            // Step 2: Get document prefix from settings
            $prefix = $this->getDocumentPrefix($document['document_type']);
            
            // Step 3: Construct expected PDF filename and path
            $filename = $this->constructPdfFilename($prefix, $document['document_number'], $document_id);
            $pdfPath = $this->getPdfFilePath($filename);
            
            // Step 4: Check if PDF exists, generate if not
            if (!file_exists($pdfPath)) {
                $pdfResult = $this->generatePdfForDocument($document_id, $document);
                if (!$pdfResult['success']) {
                    return $pdfResult;
                }
                // Update path to the newly generated PDF
                $pdfPath = $pdfResult['filepath'];
            }
            
            // Step 5: Get client email
            $clientEmail = $document['client_email'];
            if (empty($clientEmail)) {
                return [
                    'success' => false,
                    'message' => 'Client email not found for document',
                    'error_code' => 'CLIENT_EMAIL_NOT_FOUND'
                ];
            }
            
            // Step 6: Send email with attachment
            $emailResult = $this->sendDocumentEmail($document, $clientEmail, $pdfPath, $filename, $options);
            error_log('[DocumentSendingService] Email result: ' . json_encode($emailResult));
            
            // Step 7: Update document status to "sent" if email was successful
            if ($emailResult['success']) {
                try {
                    $this->updateDocumentStatus($document_id, 'sent');
                    error_log('[DocumentSendingService] Document status updated to sent');
                } catch (Exception $statusError) {
                    error_log('[DocumentSendingService] Failed to update document status: ' . $statusError->getMessage());
                }
            }
            
            // Step 8: Log the action (don't let logging failure break the email)
            try {
                $this->logDocumentSent($document_id, $clientEmail, 'email', $emailResult['success']);
            } catch (Exception $logError) {
                error_log('[DocumentSendingService] Logging failed but email may have succeeded: ' . $logError->getMessage());
            }
            
            return $emailResult;
            
        } catch (Exception $e) {
            error_log('DocumentSendingService error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send document: ' . $e->getMessage(),
                'error_code' => 'DOCUMENT_SENDING_ERROR'
            ];
        }
    }
    
    /**
     * Send document by WhatsApp
     * 
     * @param int $document_id Document ID to send
     * @param array $options Additional options (custom message, etc.)
     * @return array Result with success status and message
     */
    public function sendDocumentByWhatsApp($document_id, $options = [])
    {
        try {
            // Step 1: Get document details
            $documentDetails = $this->getDocumentDetails($document_id);
            if (!$documentDetails['success']) {
                return $documentDetails;
            }
            
            $document = $documentDetails['data'];
            
            // Step 2: Get client phone number
            $clientPhone = $document['client_cell'] ?? $document['client_tell'];
            if (empty($clientPhone)) {
                return [
                    'success' => false,
                    'message' => 'Client phone number not found for document',
                    'error_code' => 'CLIENT_PHONE_NOT_FOUND'
                ];
            }
            
            // Step 3: Generate PDF if needed
            $prefix = $this->getDocumentPrefix($document['document_type']);
            $filename = $this->constructPdfFilename($prefix, $document['document_number'], $document_id);
            $pdfPath = $this->getPdfFilePath($filename);
            
            if (!file_exists($pdfPath)) {
                $pdfResult = $this->generatePdfForDocument($document_id, $document);
                if (!$pdfResult['success']) {
                    return $pdfResult;
                }
                $pdfPath = $pdfResult['filepath'];
            }
            
            // Step 4: Send via WhatsApp (placeholder for now)
            $whatsappResult = $this->sendDocumentWhatsApp($document, $clientPhone, $pdfPath, $filename, $options);
            error_log('[DocumentSendingService] WhatsApp result: ' . json_encode($whatsappResult));
            
            // Step 5: Update document status if successful
            if ($whatsappResult['success']) {
                try {
                    $this->updateDocumentStatus($document_id, 'sent');
                    error_log('[DocumentSendingService] Document status updated to sent');
                } catch (Exception $statusError) {
                    error_log('[DocumentSendingService] Failed to update document status: ' . $statusError->getMessage());
                }
            }
            
            // Step 6: Log the action
            try {
                $this->logDocumentSent($document_id, $clientPhone, 'whatsapp', $whatsappResult['success']);
            } catch (Exception $logError) {
                error_log('[DocumentSendingService] Logging failed but WhatsApp may have succeeded: ' . $logError->getMessage());
            }
            
            return $whatsappResult;
            
        } catch (Exception $e) {
            error_log('DocumentSendingService WhatsApp error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send document via WhatsApp: ' . $e->getMessage(),
                'error_code' => 'WHATSAPP_SENDING_ERROR'
            ];
        }
    }
    
    /**
     * Get document details from database
     */
    private function getDocumentDetails($document_id)
    {
        $sql = "SELECT d.document_id, d.document_number, d.document_type, d.issue_date, d.due_date, 
                       d.total_amount, d.document_status, d.notes, d.client_id,
                       c.client_id, c.client_name, c.client_email, c.client_cell, c.client_tell,
                       c.registration_number, c.vat_number,
                       s.company_name, s.company_email
                FROM invoicing.documents d
                JOIN invoicing.clients c ON d.client_id = c.client_id
                LEFT JOIN settings.invoice_settings s ON s.id = 1
                WHERE d.document_id = :document_id AND d.deleted_at IS NULL";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Document not found',
                    'error_code' => 'DOCUMENT_NOT_FOUND'
                ];
            }
            
            error_log('[DocumentSendingService] Document details retrieved: ' . json_encode($result));
            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (Exception $e) {
            error_log('Error getting document details: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error occurred',
                'error_code' => 'DATABASE_ERROR'
            ];
        }
    }
    
    /**
     * Get document prefix from settings based on document type
     */
    private function getDocumentPrefix($document_type)
    {
        $prefixMap = [
            'invoice' => 'invoice_prefix',
            'quotation' => 'quotation_prefix',
            'vehicle_invoice' => 'vehicle_invoice_prefix',
            'vehicle_quotation' => 'vehicle_quotation_prefix',
            'credit_note' => 'credit_note_prefix',
            'proforma' => 'proforma_prefix',
            'refund' => 'refund_prefix'
        ];
        
        $prefixField = $prefixMap[$document_type] ?? 'invoice_prefix';
        
        $sql = "SELECT $prefixField FROM settings.invoice_settings WHERE id = 1";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result[$prefixField] ?? 'DOC';
        } catch (Exception $e) {
            error_log('Error getting document prefix: ' . $e->getMessage());
            return 'DOC';
        }
    }
    
    /**
     * Construct PDF filename using naming convention: [Prefix]-[DocumentNumber]-[DocumentID].pdf
     */
    private function constructPdfFilename($prefix, $document_number, $document_id)
    {
        return $prefix . '-' . $document_number . '-' . $document_id . '.pdf';
    }
    
    /**
     * Get full PDF file path
     */
    private function getPdfFilePath($filename)
    {
        $accountCode = $_SESSION['account_number'] ?? 'ACC002';
        $basePath = __DIR__ . '/../../Uploads/' . $accountCode . '/invoices/';
        
        // Create directory if it doesn't exist
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }
        
        return $basePath . $filename;
    }
    
    /**
     * Generate PDF for document if it doesn't exist
     */
    private function generatePdfForDocument($document_id, $document)
    {
        try {
            // Get document items
            $itemsSql = "SELECT i.product_description, i.quantity, i.unit_price, i.line_total, 
                                tr.rate as tax_percentage
                         FROM invoicing.document_items i
                         LEFT JOIN core.tax_rates tr ON i.tax_rate_id = tr.tax_rate_id
                         WHERE i.document_id = :document_id";
            
            $stmt = $this->conn->prepare($itemsSql);
            $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Prepare data for PDF generation
            $pdfData = [
                'client_name' => $document['client_name'],
                'client_email' => $document['client_email'],
                'client_phone' => $document['client_cell'] ?? $document['client_tell'],
                'vat_number' => $document['vat_number'],
                'registration_number' => $document['registration_number'],
                'document_number' => $document['document_number'],
                'document_type' => $document['document_type'],
                'issue_date' => $document['issue_date'],
                'due_date' => $document['due_date'],
                'document_status' => $document['document_status'],
                'total_amount' => $document['total_amount'],
                'items' => $items,
                'subtotal' => $document['total_amount'], // Simplified for now
                'tax_amount' => 0, // Calculate if needed
                'discount_amount' => 0
            ];
            
            // Generate PDF using existing function
            $filename = $this->constructPdfFilename(
                $this->getDocumentPrefix($document['document_type']),
                $document['document_number'],
                $document_id
            );
            $filepath = $this->getPdfFilePath($filename);
            
            // Use mPDF to generate PDF
            require_once __DIR__ . '/../../vendor/autoload.php';
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15
            ]);
            
            $html = $this->generateDocumentHTML($pdfData);
            $mpdf->WriteHTML($html);
            $mpdf->Output($filepath, 'F');
            
            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            error_log('Error generating PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
                'error_code' => 'PDF_GENERATION_ERROR'
            ];
        }
    }
    
    /**
     * Generate HTML for document PDF
     */
    private function generateDocumentHTML($data)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>
            body { font-family: "Segoe UI", Arial, sans-serif; color: #222; margin: 0; padding: 20px; }
            .document-box { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; }
            .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
            .company-info { font-size: 1.2em; font-weight: bold; color: #1a73e8; }
            .document-title { font-size: 2em; color: #1a73e8; font-weight: bold; margin-bottom: 20px; }
            .client-info { margin-bottom: 30px; }
            .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            .items-table th { background: #f5f5f5; }
            .total { text-align: right; font-weight: bold; font-size: 1.2em; margin-top: 20px; }
        </style></head><body>';
        
        $html .= '<div class="document-box">';
        $html .= '<div class="header">';
        $html .= '<div class="company-info">' . ($data['company_name'] ?? 'Your Company') . '</div>';
        $html .= '<div class="document-title">' . strtoupper($data['document_type']) . '</div>';
        $html .= '</div>';
        
        $html .= '<div class="client-info">';
        $html .= '<strong>To:</strong> ' . $data['client_name'] . '<br>';
        if (!empty($data['client_email'])) {
            $html .= '<strong>Email:</strong> ' . $data['client_email'] . '<br>';
        }
        if (!empty($data['client_phone'])) {
            $html .= '<strong>Phone:</strong> ' . $data['client_phone'] . '<br>';
        }
        $html .= '<strong>Document Number:</strong> ' . $data['document_number'] . '<br>';
        $html .= '<strong>Date:</strong> ' . $data['issue_date'] . '<br>';
        if (!empty($data['due_date'])) {
            $html .= '<strong>Due Date:</strong> ' . $data['due_date'] . '<br>';
        }
        $html .= '</div>';
        
        if (!empty($data['items'])) {
            $html .= '<table class="items-table">';
            $html .= '<thead><tr><th>Description</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['product_description']) . '</td>';
                $html .= '<td>' . $item['quantity'] . '</td>';
                $html .= '<td>R' . number_format($item['unit_price'], 2) . '</td>';
                $html .= '<td>R' . number_format($item['line_total'], 2) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }
        
        $html .= '<div class="total">';
        $html .= '<strong>Total Amount: R' . number_format($data['total_amount'], 2) . '</strong>';
        $html .= '</div>';
        
        $html .= '</div></body></html>';
        
        return $html;
    }
    
    /**
     * Send document email with PDF attachment
     */
    private function sendDocumentEmail($document, $clientEmail, $pdfPath, $filename, $options = [])
    {
        $prefix = $this->getDocumentPrefix($document['document_type']);
        $companyName = $document['company_name'] ?? 'Your Company';
        
        // Use custom subject if provided, otherwise use default
        $subject = $options['subject'] ?? "Your Document: {$prefix}-{$document['document_number']} from {$companyName}";
        
        // Use custom body if provided, otherwise use default
        if (isset($options['body'])) {
            $body = $options['body'];
        } else {
            $body = "Dear {$document['client_name']},<br><br>";
            $body .= "Please find your {$document['document_type']} attached to this email.<br><br>";
            $body .= "<strong>Document Details:</strong><br>";
            $body .= "Document Number: {$document['document_number']}<br>";
            $body .= "Date: {$document['issue_date']}<br>";
            $body .= "Total Amount: R" . number_format($document['total_amount'], 2) . "<br><br>";
            
            if (!empty($document['notes'])) {
                $body .= "<strong>Notes:</strong><br>";
                $body .= nl2br(htmlspecialchars($document['notes'])) . "<br><br>";
            }
            
            $body .= "If you have any questions, please don't hesitate to contact us.<br><br>";
            $body .= "Best regards,<br>{$companyName}";
        }
        
        error_log('[DocumentSendingService] About to send email to: ' . $clientEmail . ' with subject: ' . $subject);
        error_log('[DocumentSendingService] PDF path: ' . $pdfPath . ' (exists: ' . (file_exists($pdfPath) ? 'yes' : 'no') . ')');
        
        $result = $this->emailService->sendEmail($clientEmail, $subject, $body, $pdfPath, $filename);
        error_log('[DocumentSendingService] EmailService result: ' . json_encode($result));
        
        return $result;
    }
    
    /**
     * Send document via WhatsApp using WebSocket server
     */
    private function sendDocumentWhatsApp($document, $clientPhone, $pdfPath, $filename, $options = [])
    {
        try {
            // Get customer ID from session
            $customerId = $_SESSION['account_number'] ?? null;
            if (!$customerId) {
                return [
                    'success' => false,
                    'message' => 'No customer account found in session',
                    'error_code' => 'NO_CUSTOMER_SESSION'
                ];
            }
            
            // Check if PDF file exists
            if (!file_exists($pdfPath)) {
                return [
                    'success' => false,
                    'message' => 'PDF file not found: ' . $filename,
                    'error_code' => 'PDF_NOT_FOUND'
                ];
            }
            
            // Prepare message caption
            $caption = $this->generateWhatsAppCaption($document, $options);
            
            // Send to WebSocket server
            $websocketUrl = $this->getWebSocketServerUrl();
            $url = $websocketUrl . "/whatsapp/send-document/{$customerId}";
            
            // Prepare the request data
            $postData = [
                'recipient' => $clientPhone,
                'document_path' => $pdfPath,
                'caption' => $caption,
                'user_id' => $_SESSION['user_id'] ?? 1,
                'session_id' => session_id()
            ];
            
            // Send request to WebSocket server
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                error_log('[DocumentSendingService] Curl error: ' . $curlError);
                return [
                    'success' => false,
                    'message' => 'Failed to connect to WhatsApp server: ' . $curlError,
                    'error_code' => 'WEBSOCKET_CONNECTION_ERROR'
                ];
            }
            
            if ($httpCode !== 200) {
                error_log('[DocumentSendingService] WebSocket server returned HTTP ' . $httpCode . ': ' . $response);
                return [
                    'success' => false,
                    'message' => 'WhatsApp server error (HTTP ' . $httpCode . ')',
                    'error_code' => 'WEBSOCKET_SERVER_ERROR'
                ];
            }
            
            $result = json_decode($response, true);
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Invalid response from WhatsApp server',
                    'error_code' => 'INVALID_RESPONSE'
                ];
            }
            
            if ($result['success']) {
                error_log('[DocumentSendingService] WhatsApp document sent successfully: ' . $document['document_number']);
                return [
                    'success' => true,
                    'message' => 'Document sent successfully via WhatsApp',
                    'message_id' => $result['message_id'] ?? null
                ];
            } else {
                error_log('[DocumentSendingService] WhatsApp sending failed: ' . ($result['message'] ?? 'Unknown error'));
                return [
                    'success' => false,
                    'message' => 'WhatsApp sending failed: ' . ($result['message'] ?? 'Unknown error'),
                    'error_code' => 'WHATSAPP_SENDING_FAILED'
                ];
            }
            
        } catch (Exception $e) {
            error_log('[DocumentSendingService] WhatsApp sending error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'WhatsApp sending error: ' . $e->getMessage(),
                'error_code' => 'WHATSAPP_SENDING_ERROR'
            ];
        }
    }
    
    /**
     * Generate WhatsApp caption for document
     */
    private function generateWhatsAppCaption($document, $options = [])
    {
        $companyName = $document['company_name'] ?? 'Your Company';
        $documentType = ucfirst($document['document_type']);
        $documentNumber = $document['document_number'];
        $totalAmount = number_format($document['total_amount'], 2);
        
        $caption = "📄 {$documentType} #{$documentNumber}\n\n";
        $caption .= "Dear {$document['client_name']},\n\n";
        $caption .= "Please find attached your {$documentType} for R{$totalAmount}.\n\n";
        
        if (!empty($document['notes'])) {
            $caption .= "Notes: {$document['notes']}\n\n";
        }
        
        $caption .= "If you have any questions, please don't hesitate to contact us.\n\n";
        $caption .= "Best regards,\n{$companyName}";
        
        return $caption;
    }
    
    /**
     * Get WebSocket server URL
     */
    private function getWebSocketServerUrl()
    {
        return $_ENV['WEBSOCKET_SERVER_URL'] ?? 'http://websocket-server:3001';
    }
    
    /**
     * Update document status
     */
    private function updateDocumentStatus($document_id, $status)
    {
        try {
            error_log('[DocumentSendingService] Attempting to update document status: document_id=' . $document_id . ', status=' . $status);
            
            $sql = "UPDATE invoicing.documents SET document_status = :status, updated_at = NOW() WHERE document_id = :document_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            $rowCount = $stmt->rowCount();
            error_log('[DocumentSendingService] Update result: rows affected=' . $rowCount . ', success=' . ($result ? 'true' : 'false'));
            
            // Verify the update by checking the current status
            $verifySql = "SELECT document_status FROM invoicing.documents WHERE document_id = :document_id";
            $verifyStmt = $this->conn->prepare($verifySql);
            $verifyStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $verifyStmt->execute();
            $currentStatus = $verifyStmt->fetchColumn();
            error_log('[DocumentSendingService] Current document status after update: ' . $currentStatus);
            
        } catch (Exception $e) {
            error_log('[DocumentSendingService] Error updating document status: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log document sent
     */
    private function logDocumentSent($document_id, $recipient, $method, $success)
    {
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            $action = $success ? 'document_sent' : 'document_send_failed';
            $details = ucfirst($method) . " sent to: $recipient";
            $tech_id = isset($_SESSION['tech_id']) ? $_SESSION['tech_id'] : null;
            
            $sql = "INSERT INTO audit.user_actions (user_id, tech_id, module, action, related_type, related_id, old_data, new_data, details, ip_address, user_agent, session_id) VALUES (:user_id, :tech_id, :module, :action, :related_type, :related_id, :old_data, :new_data, :details, :ip_address, :user_agent, :session_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':tech_id', $tech_id, PDO::PARAM_INT);
            $stmt->bindValue(':module', 'invoicing');
            $stmt->bindValue(':action', $action);
            $stmt->bindValue(':related_type', 'document');
            $stmt->bindValue(':related_id', $document_id, PDO::PARAM_INT);
            $stmt->bindValue(':old_data', null);
            $stmt->bindValue(':new_data', null);
            $stmt->bindValue(':details', $details);
            $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
            $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);
            $stmt->bindValue(':session_id', session_id() ?: null);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("DocumentSendingService log error: " . $e->getMessage());
        }
    }
} 