<?php
session_start();
require '../../../php/db.php'; // Ensure this file initializes $conn
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check database connection
if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

$errors = []; // Initialize a global errors array


// Check if a file was uploaded
if (isset($_FILES['excel_file']) && is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);

        // Import All Sheets
        $errors = array_merge($errors, importCompanies($spreadsheet, $conn));
        $errors = array_merge($errors, importCustomers($spreadsheet, $conn));
        $errors = array_merge($errors, importProducts($spreadsheet, $conn));

    } catch (Exception $e) {
        $errors[] = "Error loading file: " . $e->getMessage();
    }
} else {
    $errors[] = "No file uploaded.";
}

// Helper function to handle duplicate key errors
function handleDuplicateError($conn) {
    $errorMessage = pg_last_error($conn);
    if (strpos($errorMessage, 'duplicate key value violates unique constraint') !== false) {
        // Return a simple message for duplicate entries
        return "Row skipped due to duplicate.";
    } else {
        // Return null if not a duplicate error
        return null;
    }
}

// Function to import products
function importProducts($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Products'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Products' not found."];
    }

    // Get the highest row number
    $highestRow = $sheet->getHighestDataRow(); // This finds the last non-empty row

    if ($highestRow <= 1) {
        return ["Skipping 'Products' sheet (no data)."];
    }

    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $errors = [];

    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip the header row
        }

        // Extract product data from the row
        $prod_id = intval($row['A']);
        $prod_name = trim($row['B']); // Trim to avoid issues with leading/trailing spaces
        $prod_descr = trim($row['C']);
        $prod_price = floatval($row['D']);
        $prod_stock = intval($row['E']);

        // Skip row if all required fields are empty
        if (empty($prod_id) && empty($prod_name) && empty($prod_descr) && empty($prod_price) && empty($prod_stock)) {
            continue;
        }

        // Insert product into the database
        $query = "INSERT INTO product (prod_id, prod_name, prod_descr, prod_price, prod_stock) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($conn, $query, array($prod_id, $prod_name, $prod_descr, $prod_price, $prod_stock));

        // Use the helper function to check for duplicates
        $duplicateMessage = handleDuplicateError($conn);
        if ($duplicateMessage) {
            // If it's a duplicate, just skip this row and add the message
            $errors[] = "$duplicateMessage Product name '$prod_name' with ID $prod_id.";
        } elseif (!$result) {
            // If it's another error, log it
            $errors[] = "Error inserting product ID $prod_id: " . pg_last_error($conn);
        }
    }

    return empty($errors) ? ["Products imported successfully!"] : $errors;
}

// Function to import companies
function importCompanies($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Companies'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Companies' not found."];
    }

    // Get the highest row number
    $highestRow = $sheet->getHighestDataRow(); // This finds the last non-empty row

    if ($highestRow <= 1) {
        return ["Skipping 'Companies' sheet (no data)."];
    }

    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $errors = [];

    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip the header row
        }

        // Extract data from each row
        $company_name    = ucwords(trim($row['A']));
        $company_tax_no  = trim($row['B']);
        $company_regis_no= trim($row['C']);
        $company_type    = ucwords(trim($row['D']));
        $industry        = ucwords(trim($row['E']));
        $contact_name    = ucwords(trim($row['F']));
        $contact_email   = strtolower(trim($row['G']));
        $contact_phone   = trim($row['H']);
        $website         = strtolower(trim($row['I']));
        $addr_line_1     = ucwords(trim($row['J']));
        $addr_line_2     = ucwords(trim($row['K']));
        $suburb          = ucwords(trim($row['L']));
        $city            = ucwords(trim($row['M']));
        $province        = ucwords(trim($row['N']));
        $country         = ucwords(trim($row['O']));
        $postcode        = trim($row['P']);

        // Step 1: Insert Address
        $addressQuery = "INSERT INTO address (addr_line_1, addr_line_2, suburb, city, province, country, postcode, updated_by) 
                        VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING addr_id";
        $addressResult = pg_query_params($conn, $addressQuery, array($addr_line_1, $addr_line_2, $suburb, $city, $province, $country, $postcode, 1));

        if (!$addressResult) {
            $errors[] = "Error inserting address for company $company_name: " . pg_last_error($conn);
            continue;
        }
        
        $addressData = pg_fetch_assoc($addressResult);
        $addr_id = $addressData['addr_id'];

        // Step 2: Insert Company (without contact fields)
        $companyQuery = "INSERT INTO company (company_name, company_tax_no, company_regis_no, company_type, industry, website, created_at, updated_at) 
                         VALUES ($1, $2, $3, $4, $5, $6, NOW(), NOW()) 
                         RETURNING company_id";
        $companyResult = pg_query_params($conn, $companyQuery, array($company_name, $company_tax_no, $company_regis_no, $company_type, $industry, $website));

        // Use the helper function to check for duplicates
        $duplicateMessage = handleDuplicateError($conn);
        if ($duplicateMessage) {
            // If it's a duplicate, just skip this row and add the message
            $errors[] = "$duplicateMessage Company name '$company_name'.";
            continue;
        } elseif (!$companyResult) {
            // If it's another error, log it
            $errors[] = "Error inserting company $company_name: " . pg_last_error($conn);
            continue;
        }

        $companyData = pg_fetch_assoc($companyResult);
        $company_id = $companyData['company_id'];

        // Step 3: Insert Contact into Company_Contacts
        $contactQuery = "INSERT INTO company_contacts (company_id, contact_name, contact_email, contact_phone, created_at, updated_at)
                         VALUES ($1, $2, $3, $4, NOW(), NOW())";
        $contactResult = pg_query_params($conn, $contactQuery, array($company_id, $contact_name, $contact_email, $contact_phone));
        if (!$contactResult) {
            $errors[] = "Error inserting contact for company $company_name: " . pg_last_error($conn);
            continue;
        }

        // Step 4: Insert into Company_Address (linking company and address)
        $companyAddressQuery = "INSERT INTO company_address (company_id, addr_id) VALUES ($1, $2)";
        $companyAddressResult = pg_query_params($conn, $companyAddressQuery, array($company_id, $addr_id));

        if (!$companyAddressResult) {
            $errors[] = "Error linking address to company $company_name: " . pg_last_error($conn);
        }
    }

    return empty($errors) ? ["Companies imported successfully!"] : $errors;
}

// Function to import customers
function importCustomers($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Customers'); // Ensure sheet name matches
    if (!$sheet) {
        return ["Sheet 'Customers' not found."];
    }

    // Get the highest row number
    $highestRow = $sheet->getHighestDataRow(); // This finds the last non-empty row

    if ($highestRow <= 1) {
        return ["Skipping 'Customers' sheet (no data)."];
    }

    $sheetData = $sheet->toArray(null, true, true, true);
    $isFirstRow = true;
    $errors = [];

    foreach ($sheetData as $row) {
        if ($isFirstRow) {
            $isFirstRow = false;
            continue; // Skip the header row
        }

        // Extract data from each row with trimming and proper casing
        $cust_fname = isset($row['A']) ? ucwords(trim($row['A'])) : null;       // First name (trim and proper case)
        $cust_lname = isset($row['B']) ? ucwords(trim($row['B'])) : null;       // Last name (trim and proper case)
        $cust_init = isset($row['C']) ? strtoupper(trim($row['C'])) : null;      // Initial (trim and upper case)
        $cust_title = isset($row['D']) ? ucwords(trim($row['D'])) : null;       // Title (trim and proper case)
        $cust_type_id = isset($row['E']) ? intval($row['E']) : null;             // Customer type ID (convert to integer)
        $cust_email = isset($row['F']) ? strtolower(trim($row['F'])) : null;    // Email address (trim and convert to lowercase)
        $cust_tel = isset($row['G']) ? trim($row['G']) : null;                  // Telephone number (trim)
        $cust_cell = isset($row['H']) ? trim($row['H']) : null;                 // Cell phone number (trim)
        $company_id = isset($row['I']) ? intval($row['I']) : null;              // Company ID (convert to integer)
        $addr_line1 = isset($row['J']) ? ucwords(trim($row['J'])) : null;       // Address line 1 (trim and proper case)
        $addr_line2 = isset($row['K']) ? ucwords(trim($row['K'])) : null;       // Address line 2 (trim and proper case)
        $suburb = isset($row['L']) ? ucwords(trim($row['L'])) : null;           // Suburb (trim and proper case)
        $city = isset($row['M']) ? ucwords(trim($row['M'])) : null;             // City (trim and proper case)
        $province = isset($row['N']) ? ucwords(trim($row['N'])) : null;         // Province (trim and proper case)
        $country = isset($row['O']) ? ucwords(trim($row['O'])) : null;          // Country (trim and proper case)
        $postcode = isset($row['P']) ? trim($row['P']) : null;                  // Postcode (trim)

        // Step 1: Insert Address
        $addressQuery = "INSERT INTO address (addr_line_1, addr_line_2, suburb, city, province, country, postcode, updated_by) 
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING addr_id";

        $addressResult = pg_query_params($conn, $addressQuery, array($addr_line1, $addr_line2, $suburb, $city, $province, $country, $postcode, 1));

        if (!$addressResult) {
            $errors[] = "Error inserting address for customer $cust_fname $cust_lname: " . pg_last_error($conn);
            continue;
        }

        $addressData = pg_fetch_assoc($addressResult);
        $addr_id = $addressData['addr_id'];

        // Step 2: Insert Customer
        $customerQuery = "INSERT INTO customers (cust_fname, cust_lname, cust_init, cust_title, cust_type_id, cust_email, cust_tel, cust_cell, company_id, created_at, updated_at) 
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW(), NOW()) RETURNING cust_id";

        $customerResult = pg_query_params($conn, $customerQuery, array($cust_fname, $cust_lname, $cust_init, $cust_title, $cust_type_id, $cust_email, $cust_tel, $cust_cell, $company_id));

        if (!$customerResult) {
            $errors[] = "Error inserting customer $cust_fname $cust_lname: " . pg_last_error($conn);
            continue;
        }

        $customerData = pg_fetch_assoc($customerResult);
        $cust_id = $customerData['cust_id'];

        // Step 3: Insert into Customer Address
        $customerAddressQuery = "INSERT INTO customer_address (cust_id, addr_id) VALUES ($1, $2)";
        $customerAddressResult = pg_query_params($conn, $customerAddressQuery, array($cust_id, $addr_id));

        // Use the helper function to check for duplicates
        $duplicateMessage = handleDuplicateError($conn);
        if ($duplicateMessage) {
            // If it's a duplicate, just skip this row and add the message
            $errors[] = "$duplicateMessage Customer name '$cust_fname $cust_lname'.";
            continue;
        } elseif (!$customerAddressResult) {
            // If it's another error, log it
            $errors[] = "Error linking address to customer $cust_fname $cust_lname: " . pg_last_error($conn);
        }
    }

    return empty($errors) ? ["Customers imported successfully!"] : $errors;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Products</title>
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../css/invoice-setup.css">
    <script src="../../../js/toggle-theme.js"></script>
</head>
<body>
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
    </div>
</body>
</html>
