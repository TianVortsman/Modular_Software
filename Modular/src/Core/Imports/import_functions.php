<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

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

// Time and Attendance Functions
function importTimeEntries($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO attendance_records (employee_id, shift_id, date, time_in, time_out, status, notes) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing time entry: " . $e->getMessage();
    }
    return $errors;
}

function importShifts($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO shifts (shift_name, start_time, end_time) VALUES (?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing shift: " . $e->getMessage();
    }
    return $errors;
}

// Mobile Module Functions
function importMobileUsers($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO mobile_users (username, device_id, access_level) VALUES (?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing mobile user: " . $e->getMessage();
    }
    return $errors;
}

function importAppSettings($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO app_settings (setting_name, setting_value) VALUES (?, ?)");
            $stmt->execute([$row[0], $row[1]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing app setting: " . $e->getMessage();
    }
    return $errors;
}

// Payroll Functions
function importSalaryData($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO salary_data (employee_id, basic_salary, allowances, deductions) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing salary data: " . $e->getMessage();
    }
    return $errors;
}

function importTaxInfo($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO tax_information (employee_id, tax_number, tax_category) VALUES (?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing tax information: " . $e->getMessage();
    }
    return $errors;
}

// Access Control Functions
function importAccessPermissions($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO access_permissions (user_id, module_name, permission_level) 
                                  VALUES (?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing access permission: " . $e->getMessage();
    }
    return $errors;
}

function importUserGroups($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO user_groups (group_name, description) VALUES (?, ?)");
            $stmt->execute([$row[0], $row[1]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing user group: " . $e->getMessage();
    }
    return $errors;
}

// Asset Management Functions
function importAssets($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO assets (asset_name, asset_type, purchase_date, value) VALUES (?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing asset: " . $e->getMessage();
    }
    return $errors;
}

function importMaintenanceRecords($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO asset_maintenance (asset_id, maintenance_date, description, cost) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing maintenance record: " . $e->getMessage();
    }
    return $errors;
}

// Fleet Management Functions
function importFuelRecords($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO fuel_records (vehicle_id, fill_date, liters, cost) VALUES (?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing fuel record: " . $e->getMessage();
    }
    return $errors;
}

// Support/Help Desk Functions
function importTickets($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO tickets (subject, description, priority, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing ticket: " . $e->getMessage();
    }
    return $errors;
}

function importCategories($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO ticket_categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$row[0], $row[1]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing category: " . $e->getMessage();
    }
    return $errors;
}

// Project Management Functions
function importProjects($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO projects (project_name, description, start_date, end_date, status) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing project: " . $e->getMessage();
    }
    return $errors;
}

function importTasks($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO tasks (project_id, task_name, description, due_date, status) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing task: " . $e->getMessage();
    }
    return $errors;
}

// HR Functions
function importEmployees($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, email, phone, hire_date, position_id, 
                                  department) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing employee: " . $e->getMessage();
    }
    return $errors;
}

function importTrainingRecords($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO training_records (employee_id, training_name, completion_date, status) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing training record: " . $e->getMessage();
    }
    return $errors;
}

// Invoice Functions
function importInvoices($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO invoices (order_id, invoice_number, invoice_date, payment_status_id, 
                                  payment_method_id, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing invoice: " . $e->getMessage();
    }
    return $errors;
}

function importSuppliers($conn, $data) {
    $errors = [];
    try {
        foreach ($data as $row) {
            $stmt = $conn->prepare("INSERT INTO suppliers (company_id, supp_type_id) VALUES (?, ?)");
            $stmt->execute([$row[0], $row[1]]);
        }
    } catch (PDOException $e) {
        $errors[] = "Error importing supplier: " . $e->getMessage();
    }
    return $errors;
}

// Function to import employees for Time and Attendance
function importTimeAndAttEmployees($conn, $data) {
    $messages = [];
    $successCount = 0;
    $errors = [];
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Skip header row
        $headerRow = array_shift($data);
        
        // Debug: Print the header row
        error_log("Header Row: " . print_r($headerRow, true));
        
        foreach ($data as $index => $row) {
            $rowNum = $index + 2; // Adding 2 because index starts at 0 and we skipped header row
            
            try {
                // Debug: Print each row being processed
                error_log("Processing Row " . $rowNum . ": " . print_r($row, true));
                
                // Extract required data
                $firstName = trim($row[0] ?? '');
                $lastName = trim($row[1] ?? '');
                $employeeNumber = trim($row[2] ?? '');
                $clockNumber = $row[3] ?? null;
                
                // Validate required fields
                $validationErrors = [];
                if (empty($firstName)) $validationErrors[] = "First Name is required";
                if (empty($lastName)) $validationErrors[] = "Last Name is required";
                if (empty($employeeNumber)) $validationErrors[] = "Employee Number is required";
                if (!isset($clockNumber)) $validationErrors[] = "Clock Number is required";
                
                if (!empty($validationErrors)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => implode(', ', $validationErrors)
                    ];
                    continue;
                }

                // Validate clock number is numeric
                if (!is_numeric($clockNumber)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Clock Number must be numeric"
                    ];
                    continue;
                }
                $clockNumber = intval($clockNumber);

                // Extract optional data with validation
                $email = !empty($row[4]) ? filter_var(trim($row[4]), FILTER_SANITIZE_EMAIL) : null;
                if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Invalid email format"
                    ];
                    continue;
                }

                $phoneNumber = trim($row[5] ?? '');
                $hireDate = !empty($row[6]) ? trim($row[6]) : date('Y-m-d');
                if (!empty($row[6]) && !strtotime($hireDate)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Invalid hire date format"
                    ];
                    continue;
                }

                $division = trim($row[7] ?? '');
                $groupName = trim($row[8] ?? '');
                $department = trim($row[9] ?? '');
                $costCenter = trim($row[10] ?? '');
                $positionId = !empty($row[11]) ? intval($row[11]) : null;
                $status = trim($row[12] ?? 'active');
                $employmentType = trim($row[13] ?? 'Permanent');
                $workScheduleType = trim($row[14] ?? 'Open');
                $biometricId = trim($row[15] ?? '');
                $emergencyContactName = trim($row[16] ?? '');
                $emergencyContactPhone = trim($row[17] ?? '');
                $address = trim($row[18] ?? '');

                // Validate status
                $validStatuses = ['active', 'inactive', 'terminated'];
                if (!in_array(strtolower($status), $validStatuses)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Invalid status. Must be one of: " . implode(', ', $validStatuses)
                    ];
                    continue;
                }

                // Validate employment type
                $validEmploymentTypes = ['Permanent', 'Contract'];
                if (!in_array($employmentType, $validEmploymentTypes)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Invalid employment type. Must be one of: " . implode(', ', $validEmploymentTypes)
                    ];
                    continue;
                }

                // Validate work schedule type
                $validWorkScheduleTypes = ['Open', 'Fixed', 'Rotating'];
                if (!in_array($workScheduleType, $validWorkScheduleTypes)) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Invalid work schedule type. Must be one of: " . implode(', ', $validWorkScheduleTypes)
                    ];
                    continue;
                }

                // Check for duplicate employee number
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE employee_number = ?");
                $checkStmt->execute([$employeeNumber]);
                if ($checkStmt->fetchColumn() > 0) {
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Employee number already exists"
                    ];
                    continue;
                }

                // Insert into employees table with all fields
                $query = "INSERT INTO employees (
                    first_name, last_name, employee_number, clock_number, 
                    email, phone_number, hire_date, division, 
                    group_name, department, cost_center, position_id,
                    status, employment_type, work_schedule_type, biometric_id,
                    emergency_contact_name, emergency_contact_phone, address,
                    created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    NOW(), NOW()
                )";
                
                $params = [
                    $firstName, $lastName, $employeeNumber, $clockNumber,
                    $email, $phoneNumber, $hireDate, $division,
                    $groupName, $department, $costCenter, $positionId,
                    $status, $employmentType, $workScheduleType, $biometricId,
                    $emergencyContactName, $emergencyContactPhone, $address
                ];
                
                $stmt = $conn->prepare($query);
                
                if ($stmt->execute($params)) {
                    $successCount++;
                    $messages[] = "Successfully imported employee: $firstName $lastName";
                    error_log("Successfully imported employee: $firstName $lastName");
                } else {
                    $error = $stmt->errorInfo();
                    $errors[] = [
                        'row' => $rowNum,
                        'data' => json_encode($row),
                        'message' => "Database error: " . ($error[2] ?? 'Unknown error')
                    ];
                }
            } catch (PDOException $e) {
                $errors[] = [
                    'row' => $rowNum,
                    'data' => json_encode($row),
                    'message' => "Database error: " . $e->getMessage()
                ];
            }
        }
        
        // Commit transaction if there were no errors
        if (empty($errors)) {
            $conn->commit();
            return [
                'success' => true,
                'message' => "Successfully imported $successCount employees",
                'errors' => [],
                'successCount' => $successCount,
                'totalRows' => count($data)
            ];
        } else {
            // If there were some successful imports along with errors
            if ($successCount > 0) {
                $conn->commit();
                return [
                    'success' => true,
                    'message' => "Imported $successCount employees with some errors",
                    'errors' => $errors,
                    'successCount' => $successCount,
                    'totalRows' => count($data)
                ];
            } else {
                // If no successful imports, rollback
                $conn->rollBack();
                return [
                    'success' => false,
                    'message' => "Import failed: No employees were imported",
                    'errors' => $errors,
                    'successCount' => 0,
                    'totalRows' => count($data ?? [])
                ];
            }
        }
        
    } catch (Exception $e) {
        // Rollback transaction on critical error
        $conn->rollBack();
        error_log("Critical import error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => "Critical error during import: " . $e->getMessage(),
            'errors' => [[
                'row' => 'N/A',
                'data' => 'N/A',
                'message' => $e->getMessage()
            ]],
            'successCount' => $successCount,
            'totalRows' => count($data ?? [])
        ];
    }
} 