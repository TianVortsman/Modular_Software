<?php
namespace App\modules\product\controllers;

use PDO;
use Exception;

function getDocuments(array $options = []): array {
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
    $sql = "SELECT d.document_id, d.client_id, d.document_number, d.document_status, d.document_type, d.issue_date, d.due_date, d.total_amount, c.client_id, c.client_name
            FROM invoicing.documents d 
            JOIN invoicing.clients c ON d.client_id = c.client_id
            WHERE 1=1"; // e.g. "SELECT id, name, status FROM your_table WHERE 1=1"
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