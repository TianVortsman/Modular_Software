<?php
session_start();
require '../../../php/db.php'; // Ensure this file initializes $conn
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Ensure PDO throws exceptions (if not already set in db.php)
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize global messages arrays
$errors = [];
$successMessages = [];

// Verify database connection
try {
    $conn->query('SELECT 1');
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if an Excel file was uploaded
if (isset($_FILES['excel_file']) && is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    try {
        $spreadsheet = IOFactory::load($file);
        
        // Import functions will be called later (see next sections)
        // Example:
        $errors = array_merge($errors, importProducts($spreadsheet, $conn));
        $errors = array_merge($errors, importCompanies($spreadsheet, $conn));
        $errors = array_merge($errors, importCustomers($spreadsheet, $conn));
        $errors = array_merge($errors, importVehicles($spreadsheet, $conn));
    } catch (Exception $e) {
        $errors[] = "Error loading Excel file: " . $e->getMessage();
    }
} else {
    $errors[] = "No Excel file uploaded.";
}

// Helper function to handle duplicate key errors
function handleDuplicateError($conn) {
    $errorMessage = $conn->errorInfo()[2] ?? '';
    if (strpos($errorMessage, 'duplicate key value violates unique constraint') !== false) {
        return "Row skipped due to duplicate.";
    }
    return null;
}

// Function to import vehicles and related data
function importVehicles($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Vehicles'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Vehicles' not found."];
    }

    // Get the last non-empty row
    $highestRow = $sheet->getHighestDataRow();
    if ($highestRow <= 1) {
        return ["Skipping 'Vehicles' sheet (no data)."];
    }

    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $messages = []; // Consolidated messages (errors & successes)

    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip header row
        }

        // --- Vehicle Table (Columns A-G) ---
        $make         = trim($row['A'] ?? '');
        $model        = trim($row['B'] ?? '');
        $year         = intval($row['C'] ?? 0);
        $vin          = trim($row['D'] ?? '');
        $regis_number = trim($row['E'] ?? '');
        $mileage      = floatval($row['F'] ?? 0);
        $status       = trim($row['G'] ?? '');

        $vehicleQuery = "INSERT INTO vehicle 
            (make, model, year, vin, regis_number, mileage, status, created_at, updated_at, deleted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL)
            RETURNING veh_id";
        $vehicleParams = [$make, $model, $year, $vin, $regis_number, $mileage, $status];

        $vehStmt = $conn->prepare($vehicleQuery);
        if (!$vehStmt->execute($vehicleParams)) {
            $messages[] = "Error inserting vehicle ($make $model): " . $vehStmt->errorInfo()[2];
            continue;
        }
        $veh_id = $conn->lastInsertId();
        $messages[] = "Vehicle ($make $model) imported successfully.";

        // --- Vehicle Insurance (Columns H-M) ---
        $insurance_provider = trim($row['H'] ?? '');
        $policy_number      = trim($row['I'] ?? '');
        $coverage_type      = trim($row['J'] ?? '');
        $ins_start_date     = trim($row['K'] ?? '');
        $ins_end_date       = trim($row['L'] ?? '');
        $insurance_amount   = floatval($row['M'] ?? 0);

        $insQuery = "INSERT INTO vehicle_insurance 
            (veh_id, insurance_provider, policy_number, coverage_type, start_date, end_date, amount, created_at, updated_at, deleted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL)";
        $insParams = [$veh_id, $insurance_provider, $policy_number, $coverage_type, $ins_start_date, $ins_end_date, $insurance_amount];

        $insStmt = $conn->prepare($insQuery);
        if (!$insStmt->execute($insParams)) {
            $messages[] = "Error inserting insurance for vehicle ID $veh_id: " . $insStmt->errorInfo()[2];
        } else {
            $messages[] = "Insurance for vehicle ID $veh_id imported successfully.";
        }

        // --- Vehicle Maintenance (Columns N-Q) ---
        $maintenance_date      = trim($row['N'] ?? '');
        $maint_descr           = trim($row['O'] ?? '');
        $maintenance_cost      = floatval($row['P'] ?? 0);
        $next_maintenance_date = trim($row['Q'] ?? '');

        $maintQuery = "INSERT INTO vehicle_maintenance 
            (veh_id, maintenance_date, descr, cost, next_maintenance_date, created_at, updated_at, deleted_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NULL)";
        $maintParams = [$veh_id, $maintenance_date, $maint_descr, $maintenance_cost, $next_maintenance_date];

        $maintStmt = $conn->prepare($maintQuery);
        if (!$maintStmt->execute($maintParams)) {
            $messages[] = "Error inserting maintenance for vehicle ID $veh_id: " . $maintStmt->errorInfo()[2];
        } else {
            $messages[] = "Maintenance for vehicle ID $veh_id imported successfully.";
        }

        // --- Vehicle Registration (Columns R-U) ---
        $regis_no     = trim($row['R'] ?? '');
        $regis_date   = trim($row['S'] ?? '');
        $reg_exp_date = trim($row['T'] ?? '');
        $issued_by    = trim($row['U'] ?? '');

        $regQuery = "INSERT INTO vehicle_registration 
            (veh_id, regis_no, regis_date, exp_date, issued_by, created_at, updated_at, deleted_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW(), NULL)";
        $regParams = [$veh_id, $regis_no, $regis_date, $reg_exp_date, $issued_by];

        $regStmt = $conn->prepare($regQuery);
        if (!$regStmt->execute($regParams)) {
            $messages[] = "Error inserting registration for vehicle ID $veh_id: " . $regStmt->errorInfo()[2];
        } else {
            $messages[] = "Registration for vehicle ID $veh_id imported successfully.";
        }

        // --- Vehicle Service Provider (Columns V-Z) ---
        $provider_name    = trim($row['V'] ?? '');
        $provider_contact = trim($row['W'] ?? '');
        $provider_address = trim($row['X'] ?? '');
        $service_type     = trim($row['Y'] ?? '');
        $provider_email   = trim($row['Z'] ?? '');

        $provQuery = "INSERT INTO vehicle_service_provider 
            (veh_id, name, contact_number, address, service_type, email, created_at, updated_at, deleted_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NULL)";
        $provParams = [$veh_id, $provider_name, $provider_contact, $provider_address, $service_type, $provider_email];

        $provStmt = $conn->prepare($provQuery);
        if (!$provStmt->execute($provParams)) {
            $messages[] = "Error inserting service provider for vehicle ID $veh_id: " . $provStmt->errorInfo()[2];
        } else {
            $messages[] = "Service provider for vehicle ID $veh_id imported successfully.";
        }
    }
    return $messages;
}


// Function to import products along with supplier and link them via the product_supplier table
function importProducts($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Products'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Products' not found."];
    }

    // Get the last non-empty row
    $highestRow = $sheet->getHighestDataRow();
    if ($highestRow <= 1) {
        return ["Skipping 'Products' sheet (no data)."];
    }

    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $messages = [];

    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip header row
        }

        // Extract product data (Columns A-N)
        $prod_name       = trim($row['A'] ?? '');
        $prod_descr      = trim($row['B'] ?? '');
        $prod_price      = floatval($row['C'] ?? 0);
        $sku             = trim($row['D'] ?? '');
        $barcode         = trim($row['E'] ?? '');
        $product_type    = trim($row['F'] ?? '');
        $brand           = trim($row['G'] ?? '');
        $manufacturer    = trim($row['H'] ?? '');
        $weight          = floatval($row['I'] ?? 0);
        $dimensions      = trim($row['J'] ?? '');
        $warranty_period = trim($row['K'] ?? '');
        $tax_rate        = floatval($row['L'] ?? 0);
        $discount        = floatval($row['M'] ?? 0);
        $status          = trim($row['N'] ?? '');

        // Extract supplier data (Columns O-Q)
        $suppl_name    = trim($row['O'] ?? '');
        $suppl_address = trim($row['P'] ?? '');
        $suppl_contact = trim($row['Q'] ?? '');

        // Skip row if required fields are missing
        if (empty($prod_name) || empty($suppl_name)) {
            continue;
        }

        try {
            // Insert product and retrieve its ID
            $productQuery = "INSERT INTO product 
                (prod_name, prod_descr, prod_price, sku, barcode, product_type, brand, manufacturer, weight, dimensions, warranty_period, tax_rate, discount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING prod_id";
            $productParams = [$prod_name, $prod_descr, $prod_price, $sku, $barcode, $product_type, $brand, $manufacturer, $weight, $dimensions, $warranty_period, $tax_rate, $discount, $status];
            $prodStmt = $conn->prepare($productQuery);
            $prodStmt->execute($productParams);
            $prod_id = $conn->lastInsertId();
            $messages[] = "Product '$prod_name' imported successfully.";
        } catch (PDOException $e) {
            $messages[] = "Error inserting product '$prod_name': " . $e->getMessage();
            continue;
        }

        try {
            // Insert supplier and retrieve its ID
            $supplierQuery = "INSERT INTO supplier (suppl_name, suppl_address, suppl_contact)
                              VALUES (?, ?, ?)
                              RETURNING suppl_id";
            $supplierParams = [$suppl_name, $suppl_address, $suppl_contact];
            $supStmt = $conn->prepare($supplierQuery);
            $supStmt->execute($supplierParams);
            $suppl_id = $conn->lastInsertId();
        } catch (PDOException $e) {
            $messages[] = "Error inserting supplier '$suppl_name': " . $e->getMessage();
            continue;
        }

        try {
            // Insert link into product_supplier table
            $linkQuery = "INSERT INTO product_supplier (prod_id, suppl_id) VALUES (?, ?)";
            $linkParams = [$prod_id, $suppl_id];
            $linkStmt = $conn->prepare($linkQuery);
            $linkStmt->execute($linkParams);
            $messages[] = "Product '$prod_name' linked with supplier '$suppl_name' successfully.";
        } catch (PDOException $e) {
            $messages[] = "Error linking product ID $prod_id with supplier ID $suppl_id: " . $e->getMessage();
        }
    }

    return $messages;
}


// Function to import companies
function importCompanies($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Companies'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Companies' not found."];
    }
    $highestRow = $sheet->getHighestDataRow();
    if ($highestRow <= 1) {
        return ["Skipping 'Companies' sheet (no data)."];
    }
    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $messages = [];
    
    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip header row
        }
        
        // Extract and sanitize data
        $company_name     = ucwords(trim($row['A'] ?? ''));
        $company_tax_no   = trim($row['B'] ?? '');
        $company_regis_no = trim($row['C'] ?? '');
        $company_type     = ucwords(trim($row['D'] ?? ''));
        $industry         = ucwords(trim($row['E'] ?? ''));
        $contact_name     = ucwords(trim($row['F'] ?? ''));
        $contact_email    = strtolower(trim($row['G'] ?? ''));
        $contact_phone    = trim($row['H'] ?? '');
        $website          = strtolower(trim($row['I'] ?? ''));
        $addr_line_1      = ucwords(trim($row['J'] ?? ''));
        $addr_line_2      = ucwords(trim($row['K'] ?? ''));
        $suburb           = ucwords(trim($row['L'] ?? ''));
        $city             = ucwords(trim($row['M'] ?? ''));
        $province         = ucwords(trim($row['N'] ?? ''));
        $country          = ucwords(trim($row['O'] ?? ''));
        $postcode         = trim($row['P'] ?? '');
        
        // Skip row if required fields are missing
        if (empty($company_name) || empty($addr_line_1)) {
            continue;
        }
        
        try {
            // Step 1: Insert Address
            $addressQuery = "INSERT INTO address (addr_line_1, addr_line_2, suburb, city, province, country, postcode, updated_by) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                             RETURNING addr_id";
            $addressStmt = $conn->prepare($addressQuery);
            $addressStmt->execute([$addr_line_1, $addr_line_2, $suburb, $city, $province, $country, $postcode, 1]);
            $addr_id = $conn->lastInsertId();
        } catch (PDOException $e) {
            $messages[] = "Error inserting address for company $company_name: " . $e->getMessage();
            continue;
        }
        
        try {
            // Step 2: Insert Company
            $companyQuery = "INSERT INTO company (company_name, company_tax_no, company_regis_no, company_type, industry, website, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW()) 
                             RETURNING company_id";
            $companyStmt = $conn->prepare($companyQuery);
            $companyStmt->execute([$company_name, $company_tax_no, $company_regis_no, $company_type, $industry, $website]);
            $company_id = $conn->lastInsertId();
            $messages[] = "Company '$company_name' imported successfully.";
        } catch (PDOException $e) {
            $messages[] = "Error inserting company $company_name: " . $e->getMessage();
            continue;
        }
        
        try {
            // Step 3: Insert Contact into Company_Contacts
            $contactQuery = "INSERT INTO company_contacts (company_id, contact_name, contact_email, contact_phone, created_at, updated_at)
                             VALUES (?, ?, ?, ?, NOW(), NOW())";
            $contactStmt = $conn->prepare($contactQuery);
            $contactStmt->execute([$company_id, $contact_name, $contact_email, $contact_phone]);
            $messages[] = "Contact for company '$company_name' imported successfully.";
        } catch (PDOException $e) {
            $messages[] = "Error inserting contact for company $company_name: " . $e->getMessage();
            continue;
        }
        
        try {
            // Step 4: Link Company and Address
            $companyAddressQuery = "INSERT INTO company_address (company_id, addr_id) VALUES (?, ?)";
            $companyAddressStmt = $conn->prepare($companyAddressQuery);
            $companyAddressStmt->execute([$company_id, $addr_id]);
            $messages[] = "Address for company '$company_name' linked successfully.";
        } catch (PDOException $e) {
            $messages[] = "Error linking address to company $company_name: " . $e->getMessage();
        }
    }
    return $messages;
}

// Function to import customers
function importCustomers($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Customers'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Customers' not found."];
    }
    $highestRow = $sheet->getHighestDataRow();
    if ($highestRow <= 1) {
        return ["Skipping 'Customers' sheet (no data)."];
    }
    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $messages = [];
    
    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip header row
        }
        
        // Extract and sanitize data
        $cust_fname   = isset($row['A']) ? ucwords(trim($row['A'])) : '';
        $cust_lname   = isset($row['B']) ? ucwords(trim($row['B'])) : '';
        $cust_init    = isset($row['C']) ? strtoupper(trim($row['C'])) : '';
        $cust_title   = isset($row['D']) ? ucwords(trim($row['D'])) : '';
        $cust_type_id = isset($row['E']) ? intval($row['E']) : 0;
        $cust_email   = isset($row['F']) ? strtolower(trim($row['F'])) : '';
        $cust_tel     = isset($row['G']) ? trim($row['G']) : '';
        $cust_cell    = isset($row['H']) ? trim($row['H']) : '';
        $company_id   = isset($row['I']) ? intval($row['I']) : 0;
        $addr_line1   = isset($row['J']) ? ucwords(trim($row['J'])) : '';
        $addr_line2   = isset($row['K']) ? ucwords(trim($row['K'])) : '';
        $suburb       = isset($row['L']) ? ucwords(trim($row['L'])) : '';
        $city         = isset($row['M']) ? ucwords(trim($row['M'])) : '';
        $province     = isset($row['N']) ? ucwords(trim($row['N'])) : '';
        $country      = isset($row['O']) ? ucwords(trim($row['O'])) : '';
        $postcode     = isset($row['P']) ? trim($row['P']) : '';
        
        // Skip row if required fields are missing
        if (empty($cust_fname) || empty($addr_line1)) {
            continue;
        }
        
        try {
            // Step 1: Insert Address
            $addressQuery = "INSERT INTO address (addr_line_1, addr_line_2, suburb, city, province, country, postcode, updated_by) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                             RETURNING addr_id";
            $addressStmt = $conn->prepare($addressQuery);
            $addressStmt->execute([$addr_line1, $addr_line2, $suburb, $city, $province, $country, $postcode, 1]);
            $addr_id = $conn->lastInsertId();
        } catch (PDOException $e) {
            $messages[] = "Error inserting address for customer $cust_fname $cust_lname: " . $e->getMessage();
            continue;
        }
        
        try {
            // Step 2: Insert Customer
            $customerQuery = "INSERT INTO customers (cust_fname, cust_lname, cust_init, cust_title, cust_type_id, cust_email, cust_tel, cust_cell, company_id, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) 
                              RETURNING cust_id";
            $customerStmt = $conn->prepare($customerQuery);
            $customerStmt->execute([$cust_fname, $cust_lname, $cust_init, $cust_title, $cust_type_id, $cust_email, $cust_tel, $cust_cell, $company_id]);
            $cust_id = $conn->lastInsertId();
            $messages[] = "Customer '$cust_fname $cust_lname' imported successfully.";
        } catch (PDOException $e) {
            $messages[] = "Error inserting customer $cust_fname $cust_lname: " . $e->getMessage();
            continue;
        }
        
        try {
            // Step 3: Link Customer and Address
            $customerAddressQuery = "INSERT INTO customer_address (cust_id, addr_id) VALUES (?, ?)";
            $customerAddressStmt = $conn->prepare($customerAddressQuery);
            $customerAddressStmt->execute([$cust_id, $addr_id]);
            $messages[] = "Address for customer '$cust_fname $cust_lname' linked successfully.";
        } catch (PDOException $e) {
            $duplicateMessage = handleDuplicateError($conn);
            if ($duplicateMessage) {
                $messages[] = "$duplicateMessage Customer '$cust_fname $cust_lname'.";
            } else {
                $messages[] = "Error linking address to customer $cust_fname $cust_lname: " . $e->getMessage();
            }
        }
    }
    return $messages;
}

// Session management for account number and user
if (isset($_GET['account_number'])) {
    // Sanitize the input
    $account_number = filter_input(INPUT_GET, 'account_number', FILTER_SANITIZE_STRING);
    $_SESSION['account_number'] = $account_number;
    // Redirect to remove the query parameter from the URL
    header("Location: invoice-setup.php");
    exit;
}

if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login if no account number is found
    header("Location: index.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? (($_SESSION['tech_logged_in'] ?? false) ? $_SESSION['tech_name'] : 'Guest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Products &amp; Display</title>
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-setup.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../js/toggle-theme.js"></script>
    <script src="../../../js/sidebar.js"></script>
    <!-- Include the improved JavaScript file or embed the script below -->
    <script src="path/to/your/improved.js" defer></script>
    <style>
        /* Optional inline CSS for error display and grids */
        .js-error-log { color: red; margin-top: 20px; }
        .grid { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        .product-card { border: 1px solid #ccc; padding: 10px; width: 200px; }
    </style>
</head>
<body id="invoice-setup">
    <?php include('../../../main/sidebar.php'); ?>
    <div class="container">
        <h1>Import Products from Excel</h1>
        <div class="form-box">
            <form action="invoice-setup.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="file" name="excel_file" accept=".xlsx, .xls">
                </div>
                <div class="button-group">
                    <button type="submit">Import</button>
                </div>
            </form>
        </div>
        <!-- PHP error and success logs -->
        <?php if (!empty(array_filter($errors))): ?>
            <div class="error-log">
                <h2>Import Errors</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error): ?>
                            <?php if (!empty($error)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($error); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if (!empty(array_filter($successMessages))): ?>
            <div class="success-log">
                <h2>Import Successes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Success Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($successMessages as $message): ?>
                            <?php if (!empty($message)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <!-- Container for JavaScript error messages -->
        <div id="js-error-log" class="js-error-log">
            <h2>JavaScript Errors</h2>
        </div>
    </div>
</body>
</html>

