<?php
error_log('[PAYMENT_API] payment-api.php accessed - ' . date('Y-m-d H:i:s'));
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
require_once __DIR__ . '/../../../src/Helpers/helpers.php';

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;
require_once __DIR__ . '/../controllers/PaymentController.php';

header('Content-Type: application/json');

function clean_numeric($value) {
    return is_null($value) ? 0 : floatval(preg_replace('/[^\d.\-]/', '', $value));
}

function clean_int($value) {
    return (is_numeric($value) && $value !== '') ? intval($value) : null;
}

try {
    if (!isset($_SESSION['account_number'])) {
        throw new Exception('User session not found');
    }
    
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    error_log('[PAYMENT_API] Action: ' . $action . ', Method: ' . $method);
    error_log('[PAYMENT_API] GET params: ' . json_encode($_GET));

    switch ($action) {
        case 'get_payments':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_payments action');
            }
            
            // Get all payments with document and client information
            $sql = "SELECT 
                        p.payment_id,
                        p.payment_date,
                        p.payment_amount,
                        p.payment_status,
                        p.payment_notes,
                        p.payment_reference,
                        p.created_at,
                        pm.method_name as payment_method_name,
                        d.document_id,
                        d.document_number,
                        d.document_type,
                        c.client_id,
                        c.client_name,
                        c.client_email
                    FROM invoicing.document_payments p
                    JOIN invoicing.documents d ON p.document_id = d.document_id
                    JOIN invoicing.clients c ON d.client_id = c.client_id
                    LEFT JOIN invoicing.payment_methods pm ON p.payment_method_id = pm.payment_method_id
                    WHERE p.deleted_at IS NULL 
                    AND d.deleted_at IS NULL
                    ORDER BY p.payment_date DESC, p.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $payments,
                'message' => 'Payments retrieved successfully'
            ]);
            break;
            
        case 'get_document_for_payment':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_document_for_payment action');
            }
            
            $document_id = isset($_GET['document_id']) ? intval($_GET['document_id']) : null;
            if (!$document_id) {
                throw new Exception('Document ID is required');
            }
            
            // Get document details with client information
            $sql = "SELECT 
                        d.document_id,
                        d.document_number,
                        d.document_type,
                        d.issue_date,
                        d.total_amount,
                        d.balance_due,
                        d.total_paid,
                        d.document_status,
                        c.client_id,
                        c.client_name,
                        c.client_email,
                        c.client_cell,
                        c.client_tell,
                        c.vat_number,
                        a.address_line1,
                        a.address_line2,
                        a.city,
                        a.suburb,
                        a.province,
                        a.country,
                        a.postal_code
                    FROM invoicing.documents d
                    JOIN invoicing.clients c ON d.client_id = c.client_id
                    LEFT JOIN invoicing.client_addresses ca ON ca.client_id = c.client_id 
                        AND ca.address_id = (SELECT address_id FROM invoicing.client_addresses ca2 WHERE ca2.client_id = c.client_id LIMIT 1)
                    LEFT JOIN invoicing.address a ON a.address_id = ca.address_id 
                        AND (a.deleted_at IS NULL OR a.deleted_at > NOW())
                    WHERE d.document_id = :document_id 
                    AND d.deleted_at IS NULL";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $stmt->execute();
            $document = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            // Validate that this document can accept payments
            $allowedTypes = ['invoice', 'vehicle_invoice', 'recurring_invoice'];
            $documentType = trim($document['document_type']);
            error_log('[PAYMENT_API] Document type: ' . $document['document_type'] . ', Trimmed: ' . $documentType . ', Allowed types: ' . json_encode($allowedTypes));
            error_log('[PAYMENT_API] Document type in allowed types: ' . (in_array($documentType, $allowedTypes) ? 'true' : 'false'));
            
            if (!in_array($documentType, $allowedTypes)) {
                throw new Exception('Payments can only be recorded for invoices. Document type: ' . $document['document_type']);
            }
            
            // Get payment history for this document
            $paymentHistorySql = "SELECT 
                                    dp.document_payment_id,
                                    dp.payment_date,
                                    dp.payment_amount,
                                    dp.payment_type,
                                    dp.payment_reference,
                                    dp.payment_notes,
                                    dp.created_at,
                                    pm.method_name,
                                    e.employee_first_name,
                                    e.employee_last_name
                                FROM invoicing.document_payments dp
                                LEFT JOIN invoicing.payment_methods pm ON dp.payment_method_id = pm.payment_method_id
                                LEFT JOIN core.employees e ON dp.created_by = e.employee_id
                                WHERE dp.document_id = :document_id 
                                AND dp.deleted_at IS NULL
                                ORDER BY dp.payment_date DESC, dp.created_at DESC";
            
            $paymentStmt = $conn->prepare($paymentHistorySql);
            $paymentStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $paymentStmt->execute();
            $paymentHistory = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $document['payment_history'] = $paymentHistory;
            
            echo json_encode([
                'success' => true,
                'message' => 'Document details retrieved successfully',
                'data' => $document
            ]);
            break;
            
        case 'record_payment':
            if ($method !== 'POST') {
                throw new Exception('POST method required for record_payment action');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            error_log('[PAYMENT_API] record_payment data: ' . json_encode($data));
            
            // Validate required fields
            $requiredFields = ['payment_amount', 'payment_date', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }
            
            $document_id = isset($data['document_id']) ? intval($data['document_id']) : null;
            $payment_amount = clean_numeric($data['payment_amount']);
            $payment_date = $data['payment_date'];
            $payment_method = $data['payment_method'];
            $payment_reference = $data['payment_reference'] ?? null;
            $payment_notes = $data['payment_notes'] ?? null;
            $allocation_type = $data['allocation_type'] ?? 'full';
            
            // Validate payment amount
            if ($payment_amount <= 0) {
                throw new Exception('Payment amount must be greater than zero');
            }
            
            // Get document details for validation
            $docSql = "SELECT 
                        document_id, 
                        document_type, 
                        total_amount, 
                        balance_due, 
                        total_paid,
                        document_status
                    FROM invoicing.documents 
                    WHERE document_id = :document_id 
                    AND deleted_at IS NULL";
            
            $docStmt = $conn->prepare($docSql);
            $docStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $docStmt->execute();
            $document = $docStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            // Validate document type
            $allowedTypes = ['invoice', 'vehicle_invoice', 'recurring_invoice'];
            $documentType = trim($document['document_type']);
            error_log('[PAYMENT_API] record_payment - Document type: ' . $document['document_type'] . ', Trimmed: ' . $documentType . ', Allowed types: ' . json_encode($allowedTypes));
            error_log('[PAYMENT_API] record_payment - Document type in allowed types: ' . (in_array($documentType, $allowedTypes) ? 'true' : 'false'));
            
            if (!in_array($documentType, $allowedTypes)) {
                throw new Exception('Payments can only be recorded for invoices. Document type: ' . $document['document_type']);
            }
            
            // Validate payment amount against balance
            $maxPaymentAmount = $document['balance_due'];
            if ($payment_amount > $maxPaymentAmount && $allocation_type !== 'overpayment') {
                throw new Exception("Payment amount (R{$payment_amount}) cannot exceed balance due (R{$maxPaymentAmount})");
            }
            
            // Get payment method ID
            $methodSql = "SELECT payment_method_id FROM invoicing.payment_methods WHERE method_code = :method_code";
            $methodStmt = $conn->prepare($methodSql);
            $methodStmt->bindValue(':method_code', $payment_method);
            $methodStmt->execute();
            $paymentMethod = $methodStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$paymentMethod) {
                throw new Exception('Invalid payment method');
            }
            
            $payment_method_id = $paymentMethod['payment_method_id'];
            
            try {
                $conn->beginTransaction();
                
                if ($document_id) {
                    // Payment linked to a document
                    // Get document details for validation
                    $docSql = "SELECT 
                                document_id, 
                                document_type, 
                                total_amount, 
                                balance_due, 
                                total_paid,
                                document_status
                            FROM invoicing.documents 
                            WHERE document_id = :document_id 
                            AND deleted_at IS NULL";
                    
                    $docStmt = $conn->prepare($docSql);
                    $docStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                    $docStmt->execute();
                    $document = $docStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$document) {
                        throw new Exception('Document not found');
                    }
                    
                    // Validate document type
                    $allowedTypes = ['invoice', 'vehicle_invoice', 'recurring_invoice'];
                    $documentType = trim($document['document_type']);
                    error_log('[PAYMENT_API] record_payment - Inner validation - Document type: ' . $document['document_type'] . ', Trimmed: ' . $documentType . ', Allowed types: ' . json_encode($allowedTypes));
                    error_log('[PAYMENT_API] record_payment - Inner validation - Document type in allowed types: ' . (in_array($documentType, $allowedTypes) ? 'true' : 'false'));
                    
                    if (!in_array($documentType, $allowedTypes)) {
                        throw new Exception('Payments can only be recorded for invoices. Document type: ' . $document['document_type']);
                    }
                    
                    // Validate payment amount against balance
                    $maxPaymentAmount = $document['balance_due'];
                    if ($payment_amount > $maxPaymentAmount && $allocation_type !== 'overpayment') {
                        throw new Exception("Payment amount (R{$payment_amount}) cannot exceed balance due (R{$maxPaymentAmount})");
                    }
                    
                    // Insert payment record with document link
                    $insertSql = "INSERT INTO invoicing.document_payments (
                                    document_id,
                                    payment_date,
                                    payment_amount,
                                    payment_type,
                                    payment_method_id,
                                    payment_reference,
                                    payment_notes,
                                    created_by,
                                    created_at,
                                    updated_at
                                ) VALUES (
                                    :document_id,
                                    :payment_date,
                                    :payment_amount,
                                    'payment',
                                    :payment_method_id,
                                    :payment_reference,
                                    :payment_notes,
                                    :created_by,
                                    NOW(),
                                    NOW()
                                ) RETURNING document_payment_id";
                    
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                    $insertStmt->bindValue(':payment_date', $payment_date);
                    $insertStmt->bindValue(':payment_amount', $payment_amount);
                    $insertStmt->bindValue(':payment_method_id', $payment_method_id, PDO::PARAM_INT);
                    $insertStmt->bindValue(':payment_reference', $payment_reference);
                    $insertStmt->bindValue(':payment_notes', $payment_notes);
                    $insertStmt->bindValue(':created_by', $_SESSION['user_id'] ?? 1, PDO::PARAM_INT);
                    $insertStmt->execute();
                    
                    $payment_id = $insertStmt->fetchColumn();
                    
                    // Update document status if payment covers full balance
                    $newBalance = $document['balance_due'] - $payment_amount;
                    $newStatus = $document['document_status'];
                    
                    if ($newBalance <= 0) {
                        $newStatus = 'paid';
                    } elseif ($document['document_status'] === 'draft') {
                        $newStatus = 'sent';
                    }
                    
                    // Update document status and balance
                    $updateDocSql = "UPDATE invoicing.documents 
                                    SET document_status = :status,
                                        updated_at = NOW(),
                                        updated_by = :updated_by
                                    WHERE document_id = :document_id";
                    
                    $updateDocStmt = $conn->prepare($updateDocSql);
                    $updateDocStmt->bindValue(':status', $newStatus);
                    $updateDocStmt->bindValue(':updated_by', $_SESSION['user_id'] ?? 1, PDO::PARAM_INT);
                    $updateDocStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                    $updateDocStmt->execute();
                    
                    // Get updated document details
                    $updatedDocSql = "SELECT 
                                        document_id,
                                        document_number,
                                        total_amount,
                                        balance_due,
                                        total_paid,
                                        document_status
                                    FROM invoicing.documents 
                                    WHERE document_id = :document_id";
                    
                    $updatedDocStmt = $conn->prepare($updatedDocSql);
                    $updatedDocStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                    $updatedDocStmt->execute();
                    $updatedDocument = $updatedDocStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $responseData = [
                        'payment_id' => $payment_id,
                        'document' => $updatedDocument,
                        'payment_amount' => $payment_amount,
                        'new_balance' => $newBalance,
                        'linked_to_document' => true
                    ];
                    
                } else {
                    // Standalone payment (not linked to any document)
                    $insertSql = "INSERT INTO invoicing.document_payments (
                                    payment_date,
                                    payment_amount,
                                    payment_type,
                                    payment_method_id,
                                    payment_reference,
                                    payment_notes,
                                    created_by,
                                    created_at,
                                    updated_at
                                ) VALUES (
                                    :payment_date,
                                    :payment_amount,
                                    'standalone_payment',
                                    :payment_method_id,
                                    :payment_reference,
                                    :payment_notes,
                                    :created_by,
                                    NOW(),
                                    NOW()
                                ) RETURNING document_payment_id";
                    
                    $insertStmt = $conn->prepare($insertSql);
                    $insertStmt->bindValue(':payment_date', $payment_date);
                    $insertStmt->bindValue(':payment_amount', $payment_amount);
                    $insertStmt->bindValue(':payment_method_id', $payment_method_id, PDO::PARAM_INT);
                    $insertStmt->bindValue(':payment_reference', $payment_reference);
                    $insertStmt->bindValue(':payment_notes', $payment_notes);
                    $insertStmt->bindValue(':created_by', $_SESSION['user_id'] ?? 1, PDO::PARAM_INT);
                    $insertStmt->execute();
                    
                    $payment_id = $insertStmt->fetchColumn();
                    
                    $responseData = [
                        'payment_id' => $payment_id,
                        'payment_amount' => $payment_amount,
                        'linked_to_document' => false
                    ];
                }
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'data' => $responseData
                ]);
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw new Exception('Failed to record payment: ' . $e->getMessage());
            }
            break;
            
        case 'get_payment_history':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_payment_history action');
            }
            
            $document_id = isset($_GET['document_id']) ? intval($_GET['document_id']) : null;
            $date_from = $_GET['date_from'] ?? null;
            $date_to = $_GET['date_to'] ?? null;
            $payment_method = $_GET['payment_method'] ?? null;
            $status = $_GET['status'] ?? null;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
            
            $offset = ($page - 1) * $limit;
            
            // Build query
            $sql = "SELECT 
                        dp.document_payment_id,
                        dp.document_id,
                        dp.payment_date,
                        dp.payment_amount,
                        dp.payment_type,
                        dp.payment_reference,
                        dp.payment_notes,
                        dp.created_at,
                        pm.method_name,
                        pm.method_code,
                        d.document_number,
                        d.document_type,
                        c.client_name,
                        e.employee_first_name,
                        e.employee_last_name
                    FROM invoicing.document_payments dp
                    LEFT JOIN invoicing.payment_methods pm ON dp.payment_method_id = pm.payment_method_id
                    LEFT JOIN invoicing.documents d ON dp.document_id = d.document_id
                    LEFT JOIN invoicing.clients c ON d.client_id = c.client_id
                    LEFT JOIN core.employees e ON dp.created_by = e.employee_id
                    WHERE dp.deleted_at IS NULL";
            
            $params = [];
            
            if ($document_id) {
                $sql .= " AND dp.document_id = :document_id";
                $params[':document_id'] = $document_id;
            }
            
            if ($date_from) {
                $sql .= " AND dp.payment_date >= :date_from";
                $params[':date_from'] = $date_from;
            }
            
            if ($date_to) {
                $sql .= " AND dp.payment_date <= :date_to";
                $params[':date_to'] = $date_to;
            }
            
            if ($payment_method) {
                $sql .= " AND pm.method_code = :payment_method";
                $params[':payment_method'] = $payment_method;
            }
            
            if ($status) {
                $sql .= " AND dp.payment_type = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY dp.payment_date DESC, dp.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM invoicing.document_payments dp
                        LEFT JOIN invoicing.payment_methods pm ON dp.payment_method_id = pm.payment_method_id
                        WHERE dp.deleted_at IS NULL";
            
            if ($document_id) $countSql .= " AND dp.document_id = :document_id";
            if ($date_from) $countSql .= " AND dp.payment_date >= :date_from";
            if ($date_to) $countSql .= " AND dp.payment_date <= :date_to";
            if ($payment_method) $countSql .= " AND pm.method_code = :payment_method";
            if ($status) $countSql .= " AND dp.payment_type = :status";
            
            $countStmt = $conn->prepare($countSql);
            foreach ($params as $key => $value) {
                if ($key !== ':limit' && $key !== ':offset') {
                    $countStmt->bindValue($key, $value);
                }
            }
            $countStmt->execute();
            $total = $countStmt->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment history retrieved successfully',
                'data' => $payments,
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit
            ]);
            break;
            
        case 'get_payment_methods':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_payment_methods action');
            }
            
            $sql = "SELECT 
                        payment_method_id,
                        method_name,
                        method_code,
                        is_active
                    FROM invoicing.payment_methods 
                    WHERE is_active = true 
                    ORDER BY method_name";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment methods retrieved successfully',
                'data' => $methods
            ]);
            break;
            
        case 'delete_payment':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete_payment action');
            }
            
            $payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : null;
            if (!$payment_id) {
                throw new Exception('Payment ID is required');
            }
            
            // Check if payment exists and can be deleted
            $checkSql = "SELECT 
                            dp.document_payment_id,
                            dp.document_id,
                            dp.payment_amount,
                            d.document_status
                        FROM invoicing.document_payments dp
                        JOIN invoicing.documents d ON dp.document_id = d.document_id
                        WHERE dp.document_payment_id = :payment_id 
                        AND dp.deleted_at IS NULL";
            
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
            $checkStmt->execute();
            $payment = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$payment) {
                throw new Exception('Payment not found');
            }
            
            // Only allow deletion if document is not paid
            if ($payment['document_status'] === 'paid') {
                throw new Exception('Cannot delete payment for a paid document');
            }
            
            try {
                $conn->beginTransaction();
                
                // Soft delete the payment
                $deleteSql = "UPDATE invoicing.document_payments 
                             SET deleted_at = NOW() 
                             WHERE document_payment_id = :payment_id";
                
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
                $deleteStmt->execute();
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment deleted successfully',
                    'data' => ['payment_id' => $payment_id]
                ]);
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw new Exception('Failed to delete payment: ' . $e->getMessage());
            }
            break;
            
        case 'validate_payment':
            if ($method !== 'POST') {
                throw new Exception('POST method required for validate_payment action');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $document_id = intval($data['document_id'] ?? 0);
            $payment_amount = clean_numeric($data['payment_amount'] ?? 0);
            $allocation_type = $data['allocation_type'] ?? 'full';
            
            if (!$document_id || $payment_amount <= 0) {
                throw new Exception('Invalid document ID or payment amount');
            }
            
            // Get document details
            $docSql = "SELECT 
                        document_id,
                        document_type,
                        total_amount,
                        balance_due,
                        document_status
                    FROM invoicing.documents 
                    WHERE document_id = :document_id 
                    AND deleted_at IS NULL";
            
            $docStmt = $conn->prepare($docSql);
            $docStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $docStmt->execute();
            $document = $docStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            // Validate document type
            $allowedTypes = ['invoice', 'vehicle_invoice', 'recurring_invoice'];
            if (!in_array($document['document_type'], $allowedTypes)) {
                throw new Exception('Payments can only be recorded for invoices');
            }
            
            // Validate payment amount
            $maxPaymentAmount = $document['balance_due'];
            $validation = [
                'is_valid' => true,
                'errors' => [],
                'warnings' => [],
                'max_amount' => $maxPaymentAmount,
                'new_balance' => $maxPaymentAmount - $payment_amount
            ];
            
            if ($payment_amount > $maxPaymentAmount && $allocation_type !== 'overpayment') {
                $validation['is_valid'] = false;
                $validation['errors'][] = "Payment amount (R{$payment_amount}) cannot exceed balance due (R{$maxPaymentAmount})";
            }
            
            if ($payment_amount > $maxPaymentAmount && $allocation_type === 'overpayment') {
                $validation['warnings'][] = "This payment exceeds the balance due and will create a credit";
            }
            
            if ($document['document_status'] === 'draft') {
                $validation['warnings'][] = "Recording payment will change document status from draft to sent";
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment validation completed',
                'data' => $validation
            ]);
            break;
            
        case 'link_payment_to_invoice':
            if ($method !== 'POST') {
                throw new Exception('POST method required for link_payment_to_invoice action');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $payment_id = intval($data['payment_id'] ?? 0);
            $document_id = intval($data['document_id'] ?? 0);
            
            if (!$payment_id || !$document_id) {
                throw new Exception('Payment ID and Document ID are required');
            }
            
            // Check if payment exists and is standalone
            $paymentSql = "SELECT 
                            document_payment_id,
                            payment_amount,
                            payment_type,
                            document_id
                        FROM invoicing.document_payments 
                        WHERE document_payment_id = :payment_id 
                        AND deleted_at IS NULL";
            
            $paymentStmt = $conn->prepare($paymentSql);
            $paymentStmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
            $paymentStmt->execute();
            $payment = $paymentStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$payment) {
                throw new Exception('Payment not found');
            }
            
            if ($payment['document_id']) {
                throw new Exception('Payment is already linked to a document');
            }
            
            // Check if document exists and can accept payments
            $docSql = "SELECT 
                        document_id,
                        document_type,
                        balance_due,
                        document_status
                    FROM invoicing.documents 
                    WHERE document_id = :document_id 
                    AND deleted_at IS NULL";
            
            $docStmt = $conn->prepare($docSql);
            $docStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $docStmt->execute();
            $document = $docStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$document) {
                throw new Exception('Document not found');
            }
            
            $allowedTypes = ['invoice', 'vehicle_invoice', 'recurring_invoice'];
            if (!in_array($document['document_type'], $allowedTypes)) {
                throw new Exception('Payments can only be linked to invoices');
            }
            
            if ($document['document_status'] === 'paid') {
                throw new Exception('Cannot link payment to a fully paid document');
            }
            
            try {
                $conn->beginTransaction();
                
                // Link payment to document
                $linkSql = "UPDATE invoicing.document_payments 
                           SET document_id = :document_id,
                               payment_type = 'payment',
                               updated_at = NOW()
                           WHERE document_payment_id = :payment_id";
                
                $linkStmt = $conn->prepare($linkSql);
                $linkStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $linkStmt->bindValue(':payment_id', $payment_id, PDO::PARAM_INT);
                $linkStmt->execute();
                
                // Update document status
                $newBalance = $document['balance_due'] - $payment['payment_amount'];
                $newStatus = $document['document_status'];
                
                if ($newBalance <= 0) {
                    $newStatus = 'paid';
                } elseif ($document['document_status'] === 'draft') {
                    $newStatus = 'sent';
                }
                
                $updateDocSql = "UPDATE invoicing.documents 
                                SET document_status = :status,
                                    updated_at = NOW(),
                                    updated_by = :updated_by
                                WHERE document_id = :document_id";
                
                $updateDocStmt = $conn->prepare($updateDocSql);
                $updateDocStmt->bindValue(':status', $newStatus);
                $updateDocStmt->bindValue(':updated_by', $_SESSION['user_id'] ?? 1, PDO::PARAM_INT);
                $updateDocStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $updateDocStmt->execute();
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment linked to invoice successfully',
                    'data' => [
                        'payment_id' => $payment_id,
                        'document_id' => $document_id,
                        'new_balance' => $newBalance,
                        'new_status' => $newStatus
                    ]
                ]);
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw new Exception('Failed to link payment: ' . $e->getMessage());
            }
            break;
            
        case 'get_unlinked_payments':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_unlinked_payments action');
            }
            
            $sql = "SELECT 
                        dp.document_payment_id,
                        dp.payment_date,
                        dp.payment_amount,
                        dp.payment_reference,
                        dp.payment_notes,
                        dp.created_at,
                        pm.method_name as payment_method_name
                    FROM invoicing.document_payments dp
                    LEFT JOIN invoicing.payment_methods pm ON dp.payment_method_id = pm.payment_method_id
                    WHERE dp.document_id IS NULL 
                    AND dp.deleted_at IS NULL
                    AND dp.payment_type = 'standalone_payment'
                    ORDER BY dp.payment_date DESC, dp.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Unlinked payments retrieved successfully',
                'data' => $payments
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => null
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
} 
