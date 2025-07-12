<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;


function list_clients(array $options = []): array {
    global $conn;

    // Extract and sanitize parameters
    $search   = $options['search']    ?? null;
    $type     = $options['type']      ?? null;
    $page     = (int)($options['page'] ?? 1);
    $limit    = (int)($options['limit'] ?? 20);
    $sortBy   = $options['sort_by']   ?? 'client_id';
    $sortDir  = strtolower($options['sort_dir'] ?? 'desc');

    // Whitelist sorting fields (must be filled per use-case)
    $allowedSortFields = ['client_id', 'client_name', 'first_name', 'last_name', 'client_type'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'client_id'; // fallback field
    }

    $allowedSortDir = ['asc', 'desc'];
    if (!in_array($sortDir, $allowedSortDir)) {
        $sortDir = 'desc';
    }

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Base SQL query
    $sql = "SELECT 
            c.client_id, c.client_type, c.client_name, c.first_name, c.last_name, c.client_email, c.client_cell, c.client_tell,
            -- Total invoices for this client
            (SELECT COUNT(*) FROM invoicing.documents d2 WHERE d2.client_id = c.client_id) AS total_invoices,

            -- Last invoice issue date
            (SELECT MAX(d3.issue_date) FROM invoicing.documents d3 WHERE d3.client_id = c.client_id) AS last_invoice_date,

            -- Total outstanding amount
            (SELECT COALESCE(SUM(d4.balance_due), 0) FROM invoicing.documents d4 WHERE d4.client_id = c.client_id AND d4.document_status IN ('unpaid', 'partially_paid')) AS outstanding_amount

            FROM invoicing.clients c
            WHERE 1=1";

    $params = [];

    // Example filter by $Variable
    if (!empty($type)) {
        $sql .= " AND c.client_type = :type";
        $params[':type'] = $type;
    }

    // Example search
    if (!empty($search)) {
        $sql .= " AND (
            c.client_name ILIKE :search
            OR c.first_name ILIKE :search
            OR c.last_name ILIKE :search
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

    } catch (\PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        return [];
    }
}

function get_client_details(int $client_id): ?array {
    global $conn;

    $sql = "SELECT *
            FROM invoicing.clients
            WHERE client_id = :client_id
            LIMIT 1";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null; // Return null if no record found

    } catch (PDOException $e) {
        error_log("Error in get_entity_details: " . $e->getMessage());
        return null;
    }
}

function create_client(array $data): ?int {
    global $conn;

    $sql = "INSERT INTO invoicing.clients (
                column1, column2
            ) VALUES (
                :column1, :column2
            )";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':column1', $data['column1']);
        $stmt->bindValue(':column2', $data['column2']);
        $stmt->execute();
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("create_entity error: " . $e->getMessage());
        return null;
    }
}



