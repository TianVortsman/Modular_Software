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
        
        return build_success_response($data, 'Clients retrieved successfully', [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ]);
        
    } catch (\PDOException $e) {
        $msg = "Query failed: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'list_clients', null, $msg);
        
        return build_error_response($msg, $options, 'Client listing query failed', 'CLIENT_LIST_ERROR');
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
            
            return build_error_response($msg, ['client_id' => $client_id], 'Client retrieval failed', 'CLIENT_NOT_FOUND');
        }
        
        // Get addresses
        $addressSql = "SELECT a.* FROM invoicing.address a INNER JOIN invoicing.client_addresses ca ON ca.address_id = a.address_id WHERE ca.client_id = :client_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW())";
        $addressStmt = $conn->prepare($addressSql);
        $addressStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $addressStmt->execute();
        $addresses = $addressStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get contacts
        $contactSql = "SELECT cp.* FROM invoicing.contact_person cp INNER JOIN invoicing.client_contacts cc ON cc.contact_id = cp.contact_id WHERE cc.client_id = :client_id AND (cp.deleted_at IS NULL OR cp.deleted_at > NOW())";
        $contactStmt = $conn->prepare($contactSql);
        $contactStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $contactStmt->execute();
        $contacts = $contactStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $client['addresses'] = $addresses;
        $client['contacts'] = $contacts;
        
        return build_success_response($client, 'Client details retrieved successfully');
        
    } catch (\PDOException $e) {
        $msg = "Query failed: " . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'get_client_details', $client_id, $msg);
        
        return build_error_response($msg, ['client_id' => $client_id], 'Client details retrieval failed', 'CLIENT_DETAILS_ERROR');
    }
}

function create_client(array $data): array {
    global $conn;
    
    // Validate required fields
    $requiredFields = ['client_name', 'client_type'];
    $validation = validate_required_fields($data, $requiredFields, 'client creation');
    if ($validation) {
        return $validation;
    }
    
    // Validate email if provided
    if (!empty($data['client_email']) && !validate_email($data['client_email'])) {
        return build_error_response('Invalid email format', $data, 'Client email validation', 'INVALID_EMAIL');
    }

    // Sanitize input data
    $sanitizedData = sanitize_input($data);

    try {
        $conn->beginTransaction();
        
        // Insert client
        $sql = "INSERT INTO invoicing.clients (client_type, client_name, first_name, last_name, client_email, client_cell, client_tell, vat_number, registration_number, created_by, updated_by) 
                VALUES (:client_type, :client_name, :first_name, :last_name, :client_email, :client_cell, :client_tell, :vat_number, :registration_number, :created_by, :updated_by) 
                RETURNING client_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_type', $sanitizedData['client_type']);
        $stmt->bindValue(':client_name', $sanitizedData['client_name']);
        $stmt->bindValue(':first_name', $sanitizedData['first_name'] ?? null);
        $stmt->bindValue(':last_name', $sanitizedData['last_name'] ?? null);
        $stmt->bindValue(':client_email', $sanitizedData['client_email'] ?? null);
        $stmt->bindValue(':client_cell', $sanitizedData['client_cell'] ?? null);
        $stmt->bindValue(':client_tell', $sanitizedData['client_tell'] ?? null);
        $stmt->bindValue(':vat_number', $sanitizedData['vat_number'] ?? null);
        $stmt->bindValue(':registration_number', $sanitizedData['registration_number'] ?? null);
        $stmt->bindValue(':created_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        $stmt->execute();
        $client_id = $stmt->fetchColumn();
        
        if (!$client_id) {
            throw new Exception('Failed to create client - no ID returned');
        }
        
        // Handle addresses if provided
        if (!empty($data['addresses']) && is_array($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                if (empty($address['address_line1'])) continue;
                
                // Insert address
                $addressSql = "INSERT INTO invoicing.address (address_line1, address_line2, city, suburb, province, country, postal_code, created_by, updated_by) 
                              VALUES (:address_line1, :address_line2, :city, :suburb, :province, :country, :postal_code, :created_by, :updated_by) 
                              RETURNING address_id";
                              
                $addressStmt = $conn->prepare($addressSql);
                $addressStmt->bindValue(':address_line1', sanitize_input($address['address_line1']));
                $addressStmt->bindValue(':address_line2', sanitize_input($address['address_line2'] ?? null));
                $addressStmt->bindValue(':city', sanitize_input($address['city'] ?? null));
                $addressStmt->bindValue(':suburb', sanitize_input($address['suburb'] ?? null));
                $addressStmt->bindValue(':province', sanitize_input($address['province'] ?? null));
                $addressStmt->bindValue(':country', sanitize_input($address['country'] ?? null));
                $addressStmt->bindValue(':postal_code', sanitize_input($address['postal_code'] ?? null));
                $addressStmt->bindValue(':created_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                $addressStmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                
                $addressStmt->execute();
                $address_id = $addressStmt->fetchColumn();

                // Link address to client
                $linkSql = "INSERT INTO invoicing.client_addresses (client_id, address_id) VALUES (:client_id, :address_id)";
                $linkStmt = $conn->prepare($linkSql);
                $linkStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                $linkStmt->bindValue(':address_id', $address_id, PDO::PARAM_INT);
                $linkStmt->execute();
            }
        }

        // Handle contacts if provided
        if (!empty($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contact) {
                if (empty($contact['contact_name'])) continue;
                
                // Validate contact email if provided
                if (!empty($contact['contact_email']) && !validate_email($contact['contact_email'])) {
                    throw new Exception('Invalid email format for contact: ' . $contact['contact_name']);
                }
                
                // Insert contact
                $contactSql = "INSERT INTO invoicing.contact_person (contact_name, contact_position, contact_email, contact_phone, created_by, updated_by) 
                              VALUES (:contact_name, :contact_position, :contact_email, :contact_phone, :created_by, :updated_by) 
                              RETURNING contact_id";
                              
                $contactStmt = $conn->prepare($contactSql);
                $contactStmt->bindValue(':contact_name', sanitize_input($contact['contact_name']));
                $contactStmt->bindValue(':contact_position', sanitize_input($contact['contact_position'] ?? null));
                $contactStmt->bindValue(':contact_email', sanitize_input($contact['contact_email'] ?? null));
                $contactStmt->bindValue(':contact_phone', sanitize_input($contact['contact_phone'] ?? null));
                $contactStmt->bindValue(':created_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                $contactStmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                
                $contactStmt->execute();
                $contact_id = $contactStmt->fetchColumn();

                // Link contact to client
                $linkSql = "INSERT INTO invoicing.client_contacts (client_id, contact_id) VALUES (:client_id, :contact_id)";
                $linkStmt = $conn->prepare($linkSql);
                $linkStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                $linkStmt->bindValue(':contact_id', $contact_id, PDO::PARAM_INT);
                $linkStmt->execute();
            }
        }

        $conn->commit();
        
        // Log the action
        log_user_action($_SESSION['user_id'] ?? null, 'create_client', $client_id, 'Client created successfully', 'invoicing', 'client', null, $sanitizedData);
        
        // Get the created client details to return
        $result = get_client_details($client_id);
        if ($result['success']) {
            return build_success_response($result['data'], 'Client created successfully', ['client_id' => $client_id]);
        } else {
            return build_success_response(['client_id' => $client_id], 'Client created successfully, but failed to retrieve details', ['client_id' => $client_id]);
        }
        
    } catch (\PDOException $e) {
        $conn->rollBack();
        $msg = "Database error during client creation: " . $e->getMessage();
        error_log($msg);
        log_user_action($_SESSION['user_id'] ?? null, 'create_client_failed', null, $msg);
        
        return build_error_response($msg, $data, 'Client creation failed', 'CLIENT_CREATE_ERROR');
        
    } catch (Exception $e) {
            $conn->rollBack();
        $msg = "Error during client creation: " . $e->getMessage();
        error_log($msg);
        log_user_action($_SESSION['user_id'] ?? null, 'create_client_failed', null, $msg);
        
        return build_error_response($msg, $data, 'Client creation failed', 'CLIENT_CREATE_ERROR');
    }
}

function update_client(array $data): array {
    global $conn;
    
    // Validate required fields
    $requiredFields = ['client_id', 'client_name', 'client_type'];
    $validation = validate_required_fields($data, $requiredFields, 'client update');
    if ($validation) {
        return $validation;
    }
    
    $client_id = (int)$data['client_id'];
    
    // Validate email if provided
    if (!empty($data['client_email']) && !validate_email($data['client_email'])) {
        return build_error_response('Invalid email format', $data, 'Client email validation', 'INVALID_EMAIL');
    }
    
    // Sanitize input data
    $sanitizedData = sanitize_input($data);
    
    try {
        $conn->beginTransaction();

        // Check if client exists
        $checkSql = "SELECT client_id FROM invoicing.clients WHERE client_id = :client_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if (!$checkStmt->fetchColumn()) {
            return build_error_response('Client not found', $data, 'Client update validation', 'CLIENT_NOT_FOUND');
        }
        
        // Update client
        $sql = "UPDATE invoicing.clients SET 
                client_type = :client_type, 
                client_name = :client_name, 
                first_name = :first_name, 
                last_name = :last_name, 
                client_email = :client_email, 
                client_cell = :client_cell, 
                client_tell = :client_tell, 
                vat_number = :vat_number, 
                registration_number = :registration_number, 
                updated_by = :updated_by, 
                updated_at = NOW() 
                WHERE client_id = :client_id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindValue(':client_type', $sanitizedData['client_type']);
        $stmt->bindValue(':client_name', $sanitizedData['client_name']);
        $stmt->bindValue(':first_name', $sanitizedData['first_name'] ?? null);
        $stmt->bindValue(':last_name', $sanitizedData['last_name'] ?? null);
        $stmt->bindValue(':client_email', $sanitizedData['client_email'] ?? null);
        $stmt->bindValue(':client_cell', $sanitizedData['client_cell'] ?? null);
        $stmt->bindValue(':client_tell', $sanitizedData['client_tell'] ?? null);
        $stmt->bindValue(':vat_number', $sanitizedData['vat_number'] ?? null);
        $stmt->bindValue(':registration_number', $sanitizedData['registration_number'] ?? null);
        $stmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
        
        $stmt->execute();
        
        // Handle address update if provided
        if (!empty($data['addresses']) && is_array($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                if (empty($address['address_line1'])) continue;
                
                // Get existing address for this client
                $existingAddressSql = "SELECT a.address_id FROM invoicing.address a 
                                      JOIN invoicing.client_addresses ca ON a.address_id = ca.address_id 
                                      WHERE ca.client_id = :client_id AND (a.deleted_at IS NULL OR a.deleted_at > NOW())
                                      LIMIT 1";
                $existingAddressStmt = $conn->prepare($existingAddressSql);
                $existingAddressStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                $existingAddressStmt->execute();
                $existingAddressId = $existingAddressStmt->fetchColumn();
                
                if ($existingAddressId) {
                    // Update existing address
                    $updateAddressSql = "UPDATE invoicing.address SET 
                                        address_line1 = :address_line1,
                                        address_line2 = :address_line2,
                                        city = :city,
                                        suburb = :suburb,
                                        province = :province,
                                        country = :country,
                                        postal_code = :postal_code,
                                        updated_by = :updated_by,
                                        updated_at = NOW()
                                        WHERE address_id = :address_id";
                                        
                    $updateAddressStmt = $conn->prepare($updateAddressSql);
                    $updateAddressStmt->bindValue(':address_id', $existingAddressId, PDO::PARAM_INT);
                    $updateAddressStmt->bindValue(':address_line1', sanitize_input($address['address_line1']));
                    $updateAddressStmt->bindValue(':address_line2', sanitize_input($address['address_line2'] ?? null));
                    $updateAddressStmt->bindValue(':city', sanitize_input($address['city'] ?? null));
                    $updateAddressStmt->bindValue(':suburb', sanitize_input($address['suburb'] ?? null));
                    $updateAddressStmt->bindValue(':province', sanitize_input($address['province'] ?? null));
                    $updateAddressStmt->bindValue(':country', sanitize_input($address['country'] ?? null));
                    $updateAddressStmt->bindValue(':postal_code', sanitize_input($address['postal_code'] ?? null));
                    $updateAddressStmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                    $updateAddressStmt->execute();
                } else {
                    // Create new address
                    $newAddressSql = "INSERT INTO invoicing.address (address_line1, address_line2, city, suburb, province, country, postal_code, created_by, updated_by) 
                                     VALUES (:address_line1, :address_line2, :city, :suburb, :province, :country, :postal_code, :created_by, :updated_by) 
                                     RETURNING address_id";
                                     
                    $newAddressStmt = $conn->prepare($newAddressSql);
                    $newAddressStmt->bindValue(':address_line1', sanitize_input($address['address_line1']));
                    $newAddressStmt->bindValue(':address_line2', sanitize_input($address['address_line2'] ?? null));
                    $newAddressStmt->bindValue(':city', sanitize_input($address['city'] ?? null));
                    $newAddressStmt->bindValue(':suburb', sanitize_input($address['suburb'] ?? null));
                    $newAddressStmt->bindValue(':province', sanitize_input($address['province'] ?? null));
                    $newAddressStmt->bindValue(':country', sanitize_input($address['country'] ?? null));
                    $newAddressStmt->bindValue(':postal_code', sanitize_input($address['postal_code'] ?? null));
                    $newAddressStmt->bindValue(':created_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                    $newAddressStmt->bindValue(':updated_by', $_SESSION['user_id'] ?? null, PDO::PARAM_INT);
                    
                    $newAddressStmt->execute();
                    $newAddressId = $newAddressStmt->fetchColumn();
                    
                    // Link new address to client
                    $linkSql = "INSERT INTO invoicing.client_addresses (client_id, address_id) VALUES (:client_id, :address_id)";
                    $linkStmt = $conn->prepare($linkSql);
                    $linkStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
                    $linkStmt->bindValue(':address_id', $newAddressId, PDO::PARAM_INT);
                    $linkStmt->execute();
                }
            }
        }
        
        $conn->commit();
        
        // Log the action
        log_user_action($_SESSION['user_id'] ?? null, 'update_client', $client_id, 'Client updated successfully', 'invoicing', 'client', null, $sanitizedData);
        
        // Get the updated client details to return
        $result = get_client_details($client_id);
        if ($result['success']) {
            return build_success_response($result['data'], 'Client updated successfully');
                        } else {
            return build_success_response(['client_id' => $client_id], 'Client updated successfully, but failed to retrieve updated details');
        }
        
    } catch (\PDOException $e) {
        $conn->rollBack();
        $msg = "Database error during client update: " . $e->getMessage();
        error_log($msg);
        log_user_action($_SESSION['user_id'] ?? null, 'update_client_failed', $client_id, $msg);
        
        return build_error_response($msg, $data, 'Client update failed', 'CLIENT_UPDATE_ERROR');
        
    } catch (Exception $e) {
            $conn->rollBack();
        $msg = "Error during client update: " . $e->getMessage();
        error_log($msg);
        log_user_action($_SESSION['user_id'] ?? null, 'update_client_failed', $client_id, $msg);
        
        return build_error_response($msg, $data, 'Client update failed', 'CLIENT_UPDATE_ERROR');
    }
}

function delete_client(int $client_id, int $deleted_by): array {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Check if client exists
        $checkSql = "SELECT client_id, client_name FROM invoicing.clients WHERE client_id = :client_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $client = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$client) {
            return build_error_response('Client not found', ['client_id' => $client_id], 'Client deletion validation', 'CLIENT_NOT_FOUND');
        }
        
        // Check if client has associated documents
        $docCheckSql = "SELECT COUNT(*) FROM invoicing.documents WHERE client_id = :client_id";
        $docCheckStmt = $conn->prepare($docCheckSql);
        $docCheckStmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $docCheckStmt->execute();
        $docCount = $docCheckStmt->fetchColumn();
        
        if ($docCount > 0) {
            return build_error_response(
                "Cannot delete client '{$client['client_name']}' because it has $docCount associated documents. Please delete or reassign the documents first.",
                ['client_id' => $client_id, 'client_name' => $client['client_name'], 'document_count' => $docCount],
                'Client deletion validation',
                'CLIENT_HAS_DOCUMENTS'
            );
        }
        
        // Soft delete client
        $sql = "UPDATE invoicing.clients SET deleted_by = :deleted_by, deleted_at = NOW() WHERE client_id = :client_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':client_id', $client_id, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_by', $deleted_by, PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        
        // Log the action
        log_user_action($deleted_by, 'delete_client', $client_id, "Client '{$client['client_name']}' deleted successfully", 'invoicing', 'client', $client, null);
        
        return build_success_response(null, "Client '{$client['client_name']}' deleted successfully");
        
    } catch (\PDOException $e) {
        $conn->rollBack();
        $msg = "Database error during client deletion: " . $e->getMessage();
        error_log($msg);
        log_user_action($deleted_by, 'delete_client_failed', $client_id, $msg);
        
        return build_error_response($msg, ['client_id' => $client_id, 'deleted_by' => $deleted_by], 'Client deletion failed', 'CLIENT_DELETE_ERROR');
        
    } catch (Exception $e) {
            $conn->rollBack();
        $msg = "Error during client deletion: " . $e->getMessage();
        error_log($msg);
        log_user_action($deleted_by, 'delete_client_failed', $client_id, $msg);
        
        return build_error_response($msg, ['client_id' => $client_id, 'deleted_by' => $deleted_by], 'Client deletion failed', 'CLIENT_DELETE_ERROR');
    }
}
