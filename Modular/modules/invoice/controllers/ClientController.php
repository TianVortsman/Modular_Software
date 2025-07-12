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

    try {
        // 1. Get client main details
        $sql = "SELECT *
                FROM invoicing.clients
                WHERE client_id = :client_id
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->execute();
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            return null;
        }

        // 2. Get addresses
        $addressSql = "SELECT a.*
                       FROM invoicing.address a
                       INNER JOIN invoicing.client_addresses ca ON ca.address_id = a.address_id
                       WHERE ca.client_id = :client_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW())";
        $addressStmt = $conn->prepare($addressSql);
        $addressStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $addressStmt->execute();
        $addresses = $addressStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Get contacts
        $contactSql = "SELECT cp.*
                       FROM invoicing.contact_person cp
                       INNER JOIN invoicing.client_contacts cc ON cc.contact_id = cp.contact_id
                       WHERE cc.client_id = :client_id AND (cp.deleted_at IS NULL OR cp.deleted_at > NOW())";
        $contactStmt = $conn->prepare($contactSql);
        $contactStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $contactStmt->execute();
        $contacts = $contactStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Return all details
        $client['addresses'] = $addresses;
        $client['contacts'] = $contacts;

        return $client;

    } catch (\PDOException $e) {
        error_log("Error in get_client_details: " . $e->getMessage());
        return null;
    }
}

function create_client(array $data): ?int {
    global $conn;

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
        error_log("create_client error: No valid fields provided.");
        return null;
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
        return (int)$client_id;
    } catch (\PDOException $e) {
        $conn->rollBack();
        error_log("create_client error: " . $e->getMessage());
        return null;
    }
}

function update_client(int $client_id, array $data): bool {
    global $conn;

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
        return true;
    } catch (\PDOException $e) {
        $conn->rollBack();
        error_log("update_client error: " . $e->getMessage());
        return false;
    }
}

function delete_client(int $client_id): bool {
    global $conn;

    try {
        // Check for linked documents
        $sqlCheck = "SELECT COUNT(*) FROM invoicing.documents WHERE client_id = :client_id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $docCount = (int)$stmtCheck->fetchColumn();

        if ($docCount > 0) {
            // Client has linked documents, cannot delete
            return false;
        }

        $conn->beginTransaction();

        // Soft delete: set deleted_at timestamp
        $sqlDelete = "UPDATE invoicing.clients SET deleted_at = NOW() WHERE client_id = :client_id";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Optionally, also soft-delete related addresses and contacts links
        // (not the address/contact themselves, as they may be shared)
        $sqlDeleteClientAddresses = "DELETE FROM invoicing.client_addresses WHERE client_id = :client_id";
        $stmtAddr = $conn->prepare($sqlDeleteClientAddresses);
        $stmtAddr->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtAddr->execute();

        $sqlDeleteClientContacts = "DELETE FROM invoicing.client_contacts WHERE client_id = :client_id";
        $stmtCont = $conn->prepare($sqlDeleteClientContacts);
        $stmtCont->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmtCont->execute();

        $conn->commit();
        return true;
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("delete_client error: " . $e->getMessage());
        return false;
    }
}
