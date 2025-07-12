<?php
// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';

use App\Core\Database\ClientDatabase;

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug session
error_log("API Session contents: " . print_r($_SESSION, true));

// Validate session variables
if (!isset($_SESSION['account_number'])) {
    error_log("No account_number in session");
    http_response_code(401);
    echo json_encode(['error' => 'No account number found in session']);
    exit;
}

// Get database connection
try {
    error_log("Attempting database connection for account: " . $_SESSION['account_number']);
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $conn = $db->connect();

    if (!$conn) {
        error_log("Database connection failed - connection object is null");
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    error_log("Database connection successful");
} catch (Exception $e) {
    error_log("Exception during database connection: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';
error_log("API Action: " . $action);

switch ($action) {
    case 'get':
        try {
            $id = $_GET['id'] ?? 0;
            error_log("Fetching customer with ID: " . $id);
            
            $query = "SELECT 
                c.customer_id,
                c.customer_initials,
                c.customer_title,
                c.first_name,
                c.last_name,
                c.dob,
                c.gender,
                c.loyalty_level,
                c.email,
                c.phone,
                c.status,
                a.address_line1,
                a.address_line2,
                a.suburb,
                a.province,
                a.city,
                a.postal_code,
                a.country
                FROM invoicing.customers c
                LEFT JOIN invoicing.customer_address ca ON c.customer_id = ca.customer_id
                LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                WHERE c.customer_id = :id AND c.deleted_at IS NULL";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer) {
                echo json_encode(['success' => true, 'data' => $customer]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Customer not found']);
            }
        } catch (PDOException $e) {
            error_log("Database error in get: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'get_all':
        try {
            error_log("Executing get_all query for customers");
            $query = "SELECT 
                c.customer_id,
                c.customer_initials,
                c.customer_title,
                c.first_name,
                c.last_name,
                c.dob,
                c.gender,
                c.loyalty_level,
                c.email,
                c.phone,
                c.status,
                (SELECT MAX(invoice_date) FROM invoicing.invoices WHERE customer_id = c.customer_id) as last_invoice_date,
                (SELECT COALESCE(SUM(total_amount), 0) FROM invoicing.invoices WHERE customer_id = c.customer_id AND status_id = 1) as outstanding_balance,
                (SELECT COUNT(*) FROM invoicing.invoices WHERE customer_id = c.customer_id) as total_invoices,
                a.address_line1,
                a.city,
                a.postal_code,
                a.country
                FROM invoicing.customers c
                LEFT JOIN invoicing.customer_address ca ON c.customer_id = ca.customer_id
                LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                WHERE c.deleted_at IS NULL
                ORDER BY c.first_name, c.last_name";
            
            error_log("Query prepared: " . $query);
            $stmt = $conn->prepare($query);
            error_log("Query prepared");
            
            if (!$stmt) {
                throw new PDOException("Failed to prepare statement: " . print_r($conn->errorInfo(), true));
            }
            
            $stmt->execute();
            error_log("Query executed");
            
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($customers) . " customers");
            error_log("First customer: " . print_r($customers[0] ?? 'No customers found', true));
            
            echo json_encode(['success' => true, 'data' => $customers]);
        } catch (PDOException $e) {
            error_log("Database error in get_all: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'add':
        try {
            $rawData = file_get_contents('php://input');
            error_log("Raw incoming data: " . $rawData);
            
            $data = json_decode($rawData, true);
            error_log("Decoded data: " . print_r($data, true));
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                throw new Exception("Invalid JSON data received");
            }

            $conn->beginTransaction();

            // Insert customer
            $stmt = $conn->prepare("
                INSERT INTO invoicing.customers (
                    customer_initials, customer_title, first_name, last_name,
                    dob, gender, loyalty_level, email, phone, tel
                ) VALUES (
                    :customer_initials, :customer_title, :first_name, :last_name,
                    :dob, :gender, :loyalty_level, :email, :phone, :tel
                ) RETURNING customer_id
            ");

            $stmt->execute([
                ':customer_initials' => $data['customerInitials'] ?? '',
                ':customer_title' => $data['customerTitle'] ?? '',
                ':first_name' => $data['customerName'] ?? '',
                ':last_name' => $data['customerSurname'] ?? '',
                ':dob' => $data['dob'] ?: null,
                ':gender' => $data['gender'] ?: null,
                ':loyalty_level' => $data['loyaltyLevel'] ?: null,
                ':email' => $data['customerEmail'] ?? '',
                ':phone' => $data['customerCell'] ?? '',
                ':tel' => $data['customerTel'] ?: null
            ]);

            $customerId = $stmt->fetchColumn();

            // Insert address
            $stmt = $conn->prepare("
                INSERT INTO invoicing.address (
                    address_line1, address_line2, suburb, province,
                    city, postal_code, country
                ) VALUES (
                    :address_line1, :address_line2, :suburb, :province,
                    :city, :postal_code, :country
                ) RETURNING address_id
            ");

            $stmt->execute([
                ':address_line1' => $data['custAddrLine1'] ?? '',
                ':address_line2' => $data['custAddrLine2'] ?: null,
                ':suburb' => $data['custSuburb'] ?: null,
                ':province' => $data['custProvince'] ?: null,
                ':city' => $data['custCity'] ?? '',
                ':postal_code' => $data['custPostalCode'] ?? '',
                ':country' => $data['custCountry'] ?? ''
            ]);

            $addressId = $stmt->fetchColumn();

            // Link address to customer
            $stmt = $conn->prepare("
                INSERT INTO invoicing.customer_address (customer_id, address_id)
                VALUES (:customer_id, :address_id)
            ");

            $stmt->execute([
                ':customer_id' => $customerId,
                ':address_id' => $addressId
            ]);

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Customer added successfully']);
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error in add customer: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Error in add customer: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            error_log("Update customer data: " . print_r($data, true));
            
            // Start transaction
            $conn->beginTransaction();
            
            // Update customer
            $query = "UPDATE invoicing.customers SET 
                customer_initials = :customer_initials,
                customer_title = :customer_title,
                first_name = :first_name,
                last_name = :last_name,
                dob = :dob,
                gender = :gender,
                loyalty_level = :loyalty_level,
                email = :email,
                phone = :phone,
                updated_at = CURRENT_TIMESTAMP
                WHERE customer_id = :id
                RETURNING customer_id";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':customer_initials' => $data['customerInitials'],
                ':customer_title' => $data['customerTitle'],
                ':first_name' => $data['customerName'],
                ':last_name' => $data['customerSurname'],
                ':dob' => $data['dob'],
                ':gender' => $data['gender'],
                ':loyalty_level' => $data['loyaltyLevel'],
                ':email' => $data['customerEmail'],
                ':phone' => $data['customerCell'],
                ':id' => $data['id']
            ]);
            
            $customerId = $stmt->fetch(PDO::FETCH_ASSOC)['customer_id'];
            
            // Check if customer has an existing address
            $query = "SELECT address_id FROM invoicing.customer_address WHERE customer_id = :customer_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([':customer_id' => $customerId]);
            $existingAddress = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingAddress) {
                // Update existing address
                $query = "UPDATE invoicing.address SET 
                    address_line1 = :address_line1,
                    address_line2 = :address_line2,
                    suburb = :suburb,
                    province = :province,
                    city = :city,
                    postal_code = :postal_code,
                    country = :country,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE address_id = :address_id";
                
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':address_line1' => $data['custAddrLine1'],
                    ':address_line2' => $data['custAddrLine2'] ?? null,
                    ':suburb' => $data['custSuburb'] ?? null,
                    ':province' => $data['custProvince'] ?? null,
                    ':city' => $data['custCity'],
                    ':postal_code' => $data['custPostalCode'],
                    ':country' => $data['custCountry'],
                    ':address_id' => $existingAddress['address_id']
                ]);
            } else {
                // Insert new address
                $query = "INSERT INTO invoicing.address (
                    address_type_id, address_line1, address_line2, suburb, province,
                    city, postal_code, country, is_primary
                    ) VALUES (
                    1, :address_line1, :address_line2, :suburb, :province,
                    :city, :postal_code, :country, true
                    ) RETURNING address_id";
                
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':address_line1' => $data['custAddrLine1'],
                    ':address_line2' => $data['custAddrLine2'] ?? null,
                    ':suburb' => $data['custSuburb'] ?? null,
                    ':province' => $data['custProvince'] ?? null,
                    ':city' => $data['custCity'],
                    ':postal_code' => $data['custPostalCode'],
                    ':country' => $data['custCountry']
                ]);
                
                $addressId = $stmt->fetch(PDO::FETCH_ASSOC)['address_id'];
                
                // Link address to customer
                $query = "INSERT INTO invoicing.customer_address (customer_id, address_id) VALUES (:customer_id, :address_id)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    ':customer_id' => $customerId,
                    ':address_id' => $addressId
                ]);
            }
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Database error in update: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_GET['id'] ?? 0;
            
            $query = "UPDATE invoicing.customers SET 
                deleted_at = CURRENT_TIMESTAMP 
                WHERE customer_id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Customer deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'search':
        try {
            $query = $_GET['query'] ?? '';
            if (strlen($query) < 2) throw new Exception('Query too short');
            $sql = "SELECT c.customer_id, c.first_name, c.last_name, c.email, c.phone, a.address_line1, a.address_line2
                    FROM invoicing.customers c
                    LEFT JOIN invoicing.customer_address ca ON c.customer_id = ca.customer_id
                    LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                    WHERE (c.first_name ILIKE :q OR c.last_name ILIKE :q OR c.email ILIKE :q OR c.phone ILIKE :q)
                      AND c.deleted_at IS NULL
                    LIMIT 20";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':q' => "%$query%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([]);
        }
        break;

    case 'search_all':
        try {
            $query = $_GET['query'] ?? '';
            if (strlen($query) < 2) throw new Exception('Query too short');
            // Search customers
            $sql_cust = "SELECT c.customer_id as customer_id, CONCAT(c.first_name, ' ', c.last_name) as customer_name, c.email, c.phone, a.address_line1, a.address_line2, 'customer' as type
                FROM invoicing.customers c
                LEFT JOIN invoicing.customer_address ca ON c.customer_id = ca.customer_id
                LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                WHERE (c.first_name ILIKE :q OR c.last_name ILIKE :q OR c.email ILIKE :q OR c.phone ILIKE :q)
                  AND c.deleted_at IS NULL
                LIMIT 10";
            $stmt_cust = $conn->prepare($sql_cust);
            $stmt_cust->execute([':q' => "%$query%"]);
            $customers = $stmt_cust->fetchAll(PDO::FETCH_ASSOC);
            // Search companies
            $sql_comp = "SELECT c.company_id as company_id, c.company_name as company_name, c.vat_number, c.registration_number, cp.email as email, cp.phone as phone, a.address_line1, a.address_line2, 'company' as type
                FROM invoicing.company c
                LEFT JOIN invoicing.company_address ca ON c.company_id = ca.company_id
                LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                LEFT JOIN invoicing.company_contact cc ON c.company_id = cc.company_id
                LEFT JOIN invoicing.contact_person cp ON cc.contact_id = cp.contact_id
                WHERE (c.company_name ILIKE :q OR c.registration_number ILIKE :q OR c.vat_number ILIKE :q OR cp.email ILIKE :q)
                  AND c.deleted_at IS NULL
                LIMIT 10";
            $stmt_comp = $conn->prepare($sql_comp);
            $stmt_comp->execute([':q' => "%$query%"]);
            $companies = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);
            // Combine and return
            $results = array_merge($customers, $companies);
            echo json_encode($results);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
} 