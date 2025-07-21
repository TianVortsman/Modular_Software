<?php
namespace App\modules\invoice\controllers;

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
    $sql = "SELECT d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.salesperson_id, d.subtotal, d.discount_amount, d.tax_amount, d.total_amount, d.client_purchase_order_number, d.notes, d.terms_conditions, d.is_recurring, d.recurring_template_id, d.requires_approval, d.approved_by, d.approved_at, d.salesperson_id, c.client_id, c.client_type, c.client_name, c.client_email, c.client_cell, c.client_tell, c.first_name, c.last_name, c.registration_number, c.vat_number, e.employee_id, e.employee_first_name, e.employee_last_name, a.address_line1, a.address_line2, a.city, a.suburb, a.province, a.country, a.postal_code FROM invoicing.documents d JOIN invoicing.clients c ON d.client_id = c.client_id JOIN core.employees e ON d.salesperson_id = e.employee_id LEFT JOIN invoicing.client_addresses ca ON ca.client_id = c.client_id AND ca.address_id = (SELECT address_id FROM invoicing.client_addresses ca2 WHERE ca2.client_id = c.client_id LIMIT 1) LEFT JOIN invoicing.address a ON a.address_id = ca.address_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW()) WHERE d.document_id = :document_id LIMIT 1";
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
    $sql = "SELECT i.item_id, i.document_id, i.product_id, i.product_description, i.quantity, i.unit_price, i.discount_percentage, i.tax_rate_id, i.line_total, tr.rate FROM invoicing.document_items i LEFT JOIN core.tax_rates tr ON i.tax_rate_id = tr.tax_rate_id WHERE i.document_id = :document_id LIMIT 100";
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
    $mode = $options['mode'] ?? 'draft';
    // Always get user_id from payload or session
    $user_id = $documentData['created_by'] ?? ($_SESSION['user_id'] ?? null);
    // Permission check
    if (!check_user_permission($user_id, 'create_document')) {
        $msg = "Permission denied for user $user_id to create document";
        error_log($msg);
        log_user_action($user_id, 'create_document', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    // Validate required fields
    $requiredFields = ['client_id', 'document_type', 'issue_date', 'subtotal', 'tax_amount', 'total_amount']; // salesperson_id is now optional
    foreach ($requiredFields as $field) {
        if (!isset($documentData[$field])) {
            $msg = "Missing required field: $field";
            error_log($msg);
            log_user_action($user_id, 'create_document', null, $msg);
            return [
                'success' => false,
                'message' => $msg,
                'data' => null,
                'error_code' => 'VALIDATION_ERROR'
            ];
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
            $document_status = !empty($options['status']) ? $options['status'] : 'pending';
        } else {
            throw new Exception("Invalid mode for create_document: $mode");
        }

        // Recurring logic: insert recurring_invoices first if needed
        $recurring_id = null;
        if (!empty($documentData['is_recurring'])) {
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
            $recurringStmt->bindValue(':client_id', $documentData['client_id'], PDO::PARAM_INT);
            $recurringStmt->bindValue(':frequency', $frequency);
            $recurringStmt->bindValue(':start_date', $start_date);
            $recurringStmt->bindValue(':end_date', $end_date);
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
                    client_id, document_type, document_number, related_document_id, issue_date, due_date, document_status, 
                    salesperson_id, subtotal, discount_amount, tax_amount, total_amount, balance_due, 
                    client_purchase_order_number, notes, terms_conditions, is_recurring, recurring_template_id, 
                    requires_approval, created_by, created_at, updated_at
                ) VALUES (
                    :client_id, :document_type, :document_number, :related_document_id, :issue_date, :due_date, :document_status, 
                    :salesperson_id, :subtotal, :discount_amount, :tax_amount, :total_amount, :balance_due, 
                    :client_purchase_order_number, :notes, :terms_conditions, :is_recurring, :recurring_template_id, 
                    :requires_approval, :created_by, NOW(), NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $documentData['client_id'], PDO::PARAM_INT);
        $stmt->bindValue(':document_type', $documentData['document_type']);
        $stmt->bindValue(':document_number', $document_number);
        $stmt->bindValue(':related_document_id', $documentData['related_document_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':issue_date', $documentData['issue_date']);
        $stmt->bindValue(':due_date', $documentData['due_date'] ?? null);
        $stmt->bindValue(':document_status', $document_status);
        $stmt->bindValue(':salesperson_id', isset($documentData['salesperson_id']) && is_numeric($documentData['salesperson_id']) ? $documentData['salesperson_id'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':subtotal', $documentData['subtotal']);
        $stmt->bindValue(':discount_amount', $documentData['discount_amount']);
        $stmt->bindValue(':tax_amount', $documentData['tax_amount']);
        $stmt->bindValue(':total_amount', $documentData['total_amount']);
        $stmt->bindValue(':balance_due', $documentData['balance_due']);
        $stmt->bindValue(':client_purchase_order_number', $documentData['client_purchase_order_number'] ?? null);
        $stmt->bindValue(':notes', $documentData['notes'] ?? null);
        $stmt->bindValue(':terms_conditions', $documentData['terms_conditions'] ?? null);
        $stmt->bindValue(':is_recurring', $documentData['is_recurring'] ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':recurring_template_id', $documentData['recurring_template_id'], is_null($documentData['recurring_template_id']) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':requires_approval', !empty($documentData['requires_approval']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':created_by', $documentData['created_by'], PDO::PARAM_INT);

        $stmt->execute();
        $document_id = $conn->lastInsertId();

        // Insert document items
        $itemSql = "INSERT INTO invoicing.document_items (
                        document_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total
                    ) VALUES (
                        :document_id, :product_id, :product_description, :quantity, :unit_price, :discount_percentage, :tax_rate_id, :line_total
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
            $itemStmt->bindValue(':quantity', $item['quantity']);
            $itemStmt->bindValue(':unit_price', $item['unit_price']);
            $itemStmt->bindValue(':discount_percentage', $item['discount_percentage']);
            $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'], is_null($item['tax_rate_id']) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $itemStmt->bindValue(':line_total', $item['line_total']);
            $itemStmt->execute();
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
        return [
            'success' => true,
            'message' => 'Document created successfully',
            'data' => [
                'document_id' => (int)$document_id,
                'document_number' => $document_number,
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
        error_log('create_document error: ' . $msg);
        log_user_action($user_id, 'create_document', null, $msg);
        return [
            'success' => false,
            'message' => $friendly,
            'data' => null,
            'error_code' => 'DOCUMENT_CREATE_ERROR'
        ];
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
    $mode = $options['mode'] ?? 'draft';
    // Always get updated_by from payload or session
    $updated_by = $documentData['updated_by'] ?? ($_SESSION['user_id'] ?? null);
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

        // Handle draft vs finalize
        if ($mode === 'draft') {
            $document_status = 'draft';
            // Do not update document_number if it's a draft update
        } elseif ($mode === 'finalize') {
            $document_status = !empty($options['status']) ? $options['status'] : 'pending';
            // Assign a new document_number if not set or is a draft
            $stmtCheck = $conn->prepare('SELECT document_number, document_status FROM invoicing.documents WHERE document_id = :document_id');
            $stmtCheck->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $currentDoc = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $currentNumber = $currentDoc['document_number'] ?? '';
            $currentStatus = $currentDoc['document_status'] ?? '';
            // Prevent editing if already finalized
            if ($currentStatus !== 'draft') {
                throw new Exception('Cannot edit a finalized document.');
            }
            if (empty($currentNumber) || strpos($currentNumber, 'DRAFT-') === 0) {
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
        } else {
            throw new Exception("Invalid mode for update_document: $mode");
        }

        // Recurring update logic
        $recurring_id = null;
        if (!empty($documentData['is_recurring'])) {
            $frequency = $documentData['frequency'] ?? null;
            $start_date = $documentData['start_date'] ?? null;
            $end_date = $documentData['end_date'] ?? null;
            if (empty($frequency) || empty($start_date)) {
                throw new Exception("Missing required recurring invoice fields: frequency and start_date are required.");
            }
            // Upsert recurring invoice
            $recurringSql = "INSERT INTO invoicing.recurring_invoices (client_id, frequency, start_date, end_date, status, created_at, updated_at)
                VALUES (:client_id, :frequency, :start_date, :end_date, 'active', NOW(), NOW())
                ON CONFLICT (client_id, start_date) DO UPDATE SET frequency = EXCLUDED.frequency, end_date = EXCLUDED.end_date, updated_at = NOW()
                RETURNING recurring_id";
            $recurringStmt = $conn->prepare($recurringSql);
            $recurringStmt->bindValue(':client_id', $documentData['client_id'], PDO::PARAM_INT);
            $recurringStmt->bindValue(':frequency', $frequency);
            $recurringStmt->bindValue(':start_date', $start_date);
            $recurringStmt->bindValue(':end_date', $end_date);
            $recurringStmt->execute();
            $recurring_id = $recurringStmt->fetchColumn();
            $documentData['is_recurring'] = true;
            $documentData['recurring_template_id'] = $recurring_id;
        } else {
            $documentData['is_recurring'] = false;
            $documentData['recurring_template_id'] = null;
        }

        // Prepare update for documents table (partial update support)
        $fields = [
            'client_id' => [':client_id', PDO::PARAM_INT],
            'document_type' => [':document_type', PDO::PARAM_STR],
            'related_document_id' => [':related_document_id', PDO::PARAM_INT],
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
            if ($key === 'salesperson_id') {
                $stmt->bindValue($param, (isset($documentData['salesperson_id']) && is_numeric($documentData['salesperson_id'])) ? $documentData['salesperson_id'] : null, $type);
            } else if (isset($documentData[$key])) {
                $stmt->bindValue($param, $documentData[$key], $type);
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
        $stmtItems = $conn->prepare("SELECT item_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total FROM invoicing.document_items WHERE document_id = :document_id");
        $stmtItems->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmtItems->execute();
        foreach ($stmtItems->fetchAll(PDO::FETCH_ASSOC) as $ci) {
            $currentItems[$ci['item_id']] = $ci;
        }
        $newItemsById = [];
        foreach ($items as $item) {
            if (!empty($item['item_id'])) {
                $newItemsById[$item['item_id']] = $item;
            }
        }
        // Update existing items
        foreach ($currentItems as $item_id => $ci) {
            if (isset($newItemsById[$item_id])) {
                $ni = $newItemsById[$item_id];
                // Compare fields, update if changed
                $fieldsToUpdate = [];
                foreach (['product_id','product_description','quantity','unit_price','discount_percentage','tax_rate_id','line_total'] as $field) {
                    if ($ci[$field] != $ni[$field]) {
                        $fieldsToUpdate[$field] = $ni[$field];
                    }
                }
                if ($fieldsToUpdate) {
                    $set = implode(', ', array_map(fn($f) => "$f = :$f", array_keys($fieldsToUpdate)));
                    $updateSql = "UPDATE invoicing.document_items SET $set WHERE item_id = :item_id";
                    $updateStmt = $conn->prepare($updateSql);
                    foreach ($fieldsToUpdate as $f => $v) {
                        $updateStmt->bindValue(":$f", $v);
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
                    document_id, product_id, product_description, quantity, unit_price, discount_percentage, tax_rate_id, line_total
                ) VALUES (
                    :document_id, :product_id, :product_description, :quantity, :unit_price, :discount_percentage, :tax_rate_id, :line_total
                )";
                $itemStmt = $conn->prepare($itemSql);
                $itemStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
                $itemStmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
                $itemStmt->bindValue(':product_description', $item['product_description']);
                $itemStmt->bindValue(':quantity', $item['quantity']);
                $itemStmt->bindValue(':unit_price', $item['unit_price']);
                $itemStmt->bindValue(':discount_percentage', $item['discount_percentage']);
                $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'], is_null($item['tax_rate_id']) ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $itemStmt->bindValue(':line_total', $item['line_total']);
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