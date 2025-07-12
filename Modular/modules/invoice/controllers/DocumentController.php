<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;

function list_documents(array $options = []): array {
    global $conn;

    // Extract and sanitize parameters
    $search   = $options['search']    ?? null;
    $type   = $options['type']    ?? null;
    $page     = (int)($options['page'] ?? 1);
    $limit    = (int)($options['limit'] ?? 20);
    $sortBy   = $options['sort_by']   ?? 'document_id';
    $sortDir  = strtolower($options['sort_dir'] ?? 'desc');

    // Whitelist sorting fields (must be filled per use-case)
    $allowedSortFields = ['document_date', 'document_id']; // e.g. ['name', 'created_at']
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'document_id'; // fallback field
    }

    $allowedSortDir = ['asc', 'desc'];
    if (!in_array($sortDir, $allowedSortDir)) {
        $sortDir = 'desc';
    }

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Base SQL query
    $sql = "SELECT d.document_id, d.client_id, d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.total_amount, d.salesperson_id, c.client_id, c.client_name, 
            e.employee_id
            FROM invoicing.documents d 
            JOIN invoicing.clients c ON d.client_id = c.client_id
            JOIN core.employees e ON d.salesperson_id = e.employee_id
            WHERE 1=1";
    $params = [];

    // Example filter by $Variable
    if (!empty($type)) {
        $sql .= " AND d.document_type = :type";
        $params[':type'] = $type;
    }

    // Example search
    if (!empty($search)) {
        $sql .= " AND (
        c.client_name ILIKE :search
        OR d.document_number ILIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }

    // Add sorting and pagination
    $sql .= " ORDER BY $sortBy $sortDir LIMIT :limit OFFSET :offset";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Bind all other values
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        return [];
    }
}

function get_document_details(int $document_id): ?array {
    global $conn;

    $sql = "SELECT 
            d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.salesperson_id, d.subtotal, d.discount_amount, d.tax_amount, d.total_amount, 
            d.client_purchase_order_number, d.notes, d.terms_conditions, d.is_recurring, d.recurring_template_id, d.requires_approval, d.approved_by, d.approved_at, d.salesperson_id, 
            c.client_id, c.client_type, c.client_name, c.client_email, c.client_cell, c.client_tell, c.first_name, c.last_name, c.registration_number, c.vat_number, 
            e.employee_id, e.employee_first_name, e.employee_last_name
            JOIN invoicing.clients c ON d.client_id = c.client_id
            JOIN core.employees e ON d.salesperson_id = e.employee_id
            FROM invoicing.documents d
            WHERE document_id = :document_id
            LIMIT 1";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null; // Return null if no record found

    } catch (PDOException $e) {
        error_log("Error in get_entity_details: " . $e->getMessage());
        return null;
    }
}

function get_document_items(int $document_id): ?array {
    global $conn;

    $sql = "SELECT 
            i.item_id, i.document_id, i.product_id, i.product_description, i.product_quantity, i.unit_price, i.discount_percentage, i.tax_rate_id, i.line_total, 
            tr.rate
            JOIN core.tax_rates tr ON i.tax_rate_id = tr.tax_rate_id
            FROM invoicing.document_items i
            WHERE document_id = :document_id
            LIMIT 1";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null; // Return null if no record found

    } catch (PDOException $e) {
        error_log("Error in get_entity_details: " . $e->getMessage());
        return null;
    }
}

function create_document(array $options): ?int {
    global $conn;

    $documentData = $options['documentData'] ?? [];
    $items = $options['items'] ?? [];
    $mode = $options['mode'] ?? 'draft';

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
        $stmt->bindValue(':is_recurring', !empty($documentData['is_recurring']) ? 1 : 0, PDO::PARAM_BOOL);
        $stmt->bindValue(':recurring_template_id', $documentData['recurring_template_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':requires_approval', !empty($documentData['requires_approval']) ? 1 : 0, PDO::PARAM_BOOL);
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

        // No need to link to client or salesperson separately, as those are foreign keys in the documents table

        $conn->commit();
        return (int)$document_id;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("create_document error: " . $e->getMessage());
        return null;
    }
}

function update_document(int $document_id, array $options): bool {
    global $conn;

    $documentData = $options['documentData'] ?? [];
    $items = $options['items'] ?? [];
    $mode = $options['mode'] ?? 'draft';

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

        // Prepare update for documents table
        $sql = "UPDATE invoicing.documents SET
                    client_id = :client_id,
                    document_type = :document_type,
                    " . (isset($options['document_number']) ? "document_number = :document_number," : "") . "
                    related_document_id = :related_document_id,
                    issue_date = :issue_date,
                    due_date = :due_date,
                    document_status = :document_status,
                    salesperson_id = :salesperson_id,
                    subtotal = :subtotal,
                    discount_amount = :discount_amount,
                    tax_amount = :tax_amount,
                    total_amount = :total_amount,
                    balance_due = :balance_due,
                    client_purchase_order_number = :client_purchase_order_number,
                    notes = :notes,
                    terms_conditions = :terms_conditions,
                    is_recurring = :is_recurring,
                    recurring_template_id = :recurring_template_id,
                    requires_approval = :requires_approval,
                    updated_at = NOW()
                WHERE document_id = :document_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $documentData['client_id'], PDO::PARAM_INT);
        $stmt->bindValue(':document_type', $documentData['document_type']);
        if (isset($options['document_number'])) {
            $stmt->bindValue(':document_number', $options['document_number']);
        }
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
        $stmt->bindValue(':is_recurring', !empty($documentData['is_recurring']) ? 1 : 0, PDO::PARAM_BOOL);
        $stmt->bindValue(':recurring_template_id', $documentData['recurring_template_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':requires_approval', !empty($documentData['requires_approval']) ? 1 : 0, PDO::PARAM_BOOL);
        $stmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);

        $stmt->execute();

        // Remove existing document items
        $deleteItemSql = "DELETE FROM invoicing.document_items WHERE document_id = :document_id";
        $deleteStmt = $conn->prepare($deleteItemSql);
        $deleteStmt->bindValue(':document_id', $document_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        // Insert new document items
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

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("update_document error: " . $e->getMessage());
        return false;
    }
}