<?php
require_once '../../../vendor/autoload.php';
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

//Invoicing Clients import 


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
    
    // Skip header row
    $headerRow = array_shift($data);
    
    foreach ($data as $index => $row) {
        $rowNum = $index + 2; // Adding 2 because index starts at 0 and we skipped header row
        
        try {
            // Start a new transaction for each record
            $conn->beginTransaction();
            
            // Extract data based on Excel column order
            $title = trim($row[0] ?? '');
            $gender = trim($row[1] ?? '');
            $firstName = trim($row[2] ?? '');
            $lastName = trim($row[3] ?? '');
            $idNumber = trim($row[4] ?? '');
            $employeeNumber = trim($row[5] ?? '');
            $clockNumber = $row[6] ?? null;
            $email = !empty($row[7]) ? filter_var(trim($row[7]), FILTER_SANITIZE_EMAIL) : null;
            $phoneNumber = trim($row[8] ?? '');
            $hireDate = !empty($row[9]) ? trim($row[9]) : date('Y-m-d');
            $division = trim($row[10] ?? '');
            $groupName = trim($row[11] ?? '');
            $department = trim($row[12] ?? '');
            $costCenter = trim($row[13] ?? '');
            $position = trim($row[14] ?? '');
            $rateType = trim($row[15] ?? '');
            $rate = isset($row[16]) && is_numeric(str_replace(['R',' ','ZAR'], '', $row[16])) ? floatval(preg_replace('/[^0-9.]/', '', $row[16])) : null;
            $overtime = trim($row[17] ?? '');
            $status = trim($row[18] ?? 'active');
            $employmentType = trim($row[19] ?? 'Permanent');
            $workScheduleType = trim($row[20] ?? 'Open');
            $payPeriod = trim($row[21] ?? '');
            $emergencyContactName = trim($row[22] ?? '');
            $emergencyContactPhone = trim($row[23] ?? '');
            $address = trim($row[24] ?? '');
            $biometricId = null; // Not present in this Excel, set to null

            // Validate required fields
            $validationErrors = [];
            if (empty($firstName)) $validationErrors[] = "First Name is required";
            if (empty($lastName)) $validationErrors[] = "Last Name is required";
            if (empty($employeeNumber)) $validationErrors[] = "Employee Number is required";
            if (!isset($clockNumber)) $validationErrors[] = "Clock Number is required";
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validationErrors[] = "Invalid email format";
            }
            if (!empty($hireDate) && !strtotime($hireDate)) {
                $validationErrors[] = "Invalid hire date format";
            }
            if (!is_numeric($clockNumber)) {
                $validationErrors[] = "Clock Number must be numeric";
            }
            
            if (!empty($validationErrors)) {
                $errors[] = [
                    'row' => $rowNum,
                    'data' => json_encode($row),
                    'message' => implode(', ', $validationErrors)
                ];
                $conn->rollBack();
                continue;
            }

            // Check for duplicate employee number
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM core.employees WHERE employee_number = ?");
            $checkStmt->execute([$employeeNumber]);
            if ($checkStmt->fetchColumn() > 0) {
                $errors[] = [
                    'row' => $rowNum,
                    'data' => json_encode($row),
                    'message' => "Employee number already exists"
                ];
                $conn->rollBack();
                continue;
            }

            // 1. Insert into core.employees
            $employeeQuery = "
                INSERT INTO core.employees (
                    first_name,
                    last_name,
                    employee_number,
                    is_sales
                ) VALUES (
                    :first_name,
                    :last_name,
                    :employee_number,
                    :is_sales
                ) RETURNING employee_id
            ";
            
            $employeeParams = [
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':employee_number' => $employeeNumber,
                ':is_sales' => false
            ];
            
            $stmt = $conn->prepare($employeeQuery);
            $stmt->execute($employeeParams);
            $employeeId = $conn->lastInsertId();

            // 2. Insert into core.employee_contact
            if (!empty($email) || !empty($phoneNumber)) {
                $contactQuery = "
                    INSERT INTO core.employee_contact (
                        employee_id,
                        email,
                        phone_number
                    ) VALUES (
                        :employee_id,
                        :email,
                        :phone_number
                    )
                ";
                
                $contactParams = [
                    ':employee_id' => $employeeId,
                    ':email' => $email,
                    ':phone_number' => $phoneNumber
                ];
                
                $stmt = $conn->prepare($contactQuery);
                $stmt->execute($contactParams);
            }

            // 3. Insert into core.employee_personal
            if (!empty($gender)) {
                $personalQuery = "
                    INSERT INTO core.employee_personal (
                        employee_id,
                        gender
                    ) VALUES (
                        :employee_id,
                        :gender
                    )
                ";
                
                $personalParams = [
                    ':employee_id' => $employeeId,
                    ':gender' => $gender
                ];
                
                $stmt = $conn->prepare($personalQuery);
                $stmt->execute($personalParams);
            }

            // 4. Insert into core.employee_employment
            $employmentQuery = "
                INSERT INTO core.employee_employment (
                    employee_id,
                    hire_date,
                    position,
                    department,
                    division,
                    group,
                    cost_center,
                    employment_type,
                    status,
                    work_week,
                    title,
                    id_number,
                    rate_type,
                    rate,
                    overtime,
                    pay_period
                ) VALUES (
                    :employee_id,
                    :hire_date,
                    :position,
                    :department,
                    :division,
                    :group,
                    :cost_center,
                    :employment_type,
                    :status,
                    :work_week,
                    :title,
                    :id_number,
                    :rate_type,
                    :rate,
                    :overtime,
                    :pay_period
                )
            ";
            
            $employmentParams = [
                ':employee_id' => $employeeId,
                ':hire_date' => $hireDate,
                ':position' => $position,
                ':department' => $department,
                ':division' => $division,
                ':group' => $groupName,
                ':cost_center' => $costCenter,
                ':employment_type' => $employmentType,
                ':status' => $status,
                ':work_week' => $workScheduleType,
                ':title' => $title,
                ':id_number' => $idNumber,
                ':rate_type' => $rateType,
                ':rate' => $rate,
                ':overtime' => $overtime,
                ':pay_period' => $payPeriod
            ];
            
            $stmt = $conn->prepare($employmentQuery);
            $stmt->execute($employmentParams);

            // 5. Insert into core.employee_emergency_contact if emergency contact data exists
            if (!empty($emergencyContactName) || !empty($emergencyContactPhone)) {
                $emergencyQuery = "
                    INSERT INTO core.employee_emergency_contact (
                        employee_id,
                        contact_name,
                        contact_phone
                    ) VALUES (
                        :employee_id,
                        :contact_name,
                        :contact_phone
                    )
                ";
                
                $emergencyParams = [
                    ':employee_id' => $employeeId,
                    ':contact_name' => $emergencyContactName,
                    ':contact_phone' => $emergencyContactPhone
                ];
                
                $stmt = $conn->prepare($emergencyQuery);
                $stmt->execute($emergencyParams);
            }

            // 6. Handle address if provided
            if (!empty($address)) {
                // First insert into core.address
                $addressQuery = "
                    INSERT INTO core.address (
                        addr_line_1
                    ) VALUES (
                        :addr_line_1
                    ) RETURNING addr_id
                ";
                
                $addressParams = [
                    ':addr_line_1' => $address
                ];
                
                $stmt = $conn->prepare($addressQuery);
                $stmt->execute($addressParams);
                $addressId = $conn->lastInsertId();
                
                // Then update employee_contact with the address_id
                $updateContactQuery = "
                    UPDATE core.employee_contact 
                    SET address_id = :address_id 
                    WHERE employee_id = :employee_id
                ";
                
                $stmt = $conn->prepare($updateContactQuery);
                $stmt->execute([
                    ':address_id' => $addressId,
                    ':employee_id' => $employeeId
                ]);
            }
            
            $conn->commit();
            $successCount++;
            $messages[] = "Successfully imported employee: $firstName $lastName";
            
        } catch (PDOException $e) {
            $errors[] = [
                'row' => $rowNum,
                'data' => json_encode($row),
                'message' => "Database error: " . $e->getMessage()
            ];
            $conn->rollBack();
        }
    }
    
    return [
        'success' => $successCount > 0,
        'message' => $successCount > 0 ? "Imported $successCount employees" . ($errors ? " with some errors" : "") : "No employees were imported",
        'errors' => $errors,
        'successCount' => $successCount,
        'totalRows' => count($data)
    ];
}

function importProducts($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Products');
    if (!$sheet) return [
        'success' => false,
        'message' => "Sheet 'Products' not found.",
        'errors' => [],
        'successCount' => 0,
        'totalRows' => 0
    ];

    $sheetData = $sheet->toArray(null, true, true, true);
    if (count($sheetData) <= 1) return [
        'success' => false,
        'message' => "No data found in 'Products' sheet.",
        'errors' => [],
        'successCount' => 0,
        'totalRows' => 0
    ];

    $errors = [];
    $successCount = 0;
    // Build a map of header name (lowercase, trimmed) => Excel column letter
    $headerRow = $sheetData[1];
    $headerMap = [];
    foreach ($headerRow as $colLetter => $colName) {
        $colName = strtolower(trim($colName));
        if ($colName !== '') {
            $headerMap[$colName] = $colLetter;
        }
    }

    $required = ['product_name', 'product_price', 'product_type_name', 'supplier_name'];
    foreach ($required as $col) {
        if (!isset($headerMap[$col])) return [
            'success' => false,
            'message' => "Missing required column: $col",
            'errors' => [],
            'successCount' => 0,
            'totalRows' => 0
        ];
    }

    for ($i = 2; $i <= count($sheetData); $i++) {
        $row = $sheetData[$i];
        $get = function($field) use ($headerMap, $row) {
            $colLetter = $headerMap[$field] ?? null;
            return $colLetter !== null ? trim($row[$colLetter] ?? '') : '';
        };
        $missing = [];
        foreach (['product_name', 'product_price', 'product_type_name', 'supplier_name'] as $req) {
            if (empty($get($req))) $missing[] = $req;
        }
        if ($missing) {
            $errors[] = [
                'row' => $i,
                'product_name' => $get('product_name'),
                'product_status' => 'skipped',
                'reason' => 'Missing required: ' . implode(', ', $missing)
            ];
            continue;
        }
        // Clean and map fields robustly
        $product_name = ucwords($get('product_name'));
        $product_description = $get('product_description');
        $product_price = floatval($get('product_price'));
        $product_status = strtolower($get('product_status') ?: 'active');
        $sku = strtoupper($get('sku'));
        $barcode = $get('barcode');
        $product_type_name = strtolower(trim($get('product_type_name')));
        $category_name = ucwords($get('category_name'));
        $subcategory_name = ucwords($get('subcategory_name'));
        $tax_rate = floatval($get('tax_rate') ?: 0);
        $discount = floatval($get('discount') ?: 0);
        $notes = $get('notes');
        $product_stock_quantity = intval($get('stock_quantity') ?: 0);
        $product_reorder_level = intval($get('reorder_level') ?: 0);
        $product_lead_time = intval($get('lead_time') ?: 0);
        $product_weight = floatval($get('product_weight') ?: 0);
        $product_dimensions = $get('dimensions');
        $product_brand = ucwords($get('brand'));
        $product_manufacturer = ucwords($get('manufacturer'));
        $warranty_period = $get('warranty_period');
        $supplier_name = ucwords($get('supplier_name'));
        $supplier_address = $get('supplier_address');
        $supplier_contact = $get('supplier_contact');

        try {
            // 1. Resolve type/category/subcategory IDs (create if not exist)
            $product_type_id = getOrCreateId($conn, 'core.product_types', 'product_type_name', $product_type_name, 'product_type_id');
            $category_id = $category_name ? getOrCreateId($conn, 'core.product_categories', 'category_name', $category_name, 'category_id', ['product_type_id' => $product_type_id]) : null;
            $subcategory_id = $subcategory_name ? getOrCreateId($conn, 'core.product_subcategories', 'subcategory_name', $subcategory_name, 'subcategory_id', ['category_id' => $category_id]) : null;

            // 2. Supplier: get or create
            $supplier_id = getOrCreateId($conn, 'inventory.supplier', 'supplier_name', $supplier_name, 'supplier_id', [
                'supplier_address' => $supplier_address,
                'supplier_contact' => $supplier_contact
            ]);

            // 3. Tax Rate: get or create tax_rate_id
            $tax_rate_id = null;
            if ($tax_rate > 0) {
                // Try to find an existing tax_rate with this rate
                $stmtTax = $conn->prepare("SELECT tax_rate_id FROM core.tax_rates WHERE rate = ? LIMIT 1");
                $stmtTax->execute([$tax_rate]);
                $tax_rate_id = $stmtTax->fetchColumn();
                if (!$tax_rate_id) {
                    // Insert new tax rate
                    $stmtTaxInsert = $conn->prepare("INSERT INTO core.tax_rates (tax_name, rate, is_active, created_at, updated_at) VALUES (?, ?, true, NOW(), NOW()) RETURNING tax_rate_id");
                    $tax_name = $tax_rate . '%';
                    $stmtTaxInsert->execute([$tax_name, $tax_rate]);
                    $tax_rate_id = $stmtTaxInsert->fetchColumn();
                }
            }

            // 4. Insert into core.products (not core.product)
            $stmt = $conn->prepare("
                INSERT INTO core.products (
                    product_name, product_description, product_price, product_status, sku, barcode, product_type_id, category_id, subcategory_id, tax_rate_id, discount, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING product_id
            ");
            $stmt->execute([
                $product_name, $product_description, $product_price, $product_status, $sku, $barcode, $product_type_id, $category_id, $subcategory_id, $tax_rate_id, $discount, $notes
            ]);
            $product_id = $stmt->fetchColumn();

            // 5. Insert into inventory.product_inventory (use correct field names)
            $stmt2 = $conn->prepare("
                INSERT INTO inventory.product_inventory (
                    product_id, product_stock_quantity, product_reorder_level, product_lead_time, product_weight, product_dimensions, product_brand, product_manufacturer, warranty_period
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt2->execute([
                $product_id, $product_stock_quantity, $product_reorder_level, $product_lead_time, $product_weight, $product_dimensions, $product_brand, $product_manufacturer, $warranty_period
            ]);

            // 6. Link product to supplier
            $stmt3 = $conn->prepare("INSERT INTO inventory.product_supplier (product_id, supplier_id) VALUES (?, ?) ON CONFLICT DO NOTHING");
            $stmt3->execute([$product_id, $supplier_id]);

            $successCount++;
        } catch (PDOException $e) {
            $reason = $e->getMessage();
            // User-friendly duplicate barcode error
            if (strpos($reason, 'duplicate key value violates unique constraint') !== false && strpos($reason, 'product_barcode_key') !== false) {
                $reason = 'Duplicate barcode' . ($barcode ? ': ' . $barcode : '');
            } else if (strpos($reason, 'duplicate key value violates unique constraint') !== false) {
                $reason = 'Duplicate value for a unique field.';
            } else {
                // Shorten generic SQL errors
                $reason = preg_replace('/SQLSTATE\[[^]]*\]: [^:]*: [0-9]+ ERROR:  /', '', $reason);
                $reason = strtok($reason, "\n"); // Only first line
            }
            $errors[] = [
                'row' => $i,
                'product_name' => $product_name,
                'product_status' => 'failed',
                'reason' => $reason
            ];
        }
    }
    return [
        'success' => $successCount > 0,
        'message' => $successCount > 0 ? "Imported $successCount products" . ($errors ? " with some errors" : "") : "No products were imported",
        'errors' => $errors,
        'successCount' => $successCount,
        'totalRows' => count($sheetData) - 1
    ];
}

function nullIfEmptyDate($val) {
    $v = trim($val);
    return ($v === '' || strtolower($v) === 'n/a') ? null : $v;
}

function nullIfEmptyBool($val) {
    $v = trim(strtolower($val));
    if ($v === '' || $v === 'n/a') return true;
    if ($v === 'true' || $v === 'yes' || $v === '1') return true;
    if ($v === 'false' || $v === 'no' || $v === '0') return false;
    return true; // fallback to true
}

/**
 * Import clients (business and private) from Excel
 * Sheet name: 'Clients'
 * Required columns: client_type, client_name/first_name/last_name, client_email, client_cell, client_tell, client_status, plus address/contact fields
 * Handles both company and customer types, creates addresses and contacts, and links them.
 */
function importClients($spreadsheet, $conn) {
    $sheet = $spreadsheet->getSheetByName('Clients');
    if (!$sheet) return [
        'success' => false,
        'message' => "Sheet 'Clients' not found.",
        'errors' => [],
        'successCount' => 0,
        'totalRows' => 0
    ];

    $sheetData = $sheet->toArray(null, true, true, true);
    if (count($sheetData) <= 1) return [
        'success' => false,
        'message' => "No data found in 'Clients' sheet.",
        'errors' => [],
        'successCount' => 0,
        'totalRows' => 0
    ];

    $errors = [];
    $successCount = 0;
    $headerRow = $sheetData[1];
    $headerMap = [];
    foreach ($headerRow as $colLetter => $colName) {
        $colName = strtolower(trim($colName));
        if ($colName !== '') {
            $headerMap[$colName] = $colLetter;
        }
    }

    // Required fields for both types
    $required = ['client_type', 'client_email', 'client_status'];
    foreach ($required as $col) {
        if (!isset($headerMap[$col])) return [
            'success' => false,
            'message' => "Missing required column: $col",
            'errors' => [],
            'successCount' => 0,
            'totalRows' => 0
        ];
    }

    for ($i = 2; $i <= count($sheetData); $i++) {
        $row = $sheetData[$i];
        $get = function($field) use ($headerMap, $row) {
            $colLetter = $headerMap[$field] ?? null;
            return $colLetter !== null ? trim($row[$colLetter] ?? '') : '';
        };
        $client_type = strtolower($get('client_type'));
        $client_email = $get('client_email');
        $client_status = $get('client_status');
        // Company fields
        $client_name = $get('client_name');
        $industry = $get('industry');
        $registration_number = $get('registration_number');
        $vat_number = $get('vat_number');
        $website = $get('website');
        // Customer fields
        $first_name = $get('first_name');
        $last_name = $get('last_name');
        $dob = nullIfEmptyDate($get('dob'));
        $gender = $get('gender');
        $loyalty_level = $get('loyalty_level');
        $title = $get('title');
        $initials = $get('initials');
        // Common
        $client_cell = $get('client_cell');
        $client_tell = $get('client_tell');
        // Address fields
        $address_type_id = $get('address_type_id') ?: 1;
        $address_line1 = $get('address_line1');
        $address_line2 = $get('address_line2');
        $city = $get('city');
        $suburb = $get('suburb');
        $province = $get('province');
        $postal_code = $get('postal_code');
        $country = $get('country');
        $is_primary = nullIfEmptyBool($get('is_primary'));
        // Contact fields
        $contact_type_id = $get('contact_type_id') ?: 1;
        $contact_first_name = $get('contact_first_name') ?: $first_name;
        $contact_last_name = $get('contact_last_name') ?: $last_name;
        $contact_position = $get('contact_position');
        $contact_email = $get('contact_email') ?: $client_email;
        $contact_phone = $get('contact_phone') ?: $client_tell;
        $contact_cell = $get('contact_cell') ?: $client_cell;
        $contact_is_primary = nullIfEmptyBool($get('contact_is_primary'));

        // Validation
        if (!$client_type || !$client_email || !$client_status) {
            $errors[] = [
                'row' => $i,
                'client_type' => $client_type,
                'client_email' => $client_email,
                'client_status' => $client_status,
                'reason' => 'Missing required client fields'
            ];
            continue;
        }
        if (($client_type === 'company' || $client_type === 'business') && !$client_name) {
            $errors[] = [
                'row' => $i,
                'client_type' => $client_type,
                'reason' => 'Missing company name'
            ];
            continue;
        }
        if ($client_type === 'private' && (!$first_name || !$last_name)) {
            $errors[] = [
                'row' => $i,
                'client_type' => $client_type,
                'reason' => 'Missing customer first or last name'
            ];
            continue;
        }
        try {
            $conn->beginTransaction();
            // 1. Insert client
            $clientFields = [
                'client_type' => $client_type,
                'client_email' => $client_email,
                'client_cell' => $client_cell,
                'client_tell' => $client_tell,
                'client_status' => $client_status
            ];
            if ($client_type === 'company' || $client_type === 'business') {
                $clientFields['client_name'] = $client_name;
                $clientFields['vat_number'] = $vat_number;
                $clientFields['registration_number'] = $registration_number;
                $clientFields['website'] = $website;
                $clientFields['industry'] = $industry;
            } else {
                $clientFields['client_name'] = $first_name . ' ' . $last_name;
                $clientFields['first_name'] = $first_name;
                $clientFields['last_name'] = $last_name;
                $clientFields['dob'] = $dob;
                $clientFields['gender'] = $gender;
                $clientFields['loyalty_level'] = $loyalty_level;
                $clientFields['title'] = $title;
                $clientFields['initials'] = $initials;
            }
            $fields = array_keys($clientFields);
            $placeholders = array_map(function($f) { return ':' . $f; }, $fields);
            $sql = "INSERT INTO invoicing.clients (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            foreach ($clientFields as $k => $v) {
                $stmt->bindValue(':' . $k, $v);
            }
            $stmt->execute();
            $client_id = $conn->lastInsertId();

            // 2. Insert address
            $address_id = null;
            if ($address_line1 && $city && $country) {
                $addressFields = [
                    'address_type_id' => $address_type_id,
                    'address_line1' => $address_line1,
                    'address_line2' => $address_line2,
                    'city' => $city,
                    'suburb' => $suburb,
                    'province' => $province,
                    'postal_code' => $postal_code,
                    'country' => $country,
                    'is_primary' => $is_primary
                ];
                $afields = array_keys($addressFields);
                $aplaceholders = array_map(function($f) { return ':' . $f; }, $afields);
                $asql = "INSERT INTO invoicing.address (" . implode(',', $afields) . ") VALUES (" . implode(',', $aplaceholders) . ")";
                $astmt = $conn->prepare($asql);
                foreach ($addressFields as $k => $v) {
                    $astmt->bindValue(':' . $k, $v);
                }
                $astmt->execute();
                $address_id = $conn->lastInsertId();
            }

            // 3. Insert contact
            $contact_id = null;
            if ($contact_first_name && $contact_last_name && $contact_email) {
                $contactFields = [
                    'contact_type_id' => $contact_type_id,
                    'first_name' => $contact_first_name,
                    'last_name' => $contact_last_name,
                    'position' => $contact_position,
                    'email' => $contact_email,
                    'phone' => $contact_phone,
                    'cell' => $contact_cell,
                    'is_primary' => $contact_is_primary
                ];
                $cfields = array_keys($contactFields);
                $cplaceholders = array_map(function($f) { return ':' . $f; }, $cfields);
                $csql = "INSERT INTO invoicing.contact_person (" . implode(',', $cfields) . ") VALUES (" . implode(',', $cplaceholders) . ")";
                $cstmt = $conn->prepare($csql);
                foreach ($contactFields as $k => $v) {
                    $cstmt->bindValue(':' . $k, $v);
                }
                $cstmt->execute();
                $contact_id = $conn->lastInsertId();
            }

            // 4. Link client to address
            if ($client_id && $address_id) {
                $linkAddr = $conn->prepare("INSERT INTO invoicing.client_addresses (client_id, address_id) VALUES (?, ?)");
                $linkAddr->execute([$client_id, $address_id]);
            }

            // 5. Link client to contact
            if ($client_id && $contact_id) {
                $linkCont = $conn->prepare("INSERT INTO invoicing.client_contacts (client_id, contact_id) VALUES (?, ?)");
                $linkCont->execute([$client_id, $contact_id]);
            }

            $conn->commit();
            $successCount++;
        } catch (PDOException $e) {
            $conn->rollBack();
            $reason = $e->getMessage();
            if (strpos($reason, 'duplicate key value violates unique constraint') !== false) {
                $reason = 'Duplicate value for a unique field.';
            } else {
                $reason = preg_replace('/SQLSTATE\[[^]]*\]: [^:]*: [0-9]+ ERROR:  /', '', $reason);
                $reason = strtok($reason, "\n");
            }
            $errors[] = [
                'row' => $i,
                'client_type' => $client_type,
                'client_email' => $client_email,
                'reason' => $reason
            ];
        }
    }
    return [
        'success' => $successCount > 0,
        'message' => $successCount > 0 ? "Imported $successCount clients" . ($errors ? " with some errors" : "") : "No clients were imported",
        'errors' => $errors,
        'successCount' => $successCount,
        'totalRows' => count($sheetData) - 1
    ];
}

// Helper to get or create a lookup value and return its ID
function getOrCreateId($conn, $table, $field, $value, $idField, $extra = []) {
    if (!$value) return null;
    $where = "$field = ?";
    $params = [$value];
    foreach ($extra as $k => $v) {
        $where .= " AND $k = ?";
        $params[] = $v;
    }
    $sql = "SELECT $idField FROM $table WHERE $where LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $id = $stmt->fetchColumn();
    if ($id) return $id;
    // Insert if not found
    $fields = array_merge([$field], array_keys($extra));
    $placeholders = implode(',', array_fill(0, count($fields), '?'));
    $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES ($placeholders) RETURNING $idField";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array_merge([$value], array_values($extra)));
    return $stmt->fetchColumn();
} 