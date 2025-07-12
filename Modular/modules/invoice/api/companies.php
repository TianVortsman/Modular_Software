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
    case 'get_all':
        try {
            error_log("Executing get_all query for companies");
            $query = "SELECT 
                c.company_id,
                c.company_name,
                c.registration_number,
                c.vat_number,
                c.status,
                (SELECT MAX(invoice_date) FROM invoicing.invoices WHERE company_id = c.company_id) as last_invoice_date,
                (SELECT COALESCE(SUM(total_amount), 0) FROM invoicing.invoices WHERE company_id = c.company_id AND status_id = 1) as outstanding_balance,
                (SELECT COUNT(*) FROM invoicing.invoices WHERE company_id = c.company_id) as total_invoices,
                a.address_line1,
                a.city,
                a.postal_code,
                a.country,
                cp.email as contact_email,
                cp.phone as contact_phone
                FROM invoicing.company c
                LEFT JOIN invoicing.company_address ca ON c.company_id = ca.company_id
                LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                LEFT JOIN invoicing.company_contact cc ON c.company_id = cc.company_id
                LEFT JOIN invoicing.contact_person cp ON cc.contact_id = cp.contact_id
                WHERE c.deleted_at IS NULL
                ORDER BY c.company_name";
            
            error_log("Query prepared: " . $query);
            $stmt = $conn->prepare($query);
            error_log("Query prepared");
            
            if (!$stmt) {
                throw new PDOException("Failed to prepare statement: " . print_r($conn->errorInfo(), true));
            }
            
            $stmt->execute();
            error_log("Query executed");
            
            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($companies) . " companies");
            error_log("First company: " . print_r($companies[0] ?? 'No companies found', true));
            
            echo json_encode(['success' => true, 'data' => $companies]);
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

            // Insert company data
            $stmt = $conn->prepare("
                INSERT INTO invoicing.company (
                    company_name, registration_number, vat_number, industry, website
                ) VALUES (
                    :company_name, :registration_number, :vat_number, :industry, :website
                ) RETURNING company_id
            ");

            $stmt->execute([
                'company_name' => $data['companyName'] ?? '',
                'registration_number' => $data['registrationNumber'] ?: null,
                'vat_number' => $data['vatNumber'] ?: null,
                'industry' => $data['industry'] ?: null,
                'website' => $data['website'] ?: null
            ]);

            $companyId = $stmt->fetchColumn();

            // Insert address data
            $stmt = $conn->prepare("
                INSERT INTO invoicing.address (
                    address_line1, address_line2, suburb, province, city, postal_code, country
                ) VALUES (
                    :address_line1, :address_line2, :suburb, :province, :city, :postal_code, :country
                ) RETURNING address_id
            ");

            $stmt->execute([
                'address_line1' => $data['addressLine1'] ?? '',
                'address_line2' => $data['addressLine2'] ?: null,
                'suburb' => $data['suburb'] ?: null,
                'province' => $data['province'] ?: null,
                'city' => $data['city'] ?? '',
                'postal_code' => $data['postalCode'] ?? '',
                'country' => $data['country'] ?? ''
            ]);

            $addressId = $stmt->fetchColumn();

            // Link address to company
            $stmt = $conn->prepare("
                INSERT INTO invoicing.company_address (company_id, address_id)
                VALUES (:company_id, :address_id)
            ");

            $stmt->execute([
                'company_id' => $companyId,
                'address_id' => $addressId
            ]);

            // Insert contact person
            $stmt = $conn->prepare("
                INSERT INTO invoicing.contact_person (
                    first_name, last_name, position, email, phone
                ) VALUES (
                    :first_name, :last_name, :position, :email, :phone
                ) RETURNING contact_id
            ");

            $stmt->execute([
                'first_name' => $data['contactFirstName'] ?? '',
                'last_name' => $data['contactLastName'] ?? '',
                'position' => $data['contactPosition'] ?: null,
                'email' => $data['contactEmail'] ?? '',
                'phone' => $data['contactPhone'] ?? ''
            ]);

            $contactId = $stmt->fetchColumn();

            // Link contact to company
            $stmt = $conn->prepare("
                INSERT INTO invoicing.company_contact (company_id, contact_id)
                VALUES (:company_id, :contact_id)
            ");

            $stmt->execute([
                'company_id' => $companyId,
                'contact_id' => $contactId
            ]);

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Company added successfully']);
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error in add company: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Error in add company: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Start transaction
            $conn->beginTransaction();
            
            // Update company
            $query = "UPDATE invoicing.company SET 
                company_name = :company_name,
                registration_number = :registration_number,
                vat_number = :vat_number,
                updated_at = CURRENT_TIMESTAMP
                WHERE company_id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':company_name' => $data['company_name'],
                ':registration_number' => $data['registration_number'],
                ':vat_number' => $data['vat_number'],
                ':id' => $data['id']
            ]);
            
            // Update address
            $query = "UPDATE invoicing.address a
                SET address_line1 = :address_line1,
                    city = :city,
                    postal_code = :postal_code,
                    country = :country
                FROM invoicing.company_address ca
                WHERE ca.company_id = :company_id
                AND ca.address_id = a.address_id";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ':address_line1' => $data['address_line1'],
                ':city' => $data['city'],
                ':postal_code' => $data['postal_code'],
                ':country' => $data['country'],
                ':company_id' => $data['id']
            ]);
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Company updated successfully']);
        } catch (PDOException $e) {
            $conn->rollBack();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_GET['id'] ?? 0;
            
            $query = "UPDATE invoicing.company SET 
                deleted_at = CURRENT_TIMESTAMP 
                WHERE company_id = :id";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Company deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'get':
        try {
            $id = $_GET['id'] ?? 0;
            
            $query = "SELECT 
                c.company_id,
                c.company_name,
                c.registration_number,
                c.vat_number,
                c.industry,
                c.website,
                a.address_line1,
                a.address_line2,
                a.suburb,
                a.province,
                a.city,
                a.postal_code,
                a.country,
                cp.first_name as contact_first_name,
                cp.last_name as contact_last_name,
                cp.position as contact_position,
                cp.email as contact_email,
                cp.phone as contact_phone
                FROM invoicing.company c
                LEFT JOIN invoicing.company_address ca ON c.company_id = ca.company_id
                LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                LEFT JOIN invoicing.company_contact cc ON c.company_id = cc.company_id
                LEFT JOIN invoicing.contact_person cp ON cc.contact_id = cp.contact_id
                WHERE c.company_id = :id AND c.deleted_at IS NULL";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            $company = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($company) {
                echo json_encode(['success' => true, 'data' => $company]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Company not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'search':
        try {
            $query = $_GET['query'] ?? '';
            if (strlen($query) < 2) throw new Exception('Query too short');
            $sql = "SELECT c.company_id, c.company_name, c.registration_number, c.vat_number, a.address_line1, a.address_line2, cp.email as contact_email, cp.phone as contact_phone
                    FROM invoicing.company c
                    LEFT JOIN invoicing.company_address ca ON c.company_id = ca.company_id
                    LEFT JOIN invoicing.address a ON ca.address_id = a.address_id
                    LEFT JOIN invoicing.company_contact cc ON c.company_id = cc.company_id
                    LEFT JOIN invoicing.contact_person cp ON cc.contact_id = cp.contact_id
                    WHERE (c.company_name ILIKE :q OR c.registration_number ILIKE :q OR c.vat_number ILIKE :q OR cp.email ILIKE :q)
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

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
} 