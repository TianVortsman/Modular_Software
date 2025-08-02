<?php
namespace App\modules\invoice\controllers;

// Helper function to clean numeric values from currency formatting
function clean_numeric($value) {
    if (is_numeric($value)) {
        return (float) $value;
    }
    
    if (is_string($value)) {
        // Remove currency symbols, spaces, and commas
        $cleaned = preg_replace('/[^\d.-]/', '', $value);
        return is_numeric($cleaned) ? (float) $cleaned : 0.0;
    }
    
    return 0.0;
}

use PDO;
use Exception;
use PDOException;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';

function list_documents(array $options = []): array {
    global $conn;
    // Extract and sanitize parameters
    $search   = $options['search']    ?? null;
    $type     = $options['type']      ?? null;
    $status   = $options['status']    ?? null;
    $dateFrom = $options['date_from'] ?? null;
    $dateTo   = $options['date_to']   ?? null;
    $clientId = $options['client_id'] ?? null;
    $page     = (int)($options['page'] ?? 1);
    $limit    = (int)($options['limit'] ?? 20);
    $sortBy   = $options['sort_by']   ?? 'document_id';
    $sortDir  = strtolower($options['sort_dir'] ?? 'desc');
    // Whitelist sorting fields
    $allowedSortFields = ['document_date', 'document_id'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'document_id';
    }
    $allowedSortDir = ['asc', 'desc'];
    if (!in_array($sortDir, $allowedSortDir)) {
        $sortDir = 'desc';
    }
    $offset = ($page - 1) * $limit;
    // Special logic for recurring invoices
    $isRecurringTab = ($type === 'recurring-invoice' || $type === 'recurring_invoice');
    if ($isRecurringTab) {
        $sql = "SELECT d.document_id, d.client_id, d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.total_amount, d.salesperson_id, c.client_id, c.client_name, e.employee_id, r.frequency, r.start_date, r.end_date, r.status as recurring_status
                FROM invoicing.documents d
                JOIN invoicing.clients c ON d.client_id = c.client_id
                JOIN core.employees e ON d.salesperson_id = e.employee_id
                LEFT JOIN invoicing.recurring_invoices r ON d.recurring_template_id = r.recurring_id
                WHERE d.is_recurring = TRUE AND d.document_type = 'recurring_invoice'";
    } else {
        $sql = "SELECT d.document_id, d.client_id, d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.total_amount, d.salesperson_id, c.client_id, c.client_name, e.employee_id
                FROM invoicing.documents d
                JOIN invoicing.clients c ON d.client_id = c.client_id
                JOIN core.employees e ON d.salesperson_id = e.employee_id
                WHERE 1=1";
    }
    $params = [];
    if (!empty($type) && !$isRecurringTab) {
        $sql .= " AND d.document_type = :type";
        $params[':type'] = $type;
    }
    if (!empty($status)) {
        $sql .= " AND d.document_status = :status";
        $params[':status'] = $status;
    }
    if (!empty($dateFrom)) {
        $sql .= " AND d.issue_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    if (!empty($dateTo)) {
        $sql .= " AND d.issue_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    if (!empty($clientId)) {
        $sql .= " AND d.client_id = :client_id";
        $params[':client_id'] = $clientId;
    }
    if (!empty($search)) {
        $sql .= " AND (c.client_name ILIKE :search OR d.document_number ILIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    $sql .= " ORDER BY $sortBy $sortDir LIMIT :limit OFFSET :offset";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Get total count for pagination
        $countSql = $isRecurringTab
            ? "SELECT COUNT(*) FROM invoicing.documents d WHERE d.is_recurring = TRUE AND d.document_type = 'recurring_invoice'"
            : "SELECT COUNT(*) FROM invoicing.documents d WHERE 1=1";
        if (!empty($type) && !$isRecurringTab) $countSql .= " AND d.document_type = :type";
        if (!empty($status)) $countSql .= " AND d.document_status = :status";
        if (!empty($dateFrom)) $countSql .= " AND d.issue_date >= :date_from";
        if (!empty($dateTo)) $countSql .= " AND d.issue_date <= :date_to";
        if (!empty($clientId)) $countSql .= " AND d.client_id = :client_id";
        if (!empty($search)) $countSql .= " AND (d.document_number ILIKE :search)";
        $countStmt = $conn->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        return [
            'success' => true,
            'message' => 'Documents retrieved successfully',
            'data' => $data,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ];
    } catch (PDOException $e) {
        $msg = "Query failed: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'list_documents', null, $msg);
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data' => null,
            'error_code' => 'DOCUMENT_LIST_ERROR'
        ];
    }
}

function get_document_details(int $document_id): array {
    global $conn;
    $sql = "SELECT d.document_id, d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.salesperson_id, d.subtotal, d.discount_amount, d.tax_amount, d.total_amount, d.client_purchase_order_number, d.notes, d.terms_conditions, d.is_recurring, d.recurring_template_id, d.requires_approval, d.approved_by, d.approved_at, d.salesperson_id, c.client_id, c.client_type, c.client_name, c.client_email, c.client_cell, c.client_tell, c.first_name, c.last_name, c.registration_number, c.vat_number, e.employee_id, e.employee_first_name, e.employee_last_name, a.address_line1, a.address_line2, a.city, a.suburb, a.province, a.country, a.postal_code FROM invoicing.documents d JOIN invoicing.clients c ON d.client_id = c.client_id JOIN core.employees e ON d.salesperson_id = e.employee_id LEFT JOIN invoicing.client_addresses ca ON ca.client_id = c.client_id AND ca.address_id = (SELECT address_id FROM invoicing.client_addresses ca2 WHERE ca2.client_id = c.client_id LIMIT 1) LEFT JOIN invoicing.address a ON a.address_id = ca.address_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW()) WHERE d.document_id = :document_id LIMIT 1";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            $msg = 'Document not found';
            error_log($msg);
            log_user_action(null, 'get_document_details', $document_id, $msg);
            return [
                'success' => false,
                'message' => $msg,
                'data' => null,
                'error_code' => 'DOCUMENT_NOT_FOUND'
            ];
        }
        $result['items'] = get_document_items($document_id)['data'] ?? [];
        if (!empty($result['is_recurring'])) {
            $recurringSql = "SELECT * FROM invoicing.recurring_invoices WHERE client_id = :client_id AND status = 'active' ORDER BY created_at DESC LIMIT 1";
            $recurringStmt = $conn->prepare($recurringSql);
            $recurringStmt->bindValue(':client_id', $result['client_id'], PDO::PARAM_INT);
            $recurringStmt->execute();
            $result['recurring'] = $recurringStmt->fetch(PDO::FETCH_ASSOC);
        }
        return [
            'success' => true,
            'message' => 'Document details retrieved successfully',
            'data' => $result
        ];
    } catch (PDOException $e) {
        $msg = "Error in get_document_details: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'get_document_details', $document_id, $msg);
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data' => null,
            'error_code' => 'DOCUMENT_DETAILS_ERROR'
        ];
    }
}

function get_document_items(int $document_id): array {
    global $conn;
    $sql = "SELECT i.item_id, i.document_id, i.product_id, i.product_description, i.quantity, i.unit_price, i.discount_percentage, i.tax_rate_id, i.line_total, i.sku AS item_code, tr.rate FROM invoicing.document_items i LEFT JOIN core.tax_rates tr ON i.tax_rate_id = tr.tax_rate_id WHERE i.document_id = :document_id LIMIT 100";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'message' => 'Document items retrieved successfully',
            'data' => $results ?: []
        ];
    } catch (PDOException $e) {
        $msg = "Error in get_document_items: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'get_document_items', $document_id, $msg);
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data' => null,
            'error_code' => 'DOCUMENT_ITEMS_ERROR'
        ];
    }
}

function create_document(array $options): array {
    global $conn;
    $documentData = $options['documentData'] ?? $options;
    // Map 'standard-invoice' to 'invoice' and 'recurring-invoice' to 'recurring_invoice' for DB compatibility
    if (isset($documentData['document_type'])) {
        if ($documentData['document_type'] === 'standard-invoice') {
            $documentData['document_type'] = 'invoice';
        } else if ($documentData['document_type'] === 'recurring-invoice') {
            $documentData['document_type'] = 'recurring_invoice';
        }
    }
    $items = $options['items'] ?? [];
    // Check for mode in options first, then in documentData, then default to draft
    $mode = $options['mode'] ?? $documentData['mode'] ?? 'draft';
    // Always get user_id from payload or session
    $user_id = $documentData['created_by'] ?? ($_SESSION['user_id'] ?? null);
    
    // If no user_id, try to get it from session or set a default for technicians
    if (empty($user_id)) {
        if (!empty($_SESSION['tech_logged_in'])) {
            $user_id = $_SESSION['tech_id'] ?? 9999; // Default tech user ID
        } else if (!empty($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        } else {
            // Fallback - allow creation but log warning
            error_log('[CREATE_DOCUMENT] Warning: No user_id found, using fallback');
            $user_id = 1; // Default fallback user ID
        }
    }
    
    // Permission check
    if (!check_user_permission($user_id, 'create_document')) {
        $msg = "Permission denied for user $user_id to create document";
        error_log($msg);
        log_user_action($user_id, 'create_document', null, $msg);
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        return build_error_response($msg, $documentData, 'Document creation permission check', 'PERMISSION_DENIED');
    }
    
    // Validate required fields
    $requiredFields = ['client_id', 'document_type', 'issue_date', 'subtotal', 'tax_amount', 'total_amount'];
    require_once __DIR__ . '/../../../src/Helpers/helpers.php';
    $validation = validate_required_fields($documentData, $requiredFields, 'document creation');
    if ($validation) {
        return $validation;
    }
    
    // Validate related document for credit notes and refunds
    if (in_array($documentData['document_type'], ['credit_note', 'refund'])) {
        if (empty($documentData['related_document_id'])) {
            return build_error_response(
                'Related document ID is required for ' . $documentData['document_type'] . 's', 
                $documentData, 
                'Document creation validation', 
                'RELATED_DOCUMENT_REQUIRED'
            );
        }
        
        // Validate that the related document exists and is an invoice type
        $relatedDocStmt = $conn->prepare("
            SELECT document_id, document_type, total_amount, balance_due, document_status 
            FROM invoicing.documents 
            WHERE document_id = :related_document_id AND deleted_at IS NULL
        ");
        $relatedDocStmt->bindValue(':related_document_id', $documentData['related_document_id'], PDO::PARAM_INT);
        $relatedDocStmt->execute();
        $relatedDoc = $relatedDocStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$relatedDoc) {
            return build_error_response(
                'Related document not found', 
                $documentData, 
                'Document creation validation', 
                'RELATED_DOCUMENT_NOT_FOUND'
            );
        }
        
        // Only allow credit notes and refunds for invoice types
        $allowedTypes = ['invoice', 'vehicle_invoice', 'recurring_invoice'];
        if (!in_array($relatedDoc['document_type'], $allowedTypes)) {
            return build_error_response(
                'Credit notes and refunds can only be created for invoices, vehicle invoices, or recurring invoices', 
                $documentData, 
                'Document creation validation', 
                'INVALID_RELATED_DOCUMENT_TYPE'
            );
        }
        
        // Validate credit note/refund amount
        $maxAmount = $documentData['document_type'] === 'credit_note' ? 
            $relatedDoc['total_amount'] : // Credit note can't exceed original invoice total
            $relatedDoc['total_amount'] - $relatedDoc['balance_due']; // Refund can't exceed paid amount
        
        if ($documentData['total_amount'] > $maxAmount) {
            return build_error_response(
                ucfirst($documentData['document_type']) . ' amount cannot exceed ' . number_format($maxAmount, 2), 
                $documentData, 
                'Document creation validation', 
                'INVALID_AMOUNT'
            );
        }
    }
    // Always set created_by from session if not set
    if (!isset($documentData['created_by']) && isset($_SESSION['user_id'])) {
        $documentData['created_by'] = $_SESSION['user_id'];
    }
    // Default discount_amount to 0 if not set
    if (!isset($documentData['discount_amount'])) {
        $documentData['discount_amount'] = 0;
    }
    // For drafts, set balance_due to total_amount if not set
    if ($mode === 'draft' && (!isset($documentData['balance_due']) || $documentData['balance_due'] === '')) {
        $documentData['balance_due'] = $documentData['total_amount'] ?? 0;
    }

    try {
        $conn->beginTransaction();

        // Handle draft vs finalize
        if ($mode === 'draft') {
            // Generate a draft document_number (e.g., "DRAFT-YYYYMMDD-HHMMSS")
            $draftNumber = 'DRAFT-' . date('Ymd-His');
            $document_number = $draftNumber;
            $document_status = 'draft';
        } elseif ($mode === 'finalize') {
            // Fetch and increment document number from settings.invoice_settings
            $type = strtolower($documentData['document_type']);
            $numberField = '';
            $prefixField = '';
            switch ($type) {
                case 'quotation':
                case 'vehicle-quotation':
                    $numberField = 'quotation_current_number';
                    $prefixField = 'quotation_prefix';
                    break;
                case 'invoice':
                case 'standard-invoice':
                case 'vehicle-invoice':
                case 'recurring-invoice':
                    $numberField = 'invoice_current_number';
                    $prefixField = 'invoice_prefix';
                    break;
                case 'credit-note':
                    $numberField = 'credit_note_current_number';
                    $prefixField = 'credit_note_prefix';
                    break;
                case 'pro-forma':
                    $numberField = 'proforma_current_number';
                    $prefixField = 'proforma_prefix';
                    break;
                default:
                    $numberField = 'invoice_current_number';
                    $prefixField = 'invoice_prefix';
            }
            // Lock row for update
            $stmt = $conn->prepare("SELECT $prefixField, $numberField FROM settings.invoice_settings WHERE id = 1 FOR UPDATE");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $prefix = $row[$prefixField] ?? '';
            $current = $row[$numberField] ?? 0;
            $next = ($current > 0) ? $current + 1 : 1;
            $document_number = $prefix . $next;
            // Increment the number in settings
            $update = $conn->prepare("UPDATE settings.invoice_settings SET $numberField = :next WHERE id = 1");
            $update->bindValue(':next', $next, PDO::PARAM_INT);
            $update->execute();
            // Use the document_status from form data if provided and it's 'Unpaid' (finalized), otherwise use options status or default to 'Unpaid'
            $document_status = (!empty($documentData['document_status']) && $documentData['document_status'] === 'Unpaid') 
                ? 'Unpaid' 
                : (!empty($options['status']) ? $options['status'] : 'Unpaid');
        } else {
            throw new Exception("Invalid mode for create_document: $mode");
        }

        // Recurring logic: insert recurring_invoices first if needed - only for actual recurring_invoice document types
        $recurring_id = null;
        if (!empty($documentData['is_recurring']) && $documentData['document_type'] === 'recurring_invoice') {
            $frequency = $documentData['frequency'] ?? null;
            $start_date = $documentData['start_date'] ?? null;
            $end_date = $documentData['end_date'] ?? null;
            if (empty($frequency) || empty($start_date)) {
                throw new Exception("Missing required recurring invoice fields: frequency and start_date are required.");
            }
            $recurringSql = "INSERT INTO invoicing.recurring_invoices (
                client_id, frequency, start_date, end_date, status, created_at, updated_at
            ) VALUES (
                :client_id, :frequency, :start_date, :end_date, 'active', NOW(), NOW()
            ) RETURNING recurring_id";
            $recurringStmt = $conn->prepare($recurringSql);
            $recurringStmt->bindValue(':client_id', $documentData['client_id'] ?? null, PDO::PARAM_INT);
            $recurringStmt->bindValue(':frequency', $frequency);
            // Handle recurring date fields - convert empty strings to null
            $start_date_clean = !empty($start_date) ? $start_date : null;
            $end_date_clean = !empty($end_date) ? $end_date : null;
            
            $recurringStmt->bindValue(':start_date', $start_date_clean);
            $recurringStmt->bindValue(':end_date', $end_date_clean);
            $recurringStmt->execute();
            $recurring_id = $recurringStmt->fetchColumn();
            $documentData['is_recurring'] = true;
            $documentData['recurring_template_id'] = $recurring_id;
        } else {
            $documentData['is_recurring'] = false;
            $documentData['recurring_template_id'] = null;
        }

        // Prepare insert for documents table
        $sql = "INSERT INTO invoicing.documents (
                    client_id, document_type, document_number, issue_date, due_date, document_status, 
                    salesperson_id, subtotal, discount_amount, tax_amount, total_amount, balance_due, 
                    client_purchase_order_number, notes, terms_conditions, is_recurring, recurring_template_id, 
                    related_document_id, requires_approval, created_by, created_at, updated_at
                ) VALUES (
                    :client_id, :document_type, :document_number, :issue_date, :due_date, :document_status, 
                    :salesperson_id, :subtotal, :discount_amount, :tax_amount, :total_amount, :balance_due, 
                    :client_purchase_order_number, :notes, :terms_conditions, :is_recurring, :recurring_template_id, 
                    :related_document_id, :requires_approval, :created_by, NOW(), NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $documentData['client_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':document_type', $documentData['document_type'] ?? 'invoice');
        $stmt->bindValue(':document_number', $document_number);
        // Handle date fields - convert empty strings to null for database
        $issue_date = !empty($documentData['issue_date']) ? $documentData['issue_date'] : null;
        $due_date = !empty($documentData['due_date']) ? $documentData['due_date'] : null;
        
        $stmt->bindValue(':issue_date', $issue_date);
        $stmt->bindValue(':due_date', $due_date);
        $stmt->bindValue(':document_status', $document_status);
        $stmt->bindValue(':salesperson_id', isset($documentData['salesperson_id']) && is_numeric($documentData['salesperson_id']) ? $documentData['salesperson_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':subtotal', clean_numeric($documentData['subtotal'] ?? 0));
        $stmt->bindValue(':discount_amount', clean_numeric($documentData['discount_amount'] ?? 0));
        $stmt->bindValue(':tax_amount', clean_numeric($documentData['tax_amount'] ?? 0));
        $stmt->bindValue(':total_amount', clean_numeric($documentData['total_amount'] ?? 0));
        $stmt->bindValue(':balance_due', clean_numeric($documentData['balance_due'] ?? 0));
        $stmt->bindValue(':client_purchase_order_number', $documentData['client_purchase_order_number'] ?? null);
        $stmt->bindValue(':notes', $documentData['notes'] ?? null);
        $stmt->bindValue(':terms_conditions', $documentData['terms_conditions'] ?? null);
        $stmt->bindValue(':is_recurring', ($documentData['is_recurring'] ?? false) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':recurring_template_id', $documentData['recurring_template_id'] ?? null, is_null($documentData['recurring_template_id'] ?? null) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':related_document_id', $documentData['related_document_id'] ?? null, is_null($documentData['related_document_id'] ?? null) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':requires_approval', !empty($documentData['requires_approval'] ?? false) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $documentData['created_by'] ?? 1, PDO::PARAM_INT);

        $stmt->execute();
        $document_id = $conn->lastInsertId();
        
        // Update balance of related document if this is a credit note or refund
        if (in_array($documentData['document_type'], ['credit_note', 'refund']) && !empty($documentData['related_document_id'])) {
            $balanceUpdateSql = "UPDATE invoicing.documents SET 
                balance_due = CASE 
                    WHEN :document_type = 'credit_note' THEN balance_due - :amount
                    WHEN :document_type = 'refund' THEN balance_due + :amount
                END,
                updated_at = NOW()
                WHERE document_id = :related_document_id";
            
            $balanceStmt = $conn->prepare($balanceUpdateSql);
            $balanceStmt->bindValue(':document_type', $documentData['document_type']);
            $balanceStmt->bindValue(':amount', clean_numeric($documentData['total_amount']));
            $balanceStmt->bindValue(':related_document_id', $documentData['related_document_id'], PDO::PARAM_INT);
            $balanceStmt->execute();
            
            // Log the balance update
            log_user_action($user_id, 'update_related_document_balance', $documentData['related_document_id'], 
                "Updated balance for " . $documentData['document_type'] . " #$document_id");
        }
        
        // Increment document number counter if document is finalized (Unpaid status)
        if (($documentData['document_status'] ?? '') === 'Unpaid') {
            increment_document_number_counter($documentData['document_type'] ?? 'invoice');
        }

        // Insert document items
        if ($documentData['document_type'] === 'credit_note') {
            // Insert credit note items
            $itemSql = "INSERT INTO invoicing.document_items (
                            document_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total, sku, credit_type, credit_reason
                        ) VALUES (
                            :document_id, :product_id, :product_description, :quantity, :unit_price, :discount_percentage, :tax_rate_id, :line_total, :sku, :credit_type, :credit_reason
                        )";
            $itemStmt = $conn->prepare($itemSql);

            foreach ($items as $item) {
                // For credit notes, use credit_reason as product_description
                $item['product_id'] = isset($item['product_id']) && is_numeric($item['product_id']) ? $item['product_id'] : null;
                $item['product_description'] = $item['credit_reason'] ?? '';
                $item['quantity'] = 1; // Credit notes always have quantity 1
                $item['unit_price'] = isset($item['credit_amount']) && is_numeric($item['credit_amount']) ? $item['credit_amount'] : 0;
                $item['discount_percentage'] = 0;
                $item['tax_rate_id'] = null; // No tax on credit notes
                $item['line_total'] = isset($item['credit_amount']) && is_numeric($item['credit_amount']) ? $item['credit_amount'] : 0;
                $item['credit_type'] = $item['credit_type'] ?? 'reason';
                $item['credit_reason'] = $item['credit_reason'] ?? '';
                
                // Check for truly missing required fields
                if ($item['product_description'] === '') {
                    $msg = "Missing credit reason in one or more items.";
                    error_log($msg);
                    return [ 'success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'INVALID_CREDIT_REASON' ];
                }
                
                $itemStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
                $itemStmt->bindValue(':product_description', $item['product_description']);
                $itemStmt->bindValue(':quantity', clean_numeric($item['quantity']));
                $itemStmt->bindValue(':unit_price', clean_numeric($item['unit_price']));
                $itemStmt->bindValue(':discount_percentage', clean_numeric($item['discount_percentage']));
                $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'], PDO::PARAM_NULL);
                $itemStmt->bindValue(':line_total', clean_numeric($item['line_total']));
                $itemStmt->bindValue(':sku', '', PDO::PARAM_STR);
                $itemStmt->bindValue(':credit_type', $item['credit_type'], PDO::PARAM_STR);
                $itemStmt->bindValue(':credit_reason', $item['credit_reason'], PDO::PARAM_STR);
                $itemStmt->execute();
            }
        } else {
            // Insert regular document items
            $itemSql = "INSERT INTO invoicing.document_items (
                            document_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total, sku
                        ) VALUES (
                            :document_id, :product_id, :product_description, :quantity, :unit_price, :discount_percentage, :tax_rate_id, :line_total, :sku
                        )";
            $itemStmt = $conn->prepare($itemSql);

            foreach ($items as $item) {
                // Validate and default all required item fields
                $item['product_id'] = isset($item['product_id']) && is_numeric($item['product_id']) ? $item['product_id'] : null;
                $item['product_description'] = $item['product_description'] ?? '';
                $item['quantity'] = isset($item['quantity']) && is_numeric($item['quantity']) ? $item['quantity'] : 1;
                $item['unit_price'] = isset($item['unit_price']) && is_numeric($item['unit_price']) ? $item['unit_price'] : 0;
                $item['discount_percentage'] = (isset($item['discount_percentage']) && $item['discount_percentage'] !== '' && is_numeric($item['discount_percentage'])) ? $item['discount_percentage'] : 0;
                // If tax_rate_id is 0 or blank, set to null
                $item['tax_rate_id'] = (isset($item['tax_rate_id']) && is_numeric($item['tax_rate_id']) && $item['tax_rate_id'] > 0) ? $item['tax_rate_id'] : null;
                $item['line_total'] = isset($item['line_total']) && is_numeric($item['line_total']) ? $item['line_total'] : 0;
                // Check for truly missing required fields
                if ($item['product_id'] === null) {
                    $msg = "Missing or invalid product_id in one or more items.";
                    error_log($msg);
                    return [ 'success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'INVALID_PRODUCT_ID' ];
                }
                if ($item['product_description'] === '') {
                    $msg = "Missing product_description in one or more items.";
                    error_log($msg);
                    return [ 'success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'INVALID_PRODUCT_DESCRIPTION' ];
                }
                // All other fields are defaulted above
                $itemStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
                $itemStmt->bindValue(':product_description', $item['product_description']);
                $itemStmt->bindValue(':quantity', clean_numeric($item['quantity']));
                $itemStmt->bindValue(':unit_price', clean_numeric($item['unit_price']));
                $itemStmt->bindValue(':discount_percentage', clean_numeric($item['discount_percentage']));
                $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'], is_null($item['tax_rate_id']) ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $itemStmt->bindValue(':line_total', clean_numeric($item['line_total']));
                $itemStmt->bindValue(':sku', $item['item_code'] ?? '', PDO::PARAM_STR);
                $itemStmt->execute();
            }
        }

        $conn->commit();

        // After commit, generate PDF for finalized documents
        $pdf_url = null;
        if ($mode === 'finalize') {
            // Prepare data for PDF
            $pdfData = [
                'client' => [
                    'name' => $documentData['client_name'] ?? '',
                    'email' => $documentData['client_email'] ?? '',
                    'phone' => $documentData['client_phone'] ?? '',
                    'address1' => $documentData['address1'] ?? '',
                    'address2' => $documentData['address2'] ?? '',
                    'vat_number' => $documentData['vat_number'] ?? '',
                    'registration_number' => $documentData['registration_number'] ?? ''
                ],
                'items' => array_map(function($item) {
                    return [
                        'qty' => $item['quantity'] ?? 1,
                        'item_code' => $item['item_code'] ?? '',
                        'description' => $item['product_description'] ?? '',
                        'unit_price' => $item['unit_price'] ?? '',
                        'tax' => $item['tax_percentage'] ?? '',
                        'total' => $item['line_total'] ?? ''
                    ];
                }, $items),
                'totals' => [
                    'subtotal' => $documentData['subtotal'] ?? '',
                    'tax' => $documentData['tax_amount'] ?? '',
                    'total' => $documentData['total_amount'] ?? ''
                ],
                'notes' => array_filter([
                    $documentData['public_note'] ?? '',
                    $documentData['private_note'] ?? '',
                    $documentData['foot_note'] ?? ''
                ]),
                'invoice_number' => $document_number,
                'invoice_date' => $documentData['issue_date'] ?? '',
                'due_date' => $documentData['due_date'] ?? '',
                'salesperson' => [
                    'name' => $documentData['salesperson_name'] ?? '',
                    'email' => '' // Add if available
                ]
            ];
            $pdfRes = null;
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, __DIR__ . '/../api/generate-document-pdf.php');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pdfData));
                $pdfResRaw = curl_exec($ch);
                curl_close($ch);
                $pdfRes = json_decode($pdfResRaw, true);
                if ($pdfRes && !empty($pdfRes['success']) && !empty($pdfRes['url'])) {
                    $pdf_url = $pdfRes['url'];
                    // Save PDF path to document
                    $stmt = $conn->prepare('UPDATE invoicing.documents SET pdf_path = :pdf_path WHERE document_id = :document_id');
                    $stmt->bindValue(':pdf_path', $pdf_url);
                    $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            } catch (Exception $e) {
                error_log('[DOCUMENT PDF GENERATION ERROR] ' . $e->getMessage());
            }
        }

        // Logging and notification
        log_user_action($user_id, 'create_document', $document_id, json_encode($documentData));
        send_notification($user_id, "Document #$document_id created successfully.");
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        return build_success_response([
            'document_id' => (int)$document_id,
            'document_number' => $document_number,
            'document_status' => $document_status,
            'pdf_url' => $pdf_url
        ], 'Document created successfully');
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = 'Error creating document: ' . $e->getMessage();
        error_log('create_document error: ' . $e->getMessage());
        log_user_action($user_id, 'create_document', null, $msg);
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        return build_error_response($msg, $documentData, 'Document creation failed', 'DOCUMENT_CREATE_ERROR');
    }
}

function update_document(int $document_id, array $options): array {
    global $conn;
    $documentData = $options['documentData'] ?? [];
    // Map 'standard-invoice' to 'invoice' and 'recurring-invoice' to 'recurring_invoice' for DB compatibility
    if (isset($documentData['document_type'])) {
        if ($documentData['document_type'] === 'standard-invoice') {
            $documentData['document_type'] = 'invoice';
        } else if ($documentData['document_type'] === 'recurring-invoice') {
            $documentData['document_type'] = 'recurring_invoice';
        }
    }
    $items = $options['items'] ?? [];
    // Check for mode in options first, then in documentData, then default to draft
    $mode = $options['mode'] ?? $documentData['mode'] ?? 'draft';
    
    // Always get updated_by from payload or session
    $updated_by = $documentData['updated_by'] ?? ($_SESSION['user_id'] ?? null);
    
    // Debug logging
    error_log('[UPDATE_DOCUMENT] Permission check - updated_by: ' . ($updated_by ?? 'NULL') . ', tech_logged_in: ' . (empty($_SESSION['tech_logged_in']) ? 'false' : 'true') . ', user_id from session: ' . ($_SESSION['user_id'] ?? 'NULL'));
    
    // If no user_id, try to get it from session or set a default for technicians
    if (empty($updated_by)) {
        if (!empty($_SESSION['tech_logged_in'])) {
            $updated_by = $_SESSION['tech_id'] ?? 9999; // Default tech user ID
        } else if (!empty($_SESSION['user_id'])) {
            $updated_by = $_SESSION['user_id'];
        } else {
            // Fallback - allow update but log warning
            error_log('[UPDATE_DOCUMENT] Warning: No updated_by user found, using fallback');
            $updated_by = 1; // Default fallback user ID
        }
    }
    
    if (!check_user_permission($updated_by, 'update_document', $document_id)) {
        $msg = "Permission denied for user $updated_by to update document $document_id";
        error_log($msg);
        log_user_action($updated_by, 'update_document', $document_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    try {
        $conn->beginTransaction();

        // Always get current document info for response and validation
        $stmtCheck = $conn->prepare('SELECT document_number, document_status, document_type, is_recurring, recurring_template_id FROM invoicing.documents WHERE document_id = :document_id');
        $stmtCheck->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $currentDoc = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $currentNumber = $currentDoc['document_number'] ?? '';
        $currentStatus = $currentDoc['document_status'] ?? '';
        $currentType = $currentDoc['document_type'] ?? '';
            $currentIsRecurring = $currentDoc['is_recurring'] ?? false;
    $currentRecurringId = $currentDoc['recurring_template_id'] ?? null;

    // Initialize document_status at function level
    $document_status = 'draft'; // Default value

    // Handle draft vs finalize
    if ($mode === 'draft') {
        $document_status = 'draft';
        // Do not update document_number if it's a draft update
    } elseif ($mode === 'finalize') {
        // Use the document_status from form data if provided and it's 'Unpaid' (finalized), otherwise use options status or default to 'Unpaid'
        $document_status = (!empty($documentData['document_status']) && $documentData['document_status'] === 'Unpaid') 
            ? 'Unpaid' 
            : (!empty($options['status']) ? $options['status'] : 'Unpaid');
    } else {
        // Default case - use document_status from documentData or keep current status
        $document_status = $documentData['document_status'] ?? $currentStatus ?? 'draft';
    }
            // Prevent editing if already finalized
            $finalizedStatuses = ['unpaid', 'Unpaid', 'approved', 'paid', 'sent'];
            if (in_array($currentStatus, $finalizedStatuses)) {
                throw new Exception('Cannot edit a finalized document.');
            }
            // Assign a new document_number if not set or is a draft
            if (empty($currentNumber) || strpos($currentNumber, 'DRAFT-') === 0) {
                $type = strtolower($documentData['document_type'] ?? $currentType ?? 'invoice');
                $numberField = '';
                $prefixField = '';
                switch ($type) {
                    case 'quotation':
                    case 'vehicle-quotation':
                        $numberField = 'quotation_current_number';
                        $prefixField = 'quotation_prefix';
                        break;
                    case 'invoice':
                    case 'standard-invoice':
                    case 'vehicle-invoice':
                    case 'recurring-invoice':
                        $numberField = 'invoice_current_number';
                        $prefixField = 'invoice_prefix';
                        break;
                    case 'credit-note':
                        $numberField = 'credit_note_current_number';
                        $prefixField = 'credit_note_prefix';
                        break;
                    case 'pro-forma':
                        $numberField = 'proforma_current_number';
                        $prefixField = 'proforma_prefix';
                        break;
                    default:
                        $numberField = 'invoice_current_number';
                        $prefixField = 'invoice_prefix';
                }
                $stmt = $conn->prepare("SELECT $prefixField, $numberField FROM settings.invoice_settings WHERE id = 1 FOR UPDATE");
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $prefix = $row[$prefixField] ?? '';
                $current = $row[$numberField] ?? 0;
                $next = ($current > 0) ? $current + 1 : 1;
                $newNumber = $prefix . $next;
                $update = $conn->prepare("UPDATE settings.invoice_settings SET $numberField = :next WHERE id = 1");
                $update->bindValue(':next', $next, PDO::PARAM_INT);
                $update->execute();
                // Update the document_number in the DB
                $stmtNum = $conn->prepare('UPDATE invoicing.documents SET document_number = :document_number WHERE document_id = :document_id');
                $stmtNum->bindValue(':document_number', $newNumber);
                $stmtNum->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $stmtNum->execute();
                $currentNumber = $newNumber;
            }

        // Protect existing recurring invoices: ensure they stay recurring
        // Only apply this logic to actual recurring_invoice document types, not quotations or other types
        if ($currentIsRecurring && $currentType === 'recurring_invoice' && ($documentData['document_type'] ?? $currentType) === 'recurring_invoice') {
            // If document was previously recurring, preserve that status
            if (!isset($documentData['is_recurring']) || !$documentData['is_recurring']) {
                error_log('[UPDATE_DOCUMENT] Preserving recurring status for recurring invoice ID: ' . $document_id);
                $documentData['is_recurring'] = true;
            }
            // Ensure document_type stays as recurring_invoice
            if (!isset($documentData['document_type']) || $documentData['document_type'] !== 'recurring_invoice') {
                error_log('[UPDATE_DOCUMENT] Preserving document_type as recurring_invoice for ID: ' . $document_id);
                $documentData['document_type'] = 'recurring_invoice';
            }
            // Preserve existing recurring_template_id if not provided
            if (!isset($documentData['recurring_template_id']) && $currentRecurringId) {
                $documentData['recurring_template_id'] = $currentRecurringId;
            }
        } else if ($currentIsRecurring && $currentType !== 'recurring_invoice') {
            // If document was marked as recurring but is not a recurring_invoice type, clear the recurring status
            error_log('[UPDATE_DOCUMENT] Clearing recurring status for non-recurring document type ID: ' . $document_id . ' (type: ' . ($documentData['document_type'] ?? $currentType) . ')');
            $documentData['is_recurring'] = false;
            $documentData['recurring_template_id'] = null;
        }

        // Recurring update logic - only for actual recurring_invoice document types
        $recurring_id = null;
        if (!empty($documentData['is_recurring']) && ($documentData['document_type'] ?? $currentType) === 'recurring_invoice') {
            error_log('[UPDATE_DOCUMENT] Processing recurring invoice update for document ID: ' . $document_id);
            
            $frequency = $documentData['frequency'] ?? null;
            $start_date = $documentData['start_date'] ?? null;
            $end_date = $documentData['end_date'] ?? null;
            
            // If we have an existing recurring template, use its values as fallbacks
            if ($currentRecurringId && (!$frequency || !$start_date)) {
                $existingRecurringStmt = $conn->prepare('SELECT frequency, start_date, end_date FROM invoicing.recurring_invoices WHERE recurring_id = :recurring_id');
                $existingRecurringStmt->bindValue(':recurring_id', $currentRecurringId, PDO::PARAM_INT);
                $existingRecurringStmt->execute();
                $existingRecurring = $existingRecurringStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingRecurring) {
                    $frequency = $frequency ?: $existingRecurring['frequency'];
                    $start_date = $start_date ?: $existingRecurring['start_date'];
                    $end_date = $end_date ?: $existingRecurring['end_date'];
                }
            }
            
            if (empty($frequency) || empty($start_date)) {
                throw new Exception("Missing required recurring invoice fields: frequency and start_date are required.");
            }
            
            // Check if recurring template already exists, update or create
            if ($currentRecurringId) {
                // Update existing recurring template
                $recurringSql = "UPDATE invoicing.recurring_invoices 
                               SET frequency = :frequency, start_date = :start_date, end_date = :end_date, updated_at = NOW() 
                               WHERE recurring_id = :recurring_id";
                $recurringStmt = $conn->prepare($recurringSql);
                // Handle recurring date fields - convert empty strings to null
                $start_date_clean = !empty($start_date) ? $start_date : null;
                $end_date_clean = !empty($end_date) ? $end_date : null;
                
                $recurringStmt->bindValue(':frequency', $frequency);
                $recurringStmt->bindValue(':start_date', $start_date_clean);
                $recurringStmt->bindValue(':end_date', $end_date_clean);
                $recurringStmt->bindValue(':recurring_id', $currentRecurringId, PDO::PARAM_INT);
                $recurringStmt->execute();
                $recurring_id = $currentRecurringId;
            } else {
                // Create new recurring template
                $recurringSql = "INSERT INTO invoicing.recurring_invoices (client_id, frequency, start_date, end_date, status, created_at, updated_at)
                               VALUES (:client_id, :frequency, :start_date, :end_date, 'active', NOW(), NOW())
                               RETURNING recurring_id";
                $recurringStmt = $conn->prepare($recurringSql);
                // Handle recurring date fields - convert empty strings to null
                $start_date_clean = !empty($start_date) ? $start_date : null;
                $end_date_clean = !empty($end_date) ? $end_date : null;
                
                $recurringStmt->bindValue(':client_id', $documentData['client_id'] ?? null, PDO::PARAM_INT);
                $recurringStmt->bindValue(':frequency', $frequency);
                $recurringStmt->bindValue(':start_date', $start_date_clean);
                $recurringStmt->bindValue(':end_date', $end_date_clean);
                $recurringStmt->execute();
                $recurring_id = $recurringStmt->fetchColumn();
            }
            $documentData['is_recurring'] = true;
            $documentData['recurring_template_id'] = $recurring_id;
            
            error_log('[UPDATE_DOCUMENT] Recurring template updated/created with ID: ' . $recurring_id);
        } else {
            // Set to false for non-recurring documents
            $documentData['is_recurring'] = false;
            $documentData['recurring_template_id'] = null;
        }

        // Prepare update for documents table (partial update support)
        $fields = [
            'client_id' => [':client_id', PDO::PARAM_INT],
            'document_type' => [':document_type', PDO::PARAM_STR],
            'issue_date' => [':issue_date', PDO::PARAM_STR],
            'due_date' => [':due_date', PDO::PARAM_STR],
            'document_status' => [':document_status', PDO::PARAM_STR],
            'salesperson_id' => [':salesperson_id', PDO::PARAM_INT], // will bind as null if not set
            'subtotal' => [':subtotal', PDO::PARAM_STR],
            'discount_amount' => [':discount_amount', PDO::PARAM_STR],
            'tax_amount' => [':tax_amount', PDO::PARAM_STR],
            'total_amount' => [':total_amount', PDO::PARAM_STR],
            'balance_due' => [':balance_due', PDO::PARAM_STR],
            'client_purchase_order_number' => [':client_purchase_order_number', PDO::PARAM_STR],
            'notes' => [':notes', PDO::PARAM_STR],
            'terms_conditions' => [':terms_conditions', PDO::PARAM_STR],
            'is_recurring' => [':is_recurring', PDO::PARAM_INT],
            'recurring_template_id' => [':recurring_template_id', PDO::PARAM_INT],
            'related_document_id' => [':related_document_id', PDO::PARAM_INT],
            'requires_approval' => [':requires_approval', PDO::PARAM_INT],
        ];
        $setParts = [];
        foreach ($fields as $key => [$param, $type]) {
            if (isset($documentData[$key])) {
                $setParts[] = "$key = $param";
            }
        }
        $setParts[] = "document_status = :document_status";
        $setParts[] = "updated_at = NOW()";
        if ($updated_by !== null) {
            $setParts[] = "updated_by = :updated_by";
        }
        $sql = "UPDATE invoicing.documents SET " . implode(", ", $setParts) . " WHERE document_id = :document_id";
        $stmt = $conn->prepare($sql);
        foreach ($fields as $key => [$param, $type]) {
            // Only bind parameters that are actually in the SET clause
            if (in_array("$key = $param", $setParts)) {
                if ($key === 'salesperson_id') {
                    $stmt->bindValue($param, (isset($documentData['salesperson_id']) && is_numeric($documentData['salesperson_id'])) ? $documentData['salesperson_id'] : null, $type);
                } else if ($key === 'issue_date' || $key === 'due_date') {
                    // Handle date fields - convert empty strings to null for database
                    $date_value = !empty($documentData[$key]) ? $documentData[$key] : null;
                    $stmt->bindValue($param, $date_value);
                } else {
                    // Handle monetary fields with clean_numeric
                    if (in_array($key, ['subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'balance_due'])) {
                        $stmt->bindValue($param, clean_numeric($documentData[$key] ?? 0), $type);
                    } else {
                        $stmt->bindValue($param, $documentData[$key] ?? null, $type);
                    }
                }
            }
        }
        $stmt->bindValue(':document_status', $document_status);
        if ($updated_by !== null) {
            $stmt->bindValue(':updated_by', $updated_by, PDO::PARAM_INT);
        }
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();

        // --- Optimized document items update ---
        // Fetch current items
        $currentItems = [];
        $stmtItems = $conn->prepare("SELECT item_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total, sku FROM invoicing.document_items WHERE document_id = :document_id");
        $stmtItems->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmtItems->execute();
        foreach ($stmtItems->fetchAll(PDO::FETCH_ASSOC) as $ci) {
            $currentItems[$ci['item_id'] ?? 0] = $ci;
        }
        $newItemsById = [];
        foreach ($items as $item) {
            if (!empty($item['item_id'] ?? '')) {
                $newItemsById[$item['item_id'] ?? 0] = $item;
            }
        }
        // Update existing items
        foreach ($currentItems as $item_id => $ci) {
            if (isset($newItemsById[$item_id])) {
                $ni = $newItemsById[$item_id];
                // Compare fields, update if changed
                $fieldsToUpdate = [];
                foreach (['product_id','product_description','quantity','unit_price','discount_percentage','tax_rate_id','line_total'] as $field) {
                    if ($ci[$field] != ($ni[$field] ?? null)) {
                        $fieldsToUpdate[$field] = $ni[$field] ?? null;
                    }
                }
                // Special handling for sku field (maps to item_code from frontend)
                if ($ci['sku'] != ($ni['item_code'] ?? '')) {
                    $fieldsToUpdate['sku'] = $ni['item_code'] ?? '';
                }
                if ($fieldsToUpdate) {
                    $set = implode(', ', array_map(fn($f) => "$f = :$f", array_keys($fieldsToUpdate)));
                    $updateSql = "UPDATE invoicing.document_items SET $set WHERE item_id = :item_id";
                    $updateStmt = $conn->prepare($updateSql);
                                    foreach ($fieldsToUpdate as $f => $v) {
                    if ($f === 'sku') {
                        // Map item_code from frontend to sku in database
                        $updateStmt->bindValue(":$f", $ni['item_code'] ?? '', PDO::PARAM_STR);
                    } else if (in_array($f, ['quantity', 'unit_price', 'discount_percentage', 'line_total'])) {
                        // Handle monetary fields with clean_numeric
                        $updateStmt->bindValue(":$f", clean_numeric($v));
                    } else {
                        $updateStmt->bindValue(":$f", $v);
                    }
                }
                    $updateStmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                    $updateStmt->execute();
                }
            } else {
                // Item removed, delete
                $delStmt = $conn->prepare("DELETE FROM invoicing.document_items WHERE item_id = :item_id");
                $delStmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
                $delStmt->execute();
            }
        }
        // Insert new items
        foreach ($items as $item) {
            if (empty($item['item_id'])) {
                $itemSql = "INSERT INTO invoicing.document_items (
                    document_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total, sku
                ) VALUES (
                    :document_id, :product_id, :product_description, :quantity, :unit_price, :discount_percentage, :tax_rate_id, :line_total, :sku
                )";
                $itemStmt = $conn->prepare($itemSql);
                $itemStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_id', $item['product_id'] ?? null, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_description', $item['product_description'] ?? '');
                $itemStmt->bindValue(':quantity', clean_numeric($item['quantity'] ?? 1));
                $itemStmt->bindValue(':unit_price', clean_numeric($item['unit_price'] ?? 0));
                $itemStmt->bindValue(':discount_percentage', clean_numeric($item['discount_percentage'] ?? 0));
                $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'] ?? null, is_null($item['tax_rate_id'] ?? null) ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $itemStmt->bindValue(':line_total', clean_numeric($item['line_total'] ?? 0));
                $itemStmt->bindValue(':sku', $item['item_code'] ?? '', PDO::PARAM_STR);
                $itemStmt->execute();
            }
        }

        $conn->commit();

        // After commit, generate PDF for finalized documents
        $pdf_url = null;
        if ($mode === 'finalize') {
            // Prepare data for PDF
            $pdfData = [
                'client' => [
                    'name' => $documentData['client_name'] ?? '',
                    'email' => $documentData['client_email'] ?? '',
                    'phone' => $documentData['client_phone'] ?? '',
                    'address1' => $documentData['address1'] ?? '',
                    'address2' => $documentData['address2'] ?? '',
                    'vat_number' => $documentData['vat_number'] ?? '',
                    'registration_number' => $documentData['registration_number'] ?? ''
                ],
                'items' => array_map(function($item) {
                    return [
                        'qty' => $item['quantity'] ?? 1,
                        'item_code' => $item['item_code'] ?? '',
                        'description' => $item['product_description'] ?? '',
                        'unit_price' => $item['unit_price'] ?? '',
                        'tax' => $item['tax_percentage'] ?? '',
                        'total' => $item['line_total'] ?? ''
                    ];
                }, $items),
                'totals' => [
                    'subtotal' => $documentData['subtotal'] ?? '',
                    'tax' => $documentData['tax_amount'] ?? '',
                    'total' => $documentData['total_amount'] ?? ''
                ],
                'notes' => array_filter([
                    $documentData['public_note'] ?? '',
                    $documentData['private_note'] ?? '',
                    $documentData['foot_note'] ?? ''
                ]),
                'invoice_number' => $currentNumber,
                'invoice_date' => $documentData['issue_date'] ?? '',
                'due_date' => $documentData['due_date'] ?? '',
                'salesperson' => [
                    'name' => $documentData['salesperson_name'] ?? '',
                    'email' => '' // Add if available
                ]
            ];
            $pdfRes = null;
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, __DIR__ . '/../api/generate-document-pdf.php');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pdfData));
                $pdfResRaw = curl_exec($ch);
                curl_close($ch);
                $pdfRes = json_decode($pdfResRaw, true);
                if ($pdfRes && !empty($pdfRes['success']) && !empty($pdfRes['url'])) {
                    $pdf_url = $pdfRes['url'];
                    // Save PDF path to document
                    $stmt = $conn->prepare('UPDATE invoicing.documents SET pdf_path = :pdf_path WHERE document_id = :document_id');
                    $stmt->bindValue(':pdf_path', $pdf_url);
                    $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            } catch (Exception $e) {
                error_log('[DOCUMENT PDF GENERATION ERROR] ' . $e->getMessage());
            }
        }

        // Logging and notification
        log_user_action($updated_by, 'update_document', $document_id, json_encode($documentData));
        send_notification($updated_by, "Document #$document_id updated successfully.");
        return [
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => [
                'document_id' => $document_id,
                'document_number' => $currentNumber,
                'document_status' => $document_status,
                'pdf_url' => $pdf_url
            ]
        ];
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // Always return the real error message for AI/global error handler, but rewrite with AI
        $msg = $e->getMessage();
        $friendly = function_exists('get_friendly_error') ? get_friendly_error($msg) : $msg;
        error_log('update_document error: ' . $msg);
        log_user_action($updated_by, 'update_document', $document_id, $msg);
        return [
            'success' => false,
            'message' => $friendly,
            'data' => null,
            'error_code' => 'DOCUMENT_UPDATE_ERROR'
        ];
    }
}

function delete_document(int $document_id, int $deleted_by): array {
    if (!check_user_permission($deleted_by, 'delete_document', $document_id)) {
        $msg = "Permission denied for user $deleted_by to delete document $document_id";
        error_log($msg);
        log_user_action($deleted_by, 'delete_document', $document_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    global $conn;
    try {
        $sql = "UPDATE invoicing.documents SET document_status = 'deleted', deleted_at = NOW(), updated_by = :deleted_by WHERE document_id = :document_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':deleted_by', $deleted_by, PDO::PARAM_INT);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        log_user_action($deleted_by, 'delete_document', $document_id);
        send_notification($deleted_by, "Document #$document_id deleted.");
        return [
            'success' => true,
            'message' => 'Document deleted successfully',
            'data' => ['document_id' => $document_id]
        ];
    } catch (Exception $e) {
        $msg = "delete_document error: " . $e->getMessage();
        error_log($msg);
        log_user_action($deleted_by, 'delete_document', $document_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to delete document',
            'data' => null,
            'error_code' => 'DOCUMENT_DELETE_ERROR'
        ];
    }
}

function change_document_status(int $document_id, string $status, int $updated_by): array {
    if (!check_user_permission($updated_by, 'change_document_status', $document_id)) {
        $msg = "Permission denied for user $updated_by to change status of document $document_id";
        error_log($msg);
        log_user_action($updated_by, 'change_document_status', $document_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    global $conn;
    try {
        $sql = "UPDATE invoicing.documents SET document_status = :status, updated_at = NOW(), updated_by = :updated_by WHERE document_id = :document_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':updated_by', $updated_by, PDO::PARAM_INT);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        log_user_action($updated_by, 'change_document_status', $document_id, $status);
        send_notification($updated_by, "Document #$document_id status changed to $status.");
        return [
            'success' => true,
            'message' => 'Document status updated',
            'data' => [
                'document_id' => $document_id,
                'status' => $status
            ]
        ];
    } catch (Exception $e) {
        $msg = "change_document_status error: " . $e->getMessage();
        error_log($msg);
        log_user_action($updated_by, 'change_document_status', $document_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to change document status',
            'data' => null,
            'error_code' => 'DOCUMENT_STATUS_ERROR'
        ];
    }
}

function get_recurring_invoice_for_document(int $document_id): array {
    global $conn;
    $sql = "SELECT r.* FROM invoicing.recurring_invoices r JOIN invoicing.documents d ON r.client_id = d.client_id WHERE d.document_id = :document_id AND r.status = 'active' ORDER BY r.created_at DESC LIMIT 1";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return [
                'success' => true,
                'message' => 'Recurring invoice found',
                'data' => $result
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No recurring invoice found',
                'data' => null,
                'error_code' => 'NO_RECURRING_INVOICE'
            ];
        }
    } catch (PDOException $e) {
        $msg = "get_recurring_invoice_for_document error: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'get_recurring_invoice_for_document', $document_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to fetch recurring invoice',
            'data' => null,
            'error_code' => 'RECURRING_INVOICE_ERROR'
        ];
    }
}

/**
 * Get related documents for a given document
 * @param int $document_id
 * @return array
 */
function get_related_documents(int $document_id): array {
    global $conn;
    try {
        // Get documents that reference this document (credit notes, refunds)
        $stmt = $conn->prepare("
            SELECT 
                d.document_id,
                d.document_type,
                d.document_number,
                d.issue_date,
                d.total_amount,
                d.document_status,
                d.related_document_id
            FROM invoicing.documents d
            WHERE d.related_document_id = :document_id 
            AND d.deleted_at IS NULL
            ORDER BY d.issue_date DESC
        ");
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        $relatedDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get the document this document references (if any)
        $stmt = $conn->prepare("
            SELECT 
                d.document_id,
                d.document_type,
                d.document_number,
                d.issue_date,
                d.total_amount,
                d.document_status
            FROM invoicing.documents d
            WHERE d.document_id = (
                SELECT related_document_id 
                FROM invoicing.documents 
                WHERE document_id = :document_id
            )
            AND d.deleted_at IS NULL
        ");
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        $parentDoc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Related documents retrieved successfully',
            'data' => [
                'parent_document' => $parentDoc,
                'related_documents' => $relatedDocs
            ]
        ];
    } catch (Exception $e) {
        error_log('get_related_documents error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to get related documents',
            'data' => null,
            'error_code' => 'RELATED_DOCUMENTS_FETCH_ERROR'
        ];
    }
}

/**
 * Get available invoices for credit note/refund creation
 * @param int $client_id (optional)
 * @return array
 */
function get_available_invoices_for_credit_refund(int $client_id = null): array {
    global $conn;
    try {
        $sql = "
            SELECT 
                d.document_id,
                d.document_number,
                d.issue_date,
                d.total_amount,
                d.balance_due,
                d.document_status,
                c.client_name
            FROM invoicing.documents d
            JOIN invoicing.clients c ON d.client_id = c.client_id
            WHERE d.document_type IN ('invoice', 'vehicle_invoice', 'recurring_invoice')
            AND d.document_status IN ('sent', 'paid', 'overdue')
            AND d.deleted_at IS NULL
        ";
        
        $params = [];
        if ($client_id) {
            $sql .= " AND d.client_id = :client_id";
            $params[':client_id'] = $client_id;
        }
        
        $sql .= " ORDER BY d.issue_date DESC";
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, PDO::PARAM_INT);
        }
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'message' => 'Available invoices retrieved successfully',
            'data' => $invoices
        ];
    } catch (Exception $e) {
        error_log('get_available_invoices_for_credit_refund error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to get available invoices',
            'data' => null,
            'error_code' => 'AVAILABLE_INVOICES_FETCH_ERROR'
        ];
    }
}

/**
 * Get next document number for a specific document type
 * @param string $action The action name (e.g., 'get_next_invoice_number')
 * @return array
 */
function get_next_document_number(string $action): array {
    global $conn;
    error_log('[GET_NEXT_DOCUMENT_NUMBER] Function called with action: ' . $action);
    try {
        // Map action to document type and settings fields
        $documentTypeMap = [
            'get_next_quotation_number' => ['type' => 'quotation', 'prefix_field' => 'quotation_prefix', 'current_field' => 'quotation_current_number'],
            'get_next_invoice_number' => ['type' => 'invoice', 'prefix_field' => 'invoice_prefix', 'current_field' => 'invoice_current_number'],
            'get_next_credit_note_number' => ['type' => 'credit_note', 'prefix_field' => 'credit_note_prefix', 'current_field' => 'credit_note_current_number'],
            'get_next_refund_number' => ['type' => 'refund', 'prefix_field' => 'refund_prefix', 'current_field' => 'refund_current_number'],
            'get_next_proforma_number' => ['type' => 'proforma', 'prefix_field' => 'proforma_prefix', 'current_field' => 'proforma_current_number']
        ];
        
        if (!isset($documentTypeMap[$action])) {
            return [
                'success' => false,
                'message' => 'Invalid document type',
                'data' => null,
                'error_code' => 'INVALID_DOCUMENT_TYPE'
            ];
        }
        
        $config = $documentTypeMap[$action];
        
        // Get current settings
        error_log('[GET_NEXT_DOCUMENT_NUMBER] Action: ' . $action . ', Config: ' . json_encode($config));
        
        // Check if the fields exist in the table
        try {
            $checkFieldsStmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name IN (?, ?)");
            $checkFieldsStmt->execute([$config['prefix_field'], $config['current_field']]);
            $existingFields = $checkFieldsStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log('[GET_NEXT_DOCUMENT_NUMBER] Existing fields: ' . json_encode($existingFields));
        } catch (Exception $e) {
            error_log('[GET_NEXT_DOCUMENT_NUMBER] Error checking fields: ' . $e->getMessage());
            $existingFields = [];
        }
        
        if (count($existingFields) < 2) {
            error_log('[GET_NEXT_DOCUMENT_NUMBER] Missing fields: ' . json_encode($existingFields) . ' for config: ' . json_encode($config));
            // Return a fallback response
            return [
                'success' => true,
                'message' => 'Next document number generated successfully (using fallback)',
                'data' => [
                    'number' => 'DRAFT-' . time(),
                    'next_number' => 1,
                    'prefix' => 'DRAFT'
                ]
            ];
        }
        
        $stmt = $conn->prepare("SELECT {$config['prefix_field']}, {$config['current_field']} FROM settings.invoice_settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log('[GET_NEXT_DOCUMENT_NUMBER] Settings result: ' . json_encode($settings));
        
        if (!$settings) {
            error_log('[GET_NEXT_DOCUMENT_NUMBER] No settings found, creating default settings');
            try {
                // Create default settings if none exist
                $insertStmt = $conn->prepare("INSERT INTO settings.invoice_settings (invoice_prefix, invoice_current_number, quotation_prefix, quotation_current_number, credit_note_prefix, credit_note_current_number, proforma_prefix, proforma_current_number, refund_prefix, refund_current_number) VALUES ('INV', 1, 'QUO', 1, 'CN', 1, 'PRO', 1, 'REF', 1)");
                $insertStmt->execute();
                error_log('[GET_NEXT_DOCUMENT_NUMBER] Default settings created successfully');
                
                // Try to get settings again
                $stmt->execute();
                $settings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$settings) {
                    error_log('[GET_NEXT_DOCUMENT_NUMBER] Still no settings after creation attempt');
                    return [
                        'success' => false,
                        'message' => 'Invoice settings not found and could not be created',
                        'data' => null,
                        'error_code' => 'SETTINGS_NOT_FOUND'
                    ];
                }
            } catch (Exception $e) {
                error_log('[GET_NEXT_DOCUMENT_NUMBER] Error creating default settings: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error creating default settings: ' . $e->getMessage(),
                    'data' => null,
                    'error_code' => 'SETTINGS_CREATION_ERROR'
                ];
            }
        }
        
        $prefix = $settings[$config['prefix_field']] ?? '';
        $currentNumber = $settings[$config['current_field']] ?? 1;
        
        // If prefix is empty, use a default based on document type
        if (empty($prefix)) {
            switch ($config['type']) {
                case 'quotation':
                    $prefix = 'QUO';
                    break;
                case 'invoice':
                    $prefix = 'INV';
                    break;
                case 'credit_note':
                    $prefix = 'CN';
                    break;
                case 'refund':
                    $prefix = 'REF';
                    break;
                case 'proforma':
                    $prefix = 'PRO';
                    break;
                default:
                    $prefix = 'DOC';
            }
        }
        
        $nextNumber = $currentNumber + 1;
        
        // Generate the document number
        $documentNumber = $prefix . $nextNumber;
        
        error_log('[GET_NEXT_DOCUMENT_NUMBER] Generated document number: ' . $documentNumber);
        
        return [
            'success' => true,
            'message' => 'Next document number generated successfully',
            'data' => [
                'number' => $documentNumber,
                'next_number' => $nextNumber,
                'prefix' => $prefix
            ]
        ];
    } catch (Exception $e) {
        error_log('get_next_document_number error: ' . $e->getMessage());
        error_log('get_next_document_number stack trace: ' . $e->getTraceAsString());
        
        // Return a fallback response instead of failing completely
        return [
            'success' => true,
            'message' => 'Next document number generated successfully (using fallback)',
            'data' => [
                'number' => 'DOC-' . time(),
                'next_number' => 1,
                'prefix' => 'DOC'
            ]
        ];
    }
}

/**
 * Increment document number counter for a specific document type
 * @param string $documentType
 * @return bool
 */
function increment_document_number_counter(string $documentType): bool {
    global $conn;
    try {
        // Map document type to settings field
        $fieldMap = [
            'quotation' => 'quotation_current_number',
            'invoice' => 'invoice_current_number',
            'credit_note' => 'credit_note_current_number',
            'refund' => 'refund_current_number',
            'proforma' => 'proforma_current_number'
        ];
        
        if (!isset($fieldMap[$documentType])) {
            error_log("Unknown document type for counter increment: $documentType");
            return false;
        }
        
        $field = $fieldMap[$documentType];
        $sql = "UPDATE settings.invoice_settings SET $field = $field + 1 WHERE id = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        return true;
    } catch (Exception $e) {
        error_log('increment_document_number_counter error: ' . $e->getMessage());
        return false;
    }
}