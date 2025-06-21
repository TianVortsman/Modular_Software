<?php
// Database connection parameters
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'ACC002';
$user = getenv('DB_USER') ?: 'Tian';
$password = getenv('DB_PASSWORD') ?: 'Modul@rdev@2024';

try {
    // Connect to database
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!\n";

    // Start transaction
    $pdo->beginTransaction();

    // Test Address Type
    echo "\nTesting Address Type...\n";
    $stmt = $pdo->query("SELECT * FROM invoicing.address_type");
    $addressTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($addressTypes) . " address types\n";

    // Test Contact Type
    echo "\nTesting Contact Type...\n";
    $stmt = $pdo->query("SELECT * FROM invoicing.contact_type");
    $contactTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($contactTypes) . " contact types\n";

    // Test Company Creation
    echo "\nTesting Company Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.company (name, registration_number, vat_number, industry, website, status)
        VALUES (?, ?, ?, ?, ?, ?)
        RETURNING company_id
    ");
    $stmt->execute(['Test Company Ltd', 'REG123456', 'VAT123456', 'Technology', 'www.testcompany.com', 'active']);
    $companyId = $stmt->fetchColumn();
    echo "Created company with ID: $companyId\n";

    // Test Address Creation
    echo "\nTesting Address Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.address (address_type_id, address_line1, city, postal_code, country, is_primary)
        VALUES (?, ?, ?, ?, ?, ?)
        RETURNING address_id
    ");
    $stmt->execute([1, '123 Test Street', 'Test City', '1234', 'Test Country', true]);
    $addressId = $stmt->fetchColumn();
    echo "Created address with ID: $addressId\n";

    // Link Address to Company
    echo "\nLinking Address to Company...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.company_address (company_id, address_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$companyId, $addressId]);
    echo "Linked address to company\n";

    // Test Contact Person Creation
    echo "\nTesting Contact Person Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.contact_person (contact_type_id, first_name, last_name, position, email, phone, is_primary)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        RETURNING contact_id
    ");
    $stmt->execute([1, 'John', 'Doe', 'Manager', 'john@testcompany.com', '1234567890', true]);
    $contactId = $stmt->fetchColumn();
    echo "Created contact person with ID: $contactId\n";

    // Link Contact to Company
    echo "\nLinking Contact to Company...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.company_contact (company_id, contact_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$companyId, $contactId]);
    echo "Linked contact to company\n";

    // Test Customer Creation
    echo "\nTesting Customer Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.customers (first_name, last_name, email, phone, status)
        VALUES (?, ?, ?, ?, ?)
        RETURNING customer_id
    ");
    $stmt->execute(['Jane', 'Smith', 'jane@example.com', '0987654321', 'active']);
    $customerId = $stmt->fetchColumn();
    echo "Created customer with ID: $customerId\n";

    // Link Address to Customer
    echo "\nLinking Address to Customer...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.customer_address (customer_id, address_id)
        VALUES (?, ?)
    ");
    $stmt->execute([$customerId, $addressId]);
    echo "Linked address to customer\n";

    // Test Quote Creation
    echo "\nTesting Quote Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.quote (customer_id, company_id, quote_date, expiration_date, total_amount, status_id, notes)
        VALUES (?, ?, CURRENT_DATE, CURRENT_DATE + INTERVAL '30 days', ?, ?, ?)
        RETURNING quote_id
    ");
    $stmt->execute([$customerId, $companyId, 1000.00, 1, 'Test quote']);
    $quoteId = $stmt->fetchColumn();
    echo "Created quote with ID: $quoteId\n";

    // Test Invoice Creation
    echo "\nTesting Invoice Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.invoice (customer_id, company_id, emp_id, inv_date, due_date, status_id, total_amount)
        VALUES (?, ?, ?, CURRENT_DATE, CURRENT_DATE + INTERVAL '30 days', ?, ?)
        RETURNING inv_id
    ");
    $stmt->execute([$customerId, $companyId, 1, 1, 1000.00]);
    $invoiceId = $stmt->fetchColumn();
    echo "Created invoice with ID: $invoiceId\n";

    // Test Invoice Template Creation
    echo "\nTesting Invoice Template Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.invoice_templates (template_name, description, template_content, is_default, created_by)
        VALUES (?, ?, ?, ?, ?)
        RETURNING template_id
    ");
    $stmt->execute(['Standard Template', 'Default invoice template', '{"template": "content"}', true, 1]);
    $templateId = $stmt->fetchColumn();
    echo "Created invoice template with ID: $templateId\n";

    // Test Recurring Invoice Creation
    echo "\nTesting Recurring Invoice Creation...\n";
    $stmt = $pdo->prepare("
        INSERT INTO invoicing.recurring_invoices (customer_id, frequency, start_date, template_id, status)
        VALUES (?, ?, CURRENT_DATE, ?, ?)
        RETURNING recurring_id
    ");
    $stmt->execute([$customerId, 'monthly', $templateId, 'active']);
    $recurringId = $stmt->fetchColumn();
    echo "Created recurring invoice with ID: $recurringId\n";

    // Verify Data
    echo "\nVerifying Data...\n";
    
    // Verify Company
    $stmt = $pdo->prepare("SELECT * FROM invoicing.company WHERE company_id = ?");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Company: " . $company['name'] . "\n";

    // Verify Customer
    $stmt = $pdo->prepare("SELECT * FROM invoicing.customers WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Customer: " . $customer['first_name'] . " " . $customer['last_name'] . "\n";

    // Verify Quote
    $stmt = $pdo->prepare("SELECT * FROM invoicing.quote WHERE quote_id = ?");
    $stmt->execute([$quoteId]);
    $quote = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Quote Amount: " . $quote['total_amount'] . "\n";

    // Verify Invoice
    $stmt = $pdo->prepare("SELECT * FROM invoicing.invoice WHERE inv_id = ?");
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Invoice Amount: " . $invoice['total_amount'] . "\n";

    // Commit transaction
    $pdo->commit();
    echo "\nAll tests completed successfully!\n";

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}