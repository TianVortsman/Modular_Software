<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';

function list_clients(array $options = []): array {
    global $conn;
    $search   = $options['search']    ?? null;
    $type     = $options['type']      ?? null;
    $page     = (int)($options['page'] ?? 1);
    $limit    = (int)($options['limit'] ?? 20);
    $sortBy   = $options['sort_by']   ?? 'client_id';
    $sortDir  = strtolower($options['sort_dir'] ?? 'desc');
    $allowedSortFields = ['client_id', 'client_name', 'first_name', 'last_name', 'client_type'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'client_id';
    }
    $allowedSortDir = ['asc', 'desc'];
    if (!in_array($sortDir, $allowedSortDir)) {
        $sortDir = 'desc';
    }
    $offset = ($page - 1) * $limit;
    $sql = "SELECT c.client_id, c.client_type, c.client_name, c.first_name, c.last_name, c.client_email, c.client_cell, c.client_tell, c.vat_number, c.registration_number, 
        (SELECT COUNT(*) FROM invoicing.documents d2 WHERE d2.client_id = c.client_id) AS total_invoices, 
        (SELECT MAX(d3.issue_date) FROM invoicing.documents d3 WHERE d3.client_id = c.client_id) AS last_invoice_date, 
        (SELECT COALESCE(SUM(d4.balance_due), 0) FROM invoicing.documents d4 WHERE d4.client_id = c.client_id AND d4.document_status IN ('unpaid', 'partially_paid')) AS outstanding_amount, 
        a.address_line1, a.address_line2, a.city, a.suburb, a.province, a.country, a.postal_code
        FROM invoicing.clients c
        LEFT JOIN invoicing.client_addresses ca ON ca.client_id = c.client_id AND ca.address_id = (
            SELECT address_id FROM invoicing.client_addresses ca2 WHERE ca2.client_id = c.client_id LIMIT 1
        )
        LEFT JOIN invoicing.address a ON a.address_id = ca.address_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW())
        WHERE 1=1";
    $params = [];
    if (!empty($type)) {
        $sql .= " AND c.client_type = :type";
        $params[':type'] = $type;
    }
    if (!empty($search)) {
        $sql .= " AND (c.client_name ILIKE :search OR c.first_name ILIKE :search OR c.last_name ILIKE :search)";
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
        $countSql = "SELECT COUNT(*) FROM invoicing.clients c WHERE 1=1";
        if (!empty($type)) $countSql .= " AND c.client_type = :type";
        if (!empty($search)) $countSql .= " AND (c.client_name ILIKE :search OR c.first_name ILIKE :search OR c.last_name ILIKE :search)";
        $countStmt = $conn->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        return [
            'success' => true,
            'message' => 'Clients retrieved successfully',
            'data' => $data,
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ];
    } catch (\PDOException $e) {
        $msg = "Query failed: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'list_clients', null, $msg);
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data' => null,
            'error_code' => 'CLIENT_LIST_ERROR'
        ];
    }
}

function get_client_details(int $client_id): array {
    global $conn;
    try {
        $sql = "SELECT * FROM invoicing.clients WHERE client_id = :client_id LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->execute();
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$client) {
            $msg = 'Client not found';
            error_log($msg);
            log_user_action(null, 'get_client_details', $client_id, $msg);
            return [
                'success' => false,
                'message' => $msg,
                'data' => null,
                'error_code' => 'CLIENT_NOT_FOUND'
            ];
        }
        $addressSql = "SELECT a.* FROM invoicing.address a INNER JOIN invoicing.client_addresses ca ON ca.address_id = a.address_id WHERE ca.client_id = :client_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW())";
        $addressStmt = $conn->prepare($addressSql);
        $addressStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $addressStmt->execute();
        $addresses = $addressStmt->fetchAll(PDO::FETCH_ASSOC);
        $contactSql = "SELECT cp.* FROM invoicing.contact_person cp INNER JOIN invoicing.client_contacts cc ON cc.contact_id = cp.contact_id WHERE cc.client_id = :client_id AND (cp.deleted_at IS NULL OR cp.deleted_at > NOW())";
        $contactStmt = $conn->prepare($contactSql);
        $contactStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $contactStmt->execute();
        $contacts = $contactStmt->fetchAll(PDO::FETCH_ASSOC);
        $client['addresses'] = $addresses;
        $client['contacts'] = $contacts;
        return [
            'success' => true,
            'message' => 'Client details retrieved successfully',
            'data' => $client
        ];
    } catch (\PDOException $e) {
        $msg = "Error in get_client_details: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'get_client_details', $client_id, $msg);
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data' => null,
            'error_code' => 'CLIENT_DETAILS_ERROR'
        ];
    }
}

function create_client(array $data): array {
    global $conn;
    // Permission check (assume $data['created_by'] is set)
    $user_id = $data['created_by'] ?? ($_SESSION['tech_id'] ?? null);
    if (!check_user_permission($user_id, 'create_client')) {
        $msg = "Permission denied for user $user_id to create client";
        error_log($msg);
        log_user_action($user_id, 'create_client', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    // 1. Insert client
    $clientFields = [
        'client_type',
        'client_name',
        'client_email',
        'client_cell',
        'client_tell',
        'client_status',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'loyalty_level',
        'title',
        'initials',
        'registration_number',
        'vat_number',
        'website',
        'industry'
    ];

    $insertFields = [];
    $insertValues = [];
    $params = [];

    foreach ($clientFields as $field) {
        if (array_key_exists($field, $data)) {
            $insertFields[] = $field;
            $insertValues[] = ':' . $field;
            $params[':' . $field] = $data[$field];
        }
    }

    if (empty($insertFields)) {
        $msg = "create_client error: No valid fields provided.";
        error_log($msg);
        log_user_action($user_id, 'create_client', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'CLIENT_CREATE_ERROR'
        ];
    }

    $sql = "INSERT INTO invoicing.clients (" . implode(', ', $insertFields) . ")
            VALUES (" . implode(', ', $insertValues) . ")";

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                $stmt->bindValue($key, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $client_id = $conn->lastInsertId();

        // 2. Insert address if provided
        $address_id = null;
        if (!empty($data['address'])) {
            $addressFields = [
                'address_type_id',
                'address_line1',
                'address_line2',
                'city',
                'suburb',
                'province',
                'postal_code',
                'country',
                'is_primary'
            ];
            $addressData = $data['address'];
            $addressInsertFields = [];
            $addressInsertValues = [];
            $addressParams = [];

            foreach ($addressFields as $field) {
                if (array_key_exists($field, $addressData)) {
                    $addressInsertFields[] = $field;
                    $addressInsertValues[] = ':' . $field;
                    $addressParams[':' . $field] = $addressData[$field];
                }
            }

            if (!empty($addressInsertFields)) {
                $addressSql = "INSERT INTO invoicing.address (" . implode(', ', $addressInsertFields) . ")
                               VALUES (" . implode(', ', $addressInsertValues) . ")";
                $addressStmt = $conn->prepare($addressSql);
                foreach ($addressParams as $key => $value) {
                    if (is_null($value)) {
                        $addressStmt->bindValue($key, null, PDO::PARAM_NULL);
                    } else {
                        $addressStmt->bindValue($key, $value);
                    }
                }
                $addressStmt->execute();
                $address_id = $conn->lastInsertId();

                // Link client to address
                $linkAddressSql = "INSERT INTO invoicing.client_addresses (client_id, address_id) VALUES (:client_id, :address_id)";
                $linkAddressStmt = $conn->prepare($linkAddressSql);
                $linkAddressStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                $linkAddressStmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
                $linkAddressStmt->execute();
            }
        }

        // 3. Insert contact person if provided
        $contact_id = null;
        if (!empty($data['contact'])) {
            $contactFields = [
                'contact_type_id',
                'first_name',
                'last_name',
                'position',
                'email',
                'phone',
                'cell',
                'is_primary'
            ];
            $contactData = $data['contact'];
            $contactInsertFields = [];
            $contactInsertValues = [];
            $contactParams = [];

            foreach ($contactFields as $field) {
                if (array_key_exists($field, $contactData)) {
                    $contactInsertFields[] = $field;
                    $contactInsertValues[] = ':' . $field;
                    $contactParams[':' . $field] = $contactData[$field];
                }
            }

            if (!empty($contactInsertFields)) {
                $contactSql = "INSERT INTO invoicing.contact_person (" . implode(', ', $contactInsertFields) . ")
                               VALUES (" . implode(', ', $contactInsertValues) . ")";
                $contactStmt = $conn->prepare($contactSql);
                foreach ($contactParams as $key => $value) {
                    if (is_null($value)) {
                        $contactStmt->bindValue($key, null, PDO::PARAM_NULL);
                    } else {
                        $contactStmt->bindValue($key, $value);
                    }
                }
                $contactStmt->execute();
                $contact_id = $conn->lastInsertId();

                // Link client to contact
                $linkContactSql = "INSERT INTO invoicing.client_contacts (client_id, contact_id) VALUES (:client_id, :contact_id)";
                $linkContactStmt = $conn->prepare($linkContactSql);
                $linkContactStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                $linkContactStmt->bindValue(':contact_id', $contact_id, PDO::PARAM_INT);
                $linkContactStmt->execute();
            }
        }

        $conn->commit();
        log_user_action($user_id, 'create_client', $client_id, json_encode($data));
        send_notification($user_id, "Client #$client_id created successfully.");
        return [
            'success' => true,
            'message' => 'Client created successfully',
            'data' => ['client_id' => (int)$client_id]
        ];
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "create_client error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'create_client', null, $msg);
        return [
            'success' => false,
            'message' => 'Failed to create client',
            'data' => null,
            'error_code' => 'CLIENT_CREATE_ERROR'
        ];
    }
}

function update_client(int $client_id, array $data): array {
    global $conn;
    $user_id = $data['updated_by'] ?? ($_SESSION['tech_id'] ?? null);
    if (!check_user_permission($user_id, 'update_client', $client_id)) {
        $msg = "Permission denied for user $user_id to update client $client_id";
        error_log($msg);
        log_user_action($user_id, 'update_client', $client_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    try {
        $conn->beginTransaction();

        // 1. Update client fields
        $clientFields = [
            'client_type',
            'client_name',
            'client_email',
            'client_cell',
            'client_tell',
            'client_status',
            'first_name',
            'last_name',
            'dob',
            'gender',
            'loyalty_level',
            'title',
            'initials',
            'registration_number',
            'vat_number',
            'website',
            'industry'
        ];

        $updateFields = [];
        $params = [];
        foreach ($clientFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        if (!empty($updateFields)) {
            $sql = "UPDATE invoicing.clients SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE client_id = :client_id";
            $params[':client_id'] = $client_id;
            $stmt = $conn->prepare($sql);
            foreach ($params as $key => $value) {
                if (is_null($value)) {
                    $stmt->bindValue($key, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
        }

        // 2. Update or insert address if provided
        if (!empty($data['address'])) {
            $addressFields = [
                'address_type_id',
                'address_line1',
                'address_line2',
                'city',
                'suburb',
                'province',
                'postal_code',
                'country',
                'is_primary'
            ];
            $addressData = $data['address'];

            // Get current address_id for this client (if any)
            $addressIdSql = "SELECT address_id FROM invoicing.client_addresses WHERE client_id = :client_id LIMIT 1";
            $addressIdStmt = $conn->prepare($addressIdSql);
            $addressIdStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
            $addressIdStmt->execute();
            $currentAddress = $addressIdStmt->fetch(PDO::FETCH_ASSOC);

            if ($currentAddress && !empty($currentAddress['address_id'])) {
                // Update existing address
                $address_id = $currentAddress['address_id'];
                $updateAddressFields = [];
                $addressParams = [];
                foreach ($addressFields as $field) {
                    if (array_key_exists($field, $addressData)) {
                        $updateAddressFields[] = "$field = :$field";
                        $addressParams[":$field"] = $addressData[$field];
                    }
                }
                if (!empty($updateAddressFields)) {
                    $addressSql = "UPDATE invoicing.address SET " . implode(', ', $updateAddressFields) . ", updated_at = CURRENT_TIMESTAMP WHERE address_id = :address_id";
                    $addressParams[':address_id'] = $address_id;
                    $addressStmt = $conn->prepare($addressSql);
                    foreach ($addressParams as $key => $value) {
                        if (is_null($value)) {
                            $addressStmt->bindValue($key, null, PDO::PARAM_NULL);
                        } else {
                            $addressStmt->bindValue($key, $value);
                        }
                    }
                    $addressStmt->execute();
                }
            } else {
                // Insert new address and link
                $addressInsertFields = [];
                $addressInsertValues = [];
                $addressParams = [];
                foreach ($addressFields as $field) {
                    if (array_key_exists($field, $addressData)) {
                        $addressInsertFields[] = $field;
                        $addressInsertValues[] = ':' . $field;
                        $addressParams[':' . $field] = $addressData[$field];
                    }
                }
                if (!empty($addressInsertFields)) {
                    $addressSql = "INSERT INTO invoicing.address (" . implode(', ', $addressInsertFields) . ")
                                   VALUES (" . implode(', ', $addressInsertValues) . ")";
                    $addressStmt = $conn->prepare($addressSql);
                    foreach ($addressParams as $key => $value) {
                        if (is_null($value)) {
                            $addressStmt->bindValue($key, null, PDO::PARAM_NULL);
                        } else {
                            $addressStmt->bindValue($key, $value);
                        }
                    }
                    $addressStmt->execute();
                    $address_id = $conn->lastInsertId();

                    // Link client to address
                    $linkAddressSql = "INSERT INTO invoicing.client_addresses (client_id, address_id) VALUES (:client_id, :address_id)";
                    $linkAddressStmt = $conn->prepare($linkAddressSql);
                    $linkAddressStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                    $linkAddressStmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
                    $linkAddressStmt->execute();
                }
            }
        }

        // 3. Update or insert contact person if provided
        if (!empty($data['contact'])) {
            $contactFields = [
                'contact_type_id',
                'first_name',
                'last_name',
                'position',
                'email',
                'phone',
                'cell',
                'is_primary'
            ];
            $contactData = $data['contact'];

            // Get current contact_id for this client (if any)
            $contactIdSql = "SELECT contact_id FROM invoicing.client_contacts WHERE client_id = :client_id LIMIT 1";
            $contactIdStmt = $conn->prepare($contactIdSql);
            $contactIdStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
            $contactIdStmt->execute();
            $currentContact = $contactIdStmt->fetch(PDO::FETCH_ASSOC);

            if ($currentContact && !empty($currentContact['contact_id'])) {
                // Update existing contact
                $contact_id = $currentContact['contact_id'];
                $updateContactFields = [];
                $contactParams = [];
                foreach ($contactFields as $field) {
                    if (array_key_exists($field, $contactData)) {
                        $updateContactFields[] = "$field = :$field";
                        $contactParams[":$field"] = $contactData[$field];
                    }
                }
                if (!empty($updateContactFields)) {
                    $contactSql = "UPDATE invoicing.contact_person SET " . implode(', ', $updateContactFields) . ", updated_at = CURRENT_TIMESTAMP WHERE contact_id = :contact_id";
                    $contactParams[':contact_id'] = $contact_id;
                    $contactStmt = $conn->prepare($contactSql);
                    foreach ($contactParams as $key => $value) {
                        if (is_null($value)) {
                            $contactStmt->bindValue($key, null, PDO::PARAM_NULL);
                        } else {
                            $contactStmt->bindValue($key, $value);
                        }
                    }
                    $contactStmt->execute();
                }
            } else {
                // Insert new contact and link
                $contactInsertFields = [];
                $contactInsertValues = [];
                $contactParams = [];
                foreach ($contactFields as $field) {
                    if (array_key_exists($field, $contactData)) {
                        $contactInsertFields[] = $field;
                        $contactInsertValues[] = ':' . $field;
                        $contactParams[':' . $field] = $contactData[$field];
                    }
                }
                if (!empty($contactInsertFields)) {
                    $contactSql = "INSERT INTO invoicing.contact_person (" . implode(', ', $contactInsertFields) . ")
                                   VALUES (" . implode(', ', $contactInsertValues) . ")";
                    $contactStmt = $conn->prepare($contactSql);
                    foreach ($contactParams as $key => $value) {
                        if (is_null($value)) {
                            $contactStmt->bindValue($key, null, PDO::PARAM_NULL);
                        } else {
                            $contactStmt->bindValue($key, $value);
                        }
                    }
                    $contactStmt->execute();
                    $contact_id = $conn->lastInsertId();

                    // Link client to contact
                    $linkContactSql = "INSERT INTO invoicing.client_contacts (client_id, contact_id) VALUES (:client_id, :contact_id)";
                    $linkContactStmt = $conn->prepare($linkContactSql);
                    $linkContactStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                    $linkContactStmt->bindValue(':contact_id', $contact_id, PDO::PARAM_INT);
                    $linkContactStmt->execute();
                }
            }
        }

        $conn->commit();
        log_user_action($user_id, 'update_client', $client_id, json_encode($data));
        send_notification($user_id, "Client #$client_id updated successfully.");
        return [
            'success' => true,
            'message' => 'Client updated successfully',
            'data' => ['client_id' => $client_id]
        ];
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "update_client error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'update_client', $client_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to update client',
            'data' => null,
            'error_code' => 'CLIENT_UPDATE_ERROR'
        ];
    }
}

function delete_client(int $client_id, int $deleted_by): array {
    $user_id = $deleted_by ?? ($_SESSION['tech_id'] ?? null);
    if (!check_user_permission($user_id, 'delete_client', $client_id)) {
        $msg = "Permission denied for user $user_id to delete client $client_id";
        error_log($msg);
        log_user_action($user_id, 'delete_client', $client_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    global $conn;
    try {
        $sqlCheck = "SELECT COUNT(*) FROM invoicing.documents WHERE client_id = :client_id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $docCount = (int)$stmtCheck->fetchColumn();
        if ($docCount > 0) {
            $msg = 'Client has linked documents, cannot delete';
            error_log($msg);
            log_user_action($user_id, 'delete_client', $client_id, $msg);
            return [
                'success' => false,
                'message' => $msg,
                'data' => null,
                'error_code' => 'CLIENT_LINKED_DOCUMENTS'
            ];
        }
        $conn->beginTransaction();
        $sqlDelete = "UPDATE invoicing.clients SET deleted_at = NOW() WHERE client_id = :client_id";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtDelete->execute();
        $sqlDeleteClientAddresses = "DELETE FROM invoicing.client_addresses WHERE client_id = :client_id";
        $stmtAddr = $conn->prepare($sqlDeleteClientAddresses);
        $stmtAddr->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtAddr->execute();
        $sqlDeleteClientContacts = "DELETE FROM invoicing.client_contacts WHERE client_id = :client_id";
        $stmtCont = $conn->prepare($sqlDeleteClientContacts);
        $stmtCont->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtCont->execute();
        $conn->commit();
        log_user_action($user_id, 'delete_client', $client_id);
        send_notification($user_id, "Client #$client_id deleted.");
        return [
            'success' => true,
            'message' => 'Client deleted successfully',
            'data' => ['client_id' => $client_id]
        ];
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "delete_client error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'delete_client', $client_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to delete client',
            'data' => null,
            'error_code' => 'CLIENT_DELETE_ERROR'
        ];
    }
}
