<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';

use App\Core\Database\ClientDatabase;

// Set CORS headers
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if (!isset($_SESSION['account_number'])) {
        error_log('account_number');
        throw new Exception('User session not found');
    }
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();
    if (!$conn) {
        error_log('[INVOICE SETUP ERROR] Database connection failed');
        throw new Exception('Database connection failed');
    }
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    // Table existence check helper (PostgreSQL)
    function tableExists($conn, $table) {
        try {
            $stmt = $conn->prepare("SELECT to_regclass(:tbl) as exists");
            $stmt->execute(['tbl' => $table]);
            $row = $stmt->fetch();
            return !empty($row['exists']);
        } catch (Exception $e) {
            error_log('[INVOICE SETUP ERROR] Table check failed: ' . $e->getMessage());
            return false;
        }
    }

    // Handle the request based on action and method
    switch ($action) {
        // Bank Information
        case 'get_bank_info':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_bank_info action');
            }
            $result = getBankInfo($conn);
            break;

        case 'save_bank_info':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_bank_info action');
            }
            $result = saveBankInfo($conn);
            break;

        // Company Information
        case 'get_company_info':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_company_info action');
            }
            $result = getCompanyInfo($conn);
            break;

        case 'save_company_info':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_company_info action');
            }
            $result = saveCompanyInfo($conn);
            break;

        // Sales Targets
        case 'get_sales_targets':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_sales_targets action');
            }
            $result = getSalesTargets($conn);
            break;

        case 'save_sales_target':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_sales_target action');
            }
            $result = saveSalesTarget($conn);
            break;

        case 'delete_sales_target':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete_sales_target action');
            }
            $result = deleteSalesTarget($conn);
            break;

        // Suppliers
        case 'get_suppliers':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_suppliers action');
            }
            $result = getSuppliers($conn);
            break;

        case 'save_supplier':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_supplier action');
            }
            $result = saveSupplier($conn);
            break;

        case 'delete_supplier':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete_supplier action');
            }
            $result = deleteSupplier($conn);
            break;

        // Credit Policy
        case 'get_credit_policy':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_credit_policy action');
            }
            $result = getCreditPolicy($conn);
            break;

        case 'save_credit_policy':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_credit_policy action');
            }
            $result = saveCreditPolicy($conn);
            break;

        // Credit Reasons
        case 'get_credit_reasons':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_credit_reasons action');
            }
            $result = getCreditReasons($conn);
            break;

        case 'save_credit_reason':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_credit_reason action');
            }
            $result = saveCreditReason($conn);
            break;

        case 'delete_credit_reason':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete_credit_reason action');
            }
            $result = deleteCreditReason($conn);
            break;

        // Invoice Numbering
        case 'get_numbering_settings':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_numbering_settings action');
            }
            if (!tableExists($conn, 'settings.invoice_settings')) {
                throw new Exception('settings.invoice_settings table does not exist');
            }
            $result = getNumberingSettings($conn);
            break;

        case 'save_numbering_settings':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_numbering_settings action');
            }
            if (!tableExists($conn, 'settings.invoice_settings')) {
                throw new Exception('settings.invoice_settings table does not exist');
            }
            $result = saveNumberingSettings($conn);
            break;

        // Terms & Footer
        case 'get_terms_settings':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_terms_settings action');
            }
            $result = getTermsSettings($conn);
            break;

        case 'save_terms_settings':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_terms_settings action');
            }
            $result = saveTermsSettings($conn);
            break;

        // Categories
        case 'get_categories':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_categories action');
            }
            if (!tableExists($conn, 'core.product_categories')) {
                throw new Exception('core.product_categories table does not exist');
            }
            $result = getCategories($conn);
            break;

        case 'save_category':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_category action');
            }
            $result = saveCategory($conn);
            break;

        case 'delete_category':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete_category action');
            }
            $result = deleteCategory($conn);
            break;

        // Subcategories
        case 'list_subcategories':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_subcategories action');
            }
            $result = getSubcategories($conn);
            break;

        case 'save_subcategory':
            if ($method !== 'POST') {
                throw new Exception('POST method required for save_subcategory action');
            }
            $result = saveSubcategory($conn);
            break;

        case 'delete_subcategory':
            if ($method !== 'DELETE') {
                throw new Exception('DELETE method required for delete_subcategory action');
            }
            $result = deleteSubcategory($conn);
            break;

        // Product Types
        case 'get_product_types':
            if ($method !== 'GET') {
                throw new Exception('GET method required for get_product_types action');
            }
            $result = getProductTypes($conn);
            break;

        // Salesperson search (autocomplete)
        case 'search_salesperson':
            if ($method !== 'GET') {
                throw new Exception('GET method required for search_salesperson action');
            }
            $query = $_GET['query'] ?? '';
            $result = searchSalesperson($conn, $query);
            break;

        default:
            throw new Exception("Invalid action: $action");
    }

    echo json_encode($result);

} catch (PDOException $e) {
    error_log('[INVOICE SETUP DB ERROR] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => null
    ]);
} catch (Exception $e) {
    error_log('[INVOICE SETUP ERROR] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}

// ==================== BANK INFORMATION FUNCTIONS ====================

function getBankInfo($conn) {
    $stmt = $conn->prepare("SELECT bank_name, bank_branch, account_number, swift_code FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'success' => true,
        'message' => 'Bank information retrieved successfully',
        'data' => $result ?: []
    ];
}

function saveBankInfo($conn) {
    $bank_name = $_POST['bank_name'] ?? '';
    $bank_branch = $_POST['bank_branch'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $swift_code = $_POST['swift_code'] ?? '';
    $stmt = $conn->prepare("UPDATE settings.invoice_settings SET bank_name = ?, bank_branch = ?, account_number = ?, swift_code = ?, updated_at = NOW() WHERE id = 1");
    $stmt->execute([$bank_name, $bank_branch, $account_number, $swift_code]);
    return [
        'success' => true,
        'message' => 'Bank information saved successfully',
        'data' => null
    ];
}

// ==================== COMPANY INFORMATION FUNCTIONS ====================

function getCompanyInfo($conn) {
    $stmt = $conn->prepare("SELECT * FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // Ensure all fields are present in the response, even if null
    $data = [
        'company_name' => $result['company_name'] ?? '',
        'company_address' => $result['company_address'] ?? '',
        'company_phone' => $result['company_phone'] ?? '',
        'company_email' => $result['company_email'] ?? '',
        'vat_number' => $result['vat_number'] ?? '',
        'registration_number' => $result['registration_number'] ?? ''
    ];
    return [
        'success' => true,
        'message' => 'Company information retrieved successfully',
        'data' => $data
    ];
}

function saveCompanyInfo($conn) {
    $company_name = $_POST['company_name'] ?? '';
    $company_address = $_POST['company_address'] ?? '';
    $company_phone = $_POST['company_phone'] ?? '';
    $company_email = $_POST['company_email'] ?? '';
    $vat_number = $_POST['vat_number'] ?? '';
    $registration_number = $_POST['registration_number'] ?? '';

    // Check if record exists
    $stmt = $conn->prepare("SELECT id FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE settings.invoice_settings 
            SET company_name = ?, company_address = ?, company_phone = ?, company_email = ?, 
                vat_number = ?, registration_number = ?, updated_at = NOW()
            WHERE id = 1
        ");
        $stmt->execute([$company_name, $company_address, $company_phone, $company_email, $vat_number, $registration_number]);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO settings.invoice_settings (company_name, company_address, company_phone, company_email, 
                                                vat_number, registration_number, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$company_name, $company_address, $company_phone, $company_email, $vat_number, $registration_number]);
    }

    return [
        'success' => true,
        'message' => 'Company information saved successfully',
        'data' => null
    ];
}

// ==================== SALES TARGETS FUNCTIONS ====================

function getSalesTargets($conn) {
    $stmt = $conn->prepare("
        SELECT st.*, u.name as user_name 
        FROM invoice_sales_targets st 
        LEFT JOIN users u ON st.user_id = u.id 
        ORDER BY st.created_at DESC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Sales targets retrieved successfully',
        'data' => $results
    ];
}

function saveSalesTarget($conn) {
    $user_id = $_POST['user_id'] ?? null;
    $target_amount = $_POST['target_amount'] ?? 0;
    $period = $_POST['period'] ?? 'monthly';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO invoice_sales_targets (user_id, target_amount, period, start_date, end_date, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$user_id, $target_amount, $period, $start_date, $end_date]);

    return [
        'success' => true,
        'message' => 'Sales target saved successfully',
        'data' => null
    ];
}

function deleteSalesTarget($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Sales target ID is required');
    }

    $stmt = $conn->prepare("DELETE FROM invoice_sales_targets WHERE id = ?");
    $stmt->execute([$id]);

    return [
        'success' => true,
        'message' => 'Sales target deleted successfully',
        'data' => null
    ];
}

// ==================== SUPPLIERS FUNCTIONS ====================

function getSuppliers($conn) {
    $stmt = $conn->prepare("SELECT * FROM invoice_suppliers ORDER BY name ASC");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Suppliers retrieved successfully',
        'data' => $results
    ];
}

function saveSupplier($conn) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO invoice_suppliers (name, email, phone, address, contact_person, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$name, $email, $phone, $address, $contact_person]);

    return [
        'success' => true,
        'message' => 'Supplier saved successfully',
        'data' => null
    ];
}

function deleteSupplier($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Supplier ID is required');
    }

    $stmt = $conn->prepare("DELETE FROM invoice_suppliers WHERE id = ?");
    $stmt->execute([$id]);

    return [
        'success' => true,
        'message' => 'Supplier deleted successfully',
        'data' => null
    ];
}

// ==================== CREDIT POLICY FUNCTIONS ====================

function getCreditPolicy($conn) {
    $stmt = $conn->prepare("SELECT * FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Credit policy retrieved successfully',
        'data' => $result ?: []
    ];
}

function saveCreditPolicy($conn) {
    $allow_credit_notes = isset($_POST['allow_credit_notes']) ? 1 : 0;
    $require_approval = isset($_POST['require_approval']) ? 1 : 0;

    // Check if record exists
    $stmt = $conn->prepare("SELECT id FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE settings.invoice_settings 
            SET allow_credit_notes = ?, require_approval = ?, updated_at = NOW()
            WHERE id = 1
        ");
        $stmt->execute([$allow_credit_notes, $require_approval]);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO settings.invoice_settings (allow_credit_notes, require_approval, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
        ");
        $stmt->execute([$allow_credit_notes, $require_approval]);
    }

    return [
        'success' => true,
        'message' => 'Credit policy saved successfully',
        'data' => null
    ];
}

// ==================== CREDIT REASONS FUNCTIONS ====================

function getCreditReasons($conn) {
    $stmt = $conn->prepare("SELECT * FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Credit reasons retrieved successfully',
        'data' => $result ?: []
    ];
}

function saveCreditReason($conn) {
    $reason = $_POST['reason'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO settings.invoice_settings (reason, created_at, updated_at)
        VALUES (?, NOW(), NOW())
    ");
    $stmt->execute([$reason]);

    return [
        'success' => true,
        'message' => 'Credit reason saved successfully',
        'data' => null
    ];
}

function deleteCreditReason($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Credit reason ID is required');
    }

    $stmt = $conn->prepare("DELETE FROM settings.invoice_settings WHERE id = ?");
    $stmt->execute([$id]);

    return [
        'success' => true,
        'message' => 'Credit reason deleted successfully',
        'data' => null
    ];
}

// ==================== INVOICE NUMBERING FUNCTIONS ====================

function getNumberingSettings($conn) {
    $stmt = $conn->prepare("SELECT * FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Numbering settings retrieved successfully',
        'data' => $result ?: []
    ];
}

function saveNumberingSettings($conn) {
    $starting_number = isset($_POST['starting_number']) ? (int)$_POST['starting_number'] : 1;
    $current_number = isset($_POST['current_number']) ? (int)$_POST['current_number'] : null;
    $invoice_prefix = $_POST['invoice_prefix'] ?? '';
    $date_format = $_POST['date_format'] ?? 'Y-m-d';

    // Check if record exists
    $stmt = $conn->prepare("SELECT id FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE settings.invoice_settings 
            SET invoice_prefix = ?, starting_number = ?, current_number = ?, date_format = ?, updated_at = NOW()
            WHERE id = 1
        ");
        $stmt->execute([$invoice_prefix, $starting_number, $current_number, $date_format]);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO settings.invoice_settings (id, invoice_prefix, starting_number, current_number, date_format, created_at, updated_at)
            VALUES (1, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$invoice_prefix, $starting_number, $current_number, $date_format]);
    }

    return [
        'success' => true,
        'message' => 'Numbering settings saved successfully',
        'data' => null
    ];
}

// ==================== TERMS & FOOTER FUNCTIONS ====================

function getTermsSettings($conn) {
    $stmt = $conn->prepare("SELECT * FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Terms settings retrieved successfully',
        'data' => $result ?: []
    ];
}

function saveTermsSettings($conn) {
    $default_payment_terms = $_POST['default_payment_terms'] ?? '';
    $default_due_days = $_POST['default_due_days'] ?? 30;
    $invoice_footer = $_POST['invoice_footer'] ?? '';

    // Check if record exists
    $stmt = $conn->prepare("SELECT id FROM settings.invoice_settings WHERE id = 1");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE settings.invoice_settings 
            SET default_payment_terms = ?, default_due_days = ?, invoice_footer = ?, updated_at = NOW()
            WHERE id = 1
        ");
        $stmt->execute([$default_payment_terms, $default_due_days, $invoice_footer]);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO settings.invoice_settings (default_payment_terms, default_due_days, invoice_footer, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$default_payment_terms, $default_due_days, $invoice_footer]);
    }

    return [
        'success' => true,
        'message' => 'Terms settings saved successfully',
        'data' => null
    ];
}

// ==================== PRODUCT MANAGEMENT FUNCTIONS ====================

function getCategories($conn) {
    $stmt = $conn->prepare("
        SELECT pc.*, pt.product_type_name 
        FROM core.product_categories pc 
        LEFT JOIN core.product_types pt ON pc.product_type_id = pt.product_type_id 
        ORDER BY pc.category_name ASC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Categories retrieved successfully',
        'data' => $results
    ];
}

function saveCategory($conn) {
    $category_name = $_POST['category_name'] ?? '';
    $product_type_id = $_POST['categories_type_id'] ?? null;
    $category_description = $_POST['category_description'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO core.product_categories (category_name, product_type_id, category_description, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$category_name, $product_type_id, $category_description]);

    return [
        'success' => true,
        'message' => 'Category saved successfully',
        'data' => null
    ];
}

function deleteCategory($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Category ID is required');
    }

    // Check if category has subcategories
    $stmt = $conn->prepare("SELECT COUNT(*) FROM core.product_subcategories WHERE category_id = ?");
    $stmt->execute([$id]);
    $subcategoryCount = $stmt->fetchColumn();

    if ($subcategoryCount > 0) {
        throw new Exception('Cannot delete category that has subcategories. Please delete subcategories first.');
    }

    // Check if category has products
    $stmt = $conn->prepare("SELECT COUNT(*) FROM core.product WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        throw new Exception('Cannot delete category that has products. Please reassign or delete products first.');
    }

    $stmt = $conn->prepare("DELETE FROM core.product_categories WHERE category_id = ?");
    $stmt->execute([$id]);

    return [
        'success' => true,
        'message' => 'Category deleted successfully',
        'data' => null
    ];
}

function getSubcategories($conn) {
    $stmt = $conn->prepare("
        SELECT ps.*, pc.category_name AS category_name 
        FROM core.product_subcategories ps 
        LEFT JOIN core.product_categories pc ON ps.category_id = pc.category_id 
        ORDER BY ps.subcategory_name ASC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log('DEBUG getSubcategories: ' . print_r($results, true));
    return [
        'success' => true,
        'message' => 'Subcategories retrieved successfully',
        'data' => $results
    ];
}

function saveSubcategory($conn) {
    $subcategory_name = $_POST['subcategory_name'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $subcategory_description = $_POST['subcategory_description'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO core.product_subcategories (subcategory_name, category_id, subcategory_description, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$subcategory_name, $category_id, $subcategory_description]);

    return [
        'success' => true,
        'message' => 'Subcategory saved successfully',
        'data' => null
    ];
}

function deleteSubcategory($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Subcategory ID is required');
    }

    // Check if subcategory has products
    $stmt = $conn->prepare("SELECT COUNT(*) FROM core.product WHERE subcategory_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        throw new Exception('Cannot delete subcategory that has products. Please reassign or delete products first.');
    }

    $stmt = $conn->prepare("DELETE FROM core.product_subcategories WHERE subcategory_id = ?");
    $stmt->execute([$id]);

    return [
        'success' => true,
        'message' => 'Subcategory deleted successfully',
        'data' => null
    ];
}

// ==================== PRODUCT TYPES FUNCTIONS ====================

function getProductTypes($conn) {
    $stmt = $conn->prepare("SELECT * FROM core.product_types ORDER BY product_type_name ASC");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'success' => true,
        'message' => 'Product types retrieved successfully',
        'data' => $results
    ];
}

// ==================== SALES PERSON SEARCH FUNCTIONS ====================

function searchSalesperson($conn, $query) {
    $query = '%' . strtolower($query) . '%';
    $stmt = $conn->prepare("SELECT core.employees.employee_id, core.employees.first_name, core.employees.last_name, core.employees.employee_number, core.employee_contact.email FROM core.employees LEFT JOIN core.employee_contact ON core.employees.employee_id = core.employee_contact.employee_id WHERE is_sales = true AND LOWER(core.employees.first_name || ' ' || core.employees.last_name) LIKE :q LIMIT 10");
    $stmt->execute(['q' => $query]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return [
        'success' => true,
        'results' => $results
    ];
}
?> 