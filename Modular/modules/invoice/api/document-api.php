<?php
require_once __DIR__ . '/../../../src/Utils/errorHandler.php';
require_once __DIR__ . '/../../../src/Helpers/helpers.php';
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;
require_once __DIR__ . '/../controllers/SalesController.php';
require_once __DIR__ . '/../controllers/DocumentController.php';

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

    // Handle mock search endpoints
    if ($action === 'search_salesperson') {
        require_once __DIR__ . '/../controllers/SalesController.php';
        $query = $_GET['query'] ?? '';
        $result = \App\modules\invoice\controllers\search_salesperson($query);
        // Only return id and name
        $data = [];
        if ($result['success'] && is_array($result['data'])) {
            foreach ($result['data'] as $sp) {
                $data[] = [
                    'salesperson_id' => $sp['employee_id'],
                    'salesperson_name' => trim(($sp['employee_first_name'] ?? '') . ' ' . ($sp['employee_last_name'] ?? ''))
                ];
            }
        }
        echo json_encode(['success' => true, 'results' => $data]);
        exit;
    }
    if ($action === 'search_product') {
        // Return mock product search results
        $results = [
            [ 'product_id' => 1, 'sku' => 'P1001', 'product_name' => 'Widget A', 'product_description' => 'A Widget', 'product_price' => 100, 'tax_rate' => 15 ],
            [ 'product_id' => 2, 'sku' => 'P1002', 'product_name' => 'Widget B', 'product_description' => 'B Widget', 'product_price' => 200, 'tax_rate' => 15 ],
            [ 'product_id' => 3, 'sku' => 'P1003', 'product_name' => 'Widget C', 'product_description' => 'C Widget', 'product_price' => 300, 'tax_rate' => 15 ]
        ];
        $query = $_GET['query'] ?? '';
        $filtered = array_filter($results, function($p) use ($query) {
            return stripos($p['product_name'], $query) !== false || stripos($p['sku'], $query) !== false;
        });
        echo json_encode(array_values($filtered));
        exit;
    }

    switch ($action) {
        case 'get_company_info':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_company_info action');
            }
            $stmt = $conn->prepare("SELECT * FROM settings.invoice_settings WHERE id = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $data = [
                'company_name' => $result['company_name'] ?? '',
                'company_address' => $result['company_address'] ?? '',
                'company_phone' => $result['company_phone'] ?? '',
                'company_email' => $result['company_email'] ?? '',
                'vat_number' => $result['vat_number'] ?? '',
                'registration_number' => $result['registration_number'] ?? ''
            ];
            echo json_encode([
                'success' => true,
                'message' => 'Company information retrieved successfully',
                'data' => $data
            ]);
            break;
        case 'get_invoice_number':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_invoice_number action');
            }
            $stmt = $conn->prepare("SELECT invoice_prefix, current_number, starting_number FROM settings.invoice_settings WHERE id = 1");
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$settings) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invoice numbering settings not found',
                    'data' => null
                ]);
                break;
            }
            $prefix = $settings['invoice_prefix'] ?? '';
            $current = $settings['current_number'] ?? null;
            $start = $settings['starting_number'] ?? 1;
            $next = ($current !== null && $current > 0) ? $current + 1 : $start;
            $invoice_number = $prefix . $next;
            echo json_encode([
                'success' => true,
                'message' => 'Next invoice number generated',
                'data' => [ 'invoice_number' => $invoice_number ]
            ]);
            break;
        case 'search_salesperson':
            if ($method !== 'GET') {
                throw new Exception('GET method required for search_salesperson action');
            }
            $query = $_GET['query'] ?? '';
            $query = '%' . strtolower($query) . '%';
            $stmt = $conn->prepare("SELECT core.employees.employee_id, core.employees.first_name, core.employees.last_name, core.employees.employee_number, core.employee_contact.email FROM core.employees LEFT JOIN core.employee_contact ON core.employees.employee_id = core.employee_contact.employee_id WHERE is_sales = true AND LOWER(core.employees.first_name || ' ' || core.employees.last_name) LIKE :q LIMIT 10");
            $stmt->execute(['q' => $query]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
            break;
        case 'fetch_invoice':
            // @phpstan-ignore-next-line (legacy InvoiceController type error)
            require_once __DIR__ . '/../controllers/InvoiceController.php';
            if ($method !== 'GET') {
                throw new Exception('GET method required for fetch_invoice action');
            }
            $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : null;
            if (!$invoice_id) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid invoice_id',
                    'data' => null
                ]);
                break;
            }
            $controller = new \App\modules\invoice\controllers\InvoiceController($conn);
            $result = $controller->fetchInvoice($invoice_id);
            echo json_encode($result);
            break;
        case 'save_invoice':
            require_once __DIR__ . '/../controllers/InvoiceController.php';
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_invoice action');
            }
            $data = json_decode(file_get_contents('php://input'), true);
            error_log('[save_invoice] Incoming data: ' . json_encode($data));
            $invoiceId = isset($data['invoice_id']) && $data['invoice_id'] ? intval($data['invoice_id']) : null;
            error_log('[save_invoice] invoiceId: ' . print_r($invoiceId, true));
            if ($invoiceId) {
                error_log('[save_invoice] UPDATE block for invoiceId: ' . $invoiceId);
                // UPDATE existing invoice and items, then exit
                try {
                    $conn->beginTransaction();
                    $clientType = $data['client_type'] ?? null;
                    $customerId = null;
                    $companyId = null;
                    if ($clientType === 'company') {
                        $companyId = clean_int($data['company_id'] ?? null);
                    } else if ($clientType === 'customer') {
                        $customerId = clean_int($data['customer_id'] ?? null);
                    }
                    // Set status_id dynamically if provided, else default to 1 (draft)
                    $statusId = isset($data['status_id']) ? intval($data['status_id']) : (isset($data['status']) && strtolower($data['status']) === 'approved' ? 2 : 1);
                    $stmt = $conn->prepare('UPDATE invoicing.invoices SET customer_id=?, company_id=?, employee_id=?, invoice_type=?, invoice_number=?, invoice_date=?, due_date=?, pay_in_days=?, status_id=?, subtotal=?, discount_amount=?, tax_amount=?, total_amount=?, notes=?, updated_at=NOW() WHERE invoice_id=?');
                    $stmt->execute([
                        $customerId,
                        $companyId,
                        clean_int($data['salesperson']['employee_id'] ?? null),
                        $data['invoice_type'] ?? 'quotation',
                        $data['invoice_number'],
                        $data['invoice_date'] ?? date('Y-m-d'),
                        $data['due_date'] ?? null,
                        clean_int($data['pay_in_days'] ?? null),
                        $statusId,
                        clean_numeric($data['totals']['subtotal'] ?? 0),
                        clean_numeric($data['totals']['discount'] ?? 0),
                        clean_numeric($data['totals']['tax'] ?? 0),
                        clean_numeric($data['totals']['total'] ?? 0),
                        is_array($data['notes']) ? implode("\n", $data['notes']) : ($data['notes'] ?? null),
                        $invoiceId
                    ]);
                    // Update recurring if present
                    if (!empty($data['is_recurring']) && !empty($data['recurring_id'])) {
                        $recurringId = intval($data['recurring_id']);
                        $recurringFields = [];
                        $recurringParams = [];
                        if (isset($data['recurring_frequency'])) {
                            $recurringFields[] = 'frequency=?';
                            $recurringParams[] = $data['recurring_frequency'];
                        }
                        if (isset($data['recurring_start_date'])) {
                            $recurringFields[] = 'start_date=?';
                            $recurringParams[] = $data['recurring_start_date'];
                        }
                        if (isset($data['recurring_end_date'])) {
                            $recurringFields[] = 'end_date=?';
                            $recurringParams[] = $data['recurring_end_date'];
                        }
                        if (isset($data['last_generated'])) {
                            $recurringFields[] = 'last_generated=?';
                            $recurringParams[] = $data['last_generated'];
                        }
                        if (isset($data['next_generation'])) {
                            $recurringFields[] = 'next_generation=?';
                            $recurringParams[] = $data['next_generation'];
                        }
                        if (!empty($recurringFields)) {
                            $recurringParams[] = $recurringId;
                            $sql = 'UPDATE invoicing.recurring_invoices SET ' . implode(', ', $recurringFields) . ', updated_at=NOW() WHERE recurring_id=?';
                            $recurringStmt = $conn->prepare($sql);
                            $recurringStmt->execute($recurringParams);
                        }
                    }
                    // Handle items: update existing, insert new, delete removed
                    $existingItems = [];
                    $stmt = $conn->prepare('SELECT item_id FROM invoicing.invoice_items WHERE invoice_id = ?');
                    $stmt->execute([$invoiceId]);
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $existingItems[$row['item_id']] = true;
                    }
                    $sentItemIds = [];
                    if (!empty($data['items']) && is_array($data['items'])) {
                        foreach ($data['items'] as $item) {
                            $itemId = isset($item['item_id']) ? intval($item['item_id']) : null;
                            $sentItemIds[] = $itemId;
                            if ($itemId && isset($existingItems[$itemId])) {
                                // Update existing item
                                $itemStmt = $conn->prepare('UPDATE invoicing.invoice_items SET product_id=?, description=?, quantity=?, unit_price=?, tax_rate_id=?, line_total=?, updated_at=NOW() WHERE item_id=?');
                                $itemStmt->execute([
                                    clean_int($item['product_id'] ?? null),
                                    $item['description'] ?? '',
                                    $item['qty'] ?? 1,
                                    clean_numeric($item['unit_price'] ?? 0),
                                    clean_int($item['tax_rate_id'] ?? null),
                                    clean_numeric($item['total'] ?? 0),
                                    $itemId
                                ]);
                            } else {
                                // Insert new item
                                $itemStmt = $conn->prepare('INSERT INTO invoicing.invoice_items (invoice_id, product_id, description, quantity, unit_price, tax_rate_id, line_total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                                $itemStmt->execute([
                                    $invoiceId,
                                    clean_int($item['product_id'] ?? null),
                                    $item['description'] ?? '',
                                    $item['qty'] ?? 1,
                                    clean_numeric($item['unit_price'] ?? 0),
                                    clean_int($item['tax_rate_id'] ?? null),
                                    clean_numeric($item['total'] ?? 0)
                                ]);
                            }
                        }
                    }
                    // Delete items not in sent list
                    $toDelete = array_diff(array_keys($existingItems), array_filter($sentItemIds));
                    foreach ($toDelete as $delId) {
                        $delStmt = $conn->prepare('DELETE FROM invoicing.invoice_items WHERE item_id = ?');
                        $delStmt->execute([$delId]);
                    }
                    $conn->commit();
                    error_log('[save_invoice] Executed UPDATE invoicing.invoices for invoiceId: ' . $invoiceId);
                    echo json_encode(['success' => true, 'message' => 'Invoice updated', 'invoice_id' => $invoiceId]);
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('[save_invoice] Update failed for invoiceId: ' . $invoiceId . ' - ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Failed to update invoice: ' . $e->getMessage()]);
                }
                error_log('[save_invoice] Exiting after update block for invoiceId: ' . $invoiceId);
                exit;
            }

            
            $isDraft = (empty($data['invoice_number']) || (isset($data['status']) && strtolower($data['status']) === 'draft'));
            $statusId = 1; // Default to Draft
            // Fetch status_id for Draft from invoice_status table
            $stmt = $conn->prepare('SELECT status_id FROM invoicing.invoice_status WHERE LOWER(status_name) = ? LIMIT 1');
            $stmt->execute(['draft']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row['status_id'])) $statusId = $row['status_id'];
            error_log('[save_invoice] isDraft: ' . print_r($isDraft, true));
            if ($isDraft) {
                error_log('[save_invoice] DRAFT block');
                // Generate a unique placeholder for invoice_number for drafts
                $draftNumber = 'DRAFT-' . date('Ymd-His') . '-' . rand(1000,9999);
                try {
                    $conn->beginTransaction();
                    $clientType = $data['client_type'] ?? null;
                    $customerId = null;
                    $companyId = null;
                    if ($clientType === 'company') {
                        $companyId = clean_int($data['company_id'] ?? null);
                    } else if ($clientType === 'customer') {
                        $customerId = clean_int($data['customer_id'] ?? null);
                    }
                    $isRecurring = !empty($data['is_recurring']);
                    $recurringId = null;
                    if ($isRecurring) {
                        // Prepare recurring fields
                        $recurringFrequency = $data['recurring_frequency'] ?? 'monthly';
                        $recurringStartDate = $data['recurring_start_date'] ?? null;
                        $recurringEndDate = $data['recurring_end_date'] ?? null;
                        $nextGeneration = $data['next_generation'] ?? null;
                        $lastGenerated = $data['last_generated'] ?? null;
                        $recurringStatus = 'active';
                        // Support both customer and company as client
                        $recurringCustomerId = $clientType === 'customer' ? $customerId : null;
                        $recurringCompanyId = $clientType === 'company' ? $companyId : null;
                        $recurringStmt = $conn->prepare('INSERT INTO invoicing.recurring_invoices (customer_id, company_id, frequency, start_date, end_date, last_generated, next_generation, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING recurring_id');
                        $recurringStmt->execute([
                            $recurringCustomerId,
                            $recurringCompanyId,
                            $recurringFrequency,
                            $recurringStartDate,
                            $recurringEndDate,
                            $lastGenerated,
                            $nextGeneration,
                            $recurringStatus
                        ]);
                        $recurringId = $recurringStmt->fetchColumn();
                    }
                    $stmt = $conn->prepare('INSERT INTO invoicing.invoices (customer_id, company_id, recurring_id, employee_id, invoice_type, invoice_number, invoice_date, due_date, pay_in_days, status_id, subtotal, discount_amount, tax_amount, total_amount, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING invoice_id');
                    $stmt->execute([
                        $customerId,
                        $companyId,
                        $recurringId,
                        clean_int($data['salesperson']['employee_id'] ?? null),
                        $data['invoice_type'] ?? 'quotation',
                        $draftNumber,
                        $data['invoice_date'] ?? date('Y-m-d'),
                        $data['due_date'] ?? null,
                        clean_int($data['pay_in_days'] ?? null),
                        $statusId,
                        clean_numeric($data['totals']['subtotal'] ?? 0),
                        clean_numeric($data['totals']['discount'] ?? 0),
                        clean_numeric($data['totals']['tax'] ?? 0),
                        clean_numeric($data['totals']['total'] ?? 0),
                        is_array($data['notes']) ? implode("\n", $data['notes']) : ($data['notes'] ?? null)
                    ]);
                    $invoiceId = $stmt->fetchColumn();
                    // Insert items
                    if (!empty($data['items']) && is_array($data['items'])) {
                        foreach ($data['items'] as $item) {
                            $itemStmt = $conn->prepare('INSERT INTO invoicing.invoice_items (invoice_id, product_id, description, quantity, unit_price, tax_rate_id, line_total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                            $itemStmt->execute([
                                $invoiceId,
                                clean_int($item['product_id'] ?? null),
                                $item['description'] ?? '',
                                $item['qty'] ?? 1,
                                clean_numeric($item['unit_price'] ?? 0),
                                clean_int($item['tax_rate_id'] ?? null),
                                clean_numeric($item['total'] ?? 0)
                            ]);
                        }
                    }
                    $conn->commit();
                    error_log('[save_invoice] Inserted draft invoiceId: ' . $invoiceId);
                    error_log('[save_invoice] Committing draft insert for invoiceId: ' . $invoiceId);
                    echo json_encode(['success' => true, 'message' => 'Draft saved', 'data' => ['invoice_id' => $invoiceId]]);
                    error_log('[save_invoice] Exiting after draft block for invoiceId: ' . $invoiceId);
                    exit;
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('[save_invoice] Draft failed for invoiceId: ' . $invoiceId . ' - ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Failed to save draft: ' . $e->getMessage()]);
                }
                error_log('[save_invoice] Exiting after draft block for invoiceId: ' . $invoiceId);
                exit;
            } else {
                error_log('[save_invoice] FINALIZE block');
                // Finalize: assign/increment number, set status to Approved
                $invoiceType = $data['invoice_type'] ?? 'quotation';
                $numberField = $invoiceType === 'quotation' ? 'quotation_current_number' : 'invoice_current_number';
                $prefixField = $invoiceType === 'quotation' ? 'quotation_prefix' : 'invoice_prefix';
                $stmt = $conn->prepare('SELECT status_id FROM invoicing.invoice_status WHERE LOWER(status_name) = ? LIMIT 1');
                $stmt->execute(['approved']);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $approvedStatusId = $row && isset($row['status_id']) ? $row['status_id'] : 2;
                try {
                    $conn->beginTransaction();
                    $stmt = $conn->prepare("UPDATE settings.invoice_settings SET $numberField = $numberField + 1 WHERE id = 1");
                    $stmt->execute();
                    $clientType = $data['client_type'] ?? null;
                    $customerId = null;
                    $companyId = null;
                    if ($clientType === 'company') {
                        $companyId = clean_int($data['company_id'] ?? null);
                    } else if ($clientType === 'customer') {
                        $customerId = clean_int($data['customer_id'] ?? null);
                    }
                    $isRecurring = !empty($data['is_recurring']);
                    $recurringId = null;
                    if ($isRecurring) {
                        // Prepare recurring fields
                        $recurringFrequency = $data['recurring_frequency'] ?? 'monthly';
                        $recurringStartDate = $data['recurring_start_date'] ?? null;
                        $recurringEndDate = $data['recurring_end_date'] ?? null;
                        $nextGeneration = $data['next_generation'] ?? null;
                        $lastGenerated = $data['last_generated'] ?? null;
                        $recurringStatus = 'active';
                        // Support both customer and company as client
                        $recurringCustomerId = $clientType === 'customer' ? $customerId : null;
                        $recurringCompanyId = $clientType === 'company' ? $companyId : null;
                        $recurringStmt = $conn->prepare('INSERT INTO invoicing.recurring_invoices (customer_id, company_id, frequency, start_date, end_date, last_generated, next_generation, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING recurring_id');
                        $recurringStmt->execute([
                            $recurringCustomerId,
                            $recurringCompanyId,
                            $recurringFrequency,
                            $recurringStartDate,
                            $recurringEndDate,
                            $lastGenerated,
                            $nextGeneration,
                            $recurringStatus
                        ]);
                        $recurringId = $recurringStmt->fetchColumn();
                    }
                    $stmt = $conn->prepare('INSERT INTO invoicing.invoices (customer_id, company_id, recurring_id, employee_id, invoice_type, invoice_number, invoice_date, due_date, pay_in_days, status_id, subtotal, discount_amount, tax_amount, total_amount, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING invoice_id');
                    $stmt->execute([
                        $customerId,
                        $companyId,
                        $recurringId,
                        clean_int($data['salesperson']['employee_id'] ?? null),
                        $invoiceType,
                        $data['invoice_number'],
                        $data['invoice_date'] ?? date('Y-m-d'),
                        $data['due_date'] ?? null,
                        clean_int($data['pay_in_days'] ?? null),
                        $approvedStatusId,
                        clean_numeric($data['totals']['subtotal'] ?? 0),
                        clean_numeric($data['totals']['discount'] ?? 0),
                        clean_numeric($data['totals']['tax'] ?? 0),
                        clean_numeric($data['totals']['total'] ?? 0),
                        is_array($data['notes']) ? implode("\n", $data['notes']) : ($data['notes'] ?? null)
                    ]);
                    $invoiceId = $stmt->fetchColumn();
                    // Insert items
                    if (!empty($data['items']) && is_array($data['items'])) {
                        foreach ($data['items'] as $item) {
                            $itemStmt = $conn->prepare('INSERT INTO invoicing.invoice_items (invoice_id, product_id, description, quantity, unit_price, tax_rate_id, line_total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                            $itemStmt->execute([
                                $invoiceId,
                                clean_int($item['product_id'] ?? null),
                                $item['description'] ?? '',
                                $item['qty'] ?? 1,
                                clean_numeric($item['unit_price'] ?? 0),
                                clean_int($item['tax_rate_id'] ?? null),
                                clean_numeric($item['total'] ?? 0)
                            ]);
                        }
                    }
                    $conn->commit();
                    error_log('[save_invoice] Inserted finalized invoiceId: ' . $invoiceId);
                    error_log('[save_invoice] Committing finalized insert for invoiceId: ' . $invoiceId);
                    echo json_encode(['success' => true, 'message' => 'Invoice finalized', 'number' => $data['invoice_number'], 'invoice_id' => $invoiceId]);
                    error_log('[save_invoice] Exiting after finalize block for invoiceId: ' . $invoiceId);
                    exit;
                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log('[save_invoice] Finalized failed for invoiceId: ' . $invoiceId . ' - ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Failed to finalize invoice: ' . $e->getMessage()]);
                }
                error_log('[save_invoice] Exiting after finalize block for invoiceId: ' . $invoiceId);
                exit;
            }
            break;
        case 'preview_quotation_number':
            if ($method !== 'GET') {
                throw new Exception('GET method required for preview_quotation_number action');
            }
            $stmt = $conn->prepare('SELECT quotation_prefix, quotation_current_number, quotation_starting_number FROM settings.invoice_settings WHERE id = 1');
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $prefix = $row['quotation_prefix'] ?? 'Q-';
            $current = $row['quotation_current_number'] ?? null;
            $start = $row['quotation_starting_number'] ?? 1;
            $next = $current ? $current + 1 : $start;
            echo json_encode(['success' => true, 'number' => $prefix . $next]);
                break;
        case 'preview_invoice_number':
            if ($method !== 'GET') {
                throw new Exception('GET method required for preview_invoice_number action');
            }
            $stmt = $conn->prepare('SELECT invoice_prefix, invoice_current_number, invoice_starting_number FROM settings.invoice_settings WHERE id = 1');
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $prefix = $row['invoice_prefix'] ?? 'INV-';
            $current = $row['invoice_current_number'] ?? null;
            $start = $row['invoice_starting_number'] ?? 1;
            $next = $current ? $current + 1 : $start;
            echo json_encode(['success' => true, 'number' => $prefix . $next]);
            break;
        case 'list_documents':
            if ($method !== 'GET') {
                throw new Exception('GET method required for list_documents action');
            }
            // Build options from query params
            $options = [
                'type' => $_GET['type'] ?? null,
                'status' => $_GET['status'] ?? null,
                'search' => $_GET['search'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null,
                'client_id' => isset($_GET['client_id']) && is_numeric($_GET['client_id']) ? (int)$_GET['client_id'] : null,
                'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1,
                'limit' => isset($_GET['limit']) ? (int)$_GET['limit'] : 20,
                'sort_by' => $_GET['sort_by'] ?? 'document_id',
                'sort_dir' => strtolower($_GET['sort_dir'] ?? 'desc'),
            ];
            $result = \App\modules\invoice\controllers\list_documents($options);
            echo json_encode($result);
            break;
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