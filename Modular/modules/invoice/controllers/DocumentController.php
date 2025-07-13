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
    $sql = "SELECT d.document_id, d.client_id, d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.total_amount, d.salesperson_id, c.client_id, c.client_name, e.employee_id FROM invoicing.documents d JOIN invoicing.clients c ON d.client_id = c.client_id JOIN core.employees e ON d.salesperson_id = e.employee_id WHERE 1=1";
    $params = [];
    if (!empty($type)) {
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
        $countSql = "SELECT COUNT(*) FROM invoicing.documents d WHERE 1=1";
        if (!empty($type)) $countSql .= " AND d.document_type = :type";
        if (!empty($status)) $countSql .= " AND d.document_status = :status";
        if (!empty($dateFrom)) $countSql .= " AND d.issue_date >= :date_from";
        if (!empty($dateTo)) $countSql .= " AND d.issue_date <= :date_to";
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
    $sql = "SELECT d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.salesperson_id, d.subtotal, d.discount_amount, d.tax_amount, d.total_amount, d.client_purchase_order_number, d.notes, d.terms_conditions, d.is_recurring, d.recurring_template_id, d.requires_approval, d.approved_by, d.approved_at, d.salesperson_id, c.client_id, c.client_type, c.client_name, c.client_email, c.client_cell, c.client_tell, c.first_name, c.last_name, c.registration_number, c.vat_number, e.employee_id, e.employee_first_name, e.employee_last_name FROM invoicing.documents d JOIN invoicing.clients c ON d.client_id = c.client_id JOIN core.employees e ON d.salesperson_id = e.employee_id WHERE d.document_id = :document_id LIMIT 1";
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
    $sql = "SELECT i.item_id, i.document_id, i.product_id, i.product_description, i.product_quantity, i.unit_price, i.discount_percentage, i.tax_rate_id, i.line_total, tr.rate FROM invoicing.document_items i JOIN core.tax_rates tr ON i.tax_rate_id = tr.tax_rate_id WHERE i.document_id = :document_id LIMIT 100";
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
    $documentData = $options['documentData'] ?? [];
    $items = $options['items'] ?? [];
    $mode = $options['mode'] ?? 'draft';
    $user_id = $documentData['created_by'] ?? null;
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
    $requiredFields = ['client_id', 'document_type', 'issue_date', 'salesperson_id', 'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'balance_due', 'created_by'];
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

    try {
        $conn->beginTransaction();

        // Handle draft vs finalize
        if ($mode === 'draft') {
            // Generate a draft document_number (e.g., "DRAFT-YYYYMMDD-HHMMSS")
            $draftNumber = 'DRAFT-' . date('Ymd-His');
            $document_number = $draftNumber;
            $document_status = 'draft';
        } elseif ($mode === 'finalize') {
            // Use provided document_number and status, or generate if not set
            $document_number = !empty($options['document_number']) ? $options['document_number'] : ('DOC-' . date('Ymd-His'));
            $document_status = !empty($options['status']) ? $options['status'] : 'pending';
        } else {
            throw new Exception("Invalid mode for create_document: $mode");
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
        $stmt->bindValue(':salesperson_id', $documentData['salesperson_id'], PDO::PARAM_INT);
        $stmt->bindValue(':subtotal', $documentData['subtotal']);
        $stmt->bindValue(':discount_amount', $documentData['discount_amount']);
        $stmt->bindValue(':tax_amount', $documentData['tax_amount']);
        $stmt->bindValue(':total_amount', $documentData['total_amount']);
        $stmt->bindValue(':balance_due', $documentData['balance_due']);
        $stmt->bindValue(':client_purchase_order_number', $documentData['client_purchase_order_number'] ?? null);
        $stmt->bindValue(':notes', $documentData['notes'] ?? null);
        $stmt->bindValue(':terms_conditions', $documentData['terms_conditions'] ?? null);
        $stmt->bindValue(':is_recurring', !empty($documentData['is_recurring']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':recurring_template_id', $documentData['recurring_template_id'] ?? null, PDO::PARAM_INT);
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
            $itemStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
            $itemStmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
            $itemStmt->bindValue(':product_description', $item['product_description']);
            $itemStmt->bindValue(':quantity', $item['quantity']);
            $itemStmt->bindValue(':unit_price', $item['unit_price']);
            $itemStmt->bindValue(':discount_percentage', $item['discount_percentage']);
            $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'], PDO::PARAM_INT);
            $itemStmt->bindValue(':line_total', $item['line_total']);
            $itemStmt->execute();
        }

        // Insert into recurring_invoices if is_recurring is true (before commit)
        if (!empty($documentData['is_recurring'])) {
            // Required fields for recurring
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
            )";
            $recurringStmt = $conn->prepare($recurringSql);
            $recurringStmt->bindValue(':client_id', $documentData['client_id'], PDO::PARAM_INT);
            $recurringStmt->bindValue(':frequency', $frequency);
            $recurringStmt->bindValue(':start_date', $start_date);
            $recurringStmt->bindValue(':end_date', $end_date);
            $recurringStmt->execute();
        }

        $conn->commit();

        // Logging and notification
        log_user_action($user_id, 'create_document', $document_id, json_encode($documentData));
        send_notification($user_id, "Document #$document_id created successfully.");
        return [
            'success' => true,
            'message' => 'Document created successfully',
            'data' => ['document_id' => (int)$document_id]
        ];
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "create_document error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'create_document', null, $msg);
        return [
            'success' => false,
            'message' => 'Failed to create document',
            'data' => null,
            'error_code' => 'DOCUMENT_CREATE_ERROR'
        ];
    }
}

function update_document(int $document_id, array $options): array {
    global $conn;
    $documentData = $options['documentData'] ?? [];
    $items = $options['items'] ?? [];
    $mode = $options['mode'] ?? 'draft';
    $updated_by = $documentData['updated_by'] ?? null;
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
            // Optionally update document_number if provided
        } else {
            throw new Exception("Invalid mode for update_document: $mode");
        }

        // Prepare update for documents table (partial update support)
        $fields = [
            'client_id' => [':client_id', PDO::PARAM_INT],
            'document_type' => [':document_type', PDO::PARAM_STR],
            'related_document_id' => [':related_document_id', PDO::PARAM_INT],
            'issue_date' => [':issue_date', PDO::PARAM_STR],
            'due_date' => [':due_date', PDO::PARAM_STR],
            'document_status' => [':document_status', PDO::PARAM_STR],
            'salesperson_id' => [':salesperson_id', PDO::PARAM_INT],
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
            if (isset($documentData[$key])) {
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
                $itemStmt->bindValue(':tax_rate_id', $item['tax_rate_id'], PDO::PARAM_INT);
                $itemStmt->bindValue(':line_total', $item['line_total']);
                $itemStmt->execute();
            }
        }

        // Recurring update logic
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
                ON CONFLICT (client_id, start_date) DO UPDATE SET frequency = EXCLUDED.frequency, end_date = EXCLUDED.end_date, updated_at = NOW()";
            $recurringStmt = $conn->prepare($recurringSql);
            $recurringStmt->bindValue(':client_id', $documentData['client_id'], PDO::PARAM_INT);
            $recurringStmt->bindValue(':frequency', $frequency);
            $recurringStmt->bindValue(':start_date', $start_date);
            $recurringStmt->bindValue(':end_date', $end_date);
            $recurringStmt->execute();
        }
        $conn->commit();
        // Logging and notification
        log_user_action($updated_by, 'update_document', $document_id, json_encode($documentData));
        send_notification($updated_by, "Document #$document_id updated successfully.");
        return [
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => ['document_id' => $document_id]
        ];
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "update_document error: " . $e->getMessage();
        error_log($msg);
        log_user_action($updated_by, 'update_document', $document_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to update document',
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