<?php
session_start();
require_once '../Database/ClientDatabase.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once 'import_functions.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

function handleError($message, $errors = [], $httpCode = 500) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors,
        'totalRows' => 0,
        'successCount' => 0
    ]);
    exit;
}

try {
    // Validate request
    if (!isset($_FILES['file']) || !isset($_POST['module']) || !isset($_POST['type'])) {
        handleError('Missing required parameters', [], 400);
    }

    $file = $_FILES['file'];
    $module = $_POST['module'];
    $type = $_POST['type'];
    $account_number = $_POST['account_number'] ?? null;

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        handleError('File upload failed: ' . ($errorMessages[$file['error']] ?? 'Unknown error'));
    }

    // Validate file type
    $allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel'
    ];
    if (!in_array($file['type'], $allowedTypes)) {
        handleError('Invalid file type. Please upload an Excel file (.xlsx or .xls)', [], 400);
    }

    // Get the database connection using ClientDatabase
    $userName = $_SESSION['user_name'] ?? 'Guest';
    $dbInstance = \App\Core\Database\ClientDatabase::getInstance($account_number, $userName);
    $conn = $dbInstance->connect();

    // Load the Excel file
    try {
        $spreadsheet = IOFactory::load($file['tmp_name']);
        
        // Add sheet validation and debugging
        $worksheet = $spreadsheet->getSheetByName($type);
        if (!$worksheet) {
            error_log("Sheet '$type' not found in workbook. Available sheets: " . implode(", ", $spreadsheet->getSheetNames()));
            handleError("Sheet '$type' not found in the workbook. Please ensure your file contains a sheet named '$type'.");
        }
        
        // Get data and validate
        $data = $worksheet->toArray();
        $totalRows = count($data);
        
        if ($totalRows <= 1) {
            return [
                'success' => false,
                'message' => 'The uploaded file contains no data rows',
                'errors' => [],
                'totalRows' => 0,
                'successCount' => 0
            ];
        }
        
        // Debug log the first few rows
        error_log("First row (headers): " . print_r($data[0], true));
        if (isset($data[1])) {
            error_log("Second row (first data row): " . print_r($data[1], true));
        }
        
        // Process data based on module and type
        switch ($module) {
            case 'timeandatt':
                switch ($type) {
                    case 'Employees':
                        $result = importTimeAndAttEmployees($conn, $data);
                        echo json_encode([
                            'success' => $result['success'],
                            'message' => $result['message'],
                            'errors' => $result['errors'],
                            'totalRows' => count($data) - 1, // Subtract header row
                            'successCount' => $result['successCount'] ?? 0
                        ]);
                        exit;
                    
                    case 'Time Entries':
                        $result = importTimeEntries($conn, $data);
                        echo json_encode([
                            'success' => !empty($result),
                            'message' => empty($result) ? 'Time entries imported successfully' : 'Import completed with errors',
                            'errors' => $result,
                            'totalRows' => count($data) - 1,
                            'successCount' => 0
                        ]);
                        exit;
                    
                    case 'Shifts':
                        $result = importShifts($conn, $data);
                        echo json_encode([
                            'success' => !empty($result),
                            'message' => empty($result) ? 'Shifts imported successfully' : 'Import completed with errors',
                            'errors' => $result,
                            'totalRows' => count($data) - 1,
                            'successCount' => 0
                        ]);
                        exit;
                }
                break;

            case 'mobile':
                switch ($type) {
                    case 'Mobile Users':
                        $result = importMobileUsers($conn, $data);
                        echo json_encode([
                            'success' => $result['success'],
                            'message' => $result['message'],
                            'errors' => $result['errors'],
                            'totalRows' => count($data),
                            'successCount' => $result['successCount'] ?? 0
                        ]);
                        exit;
                    
                    case 'App Settings':
                        $result = importAppSettings($conn, $data);
                        echo json_encode([
                            'success' => $result['success'],
                            'message' => $result['message'],
                            'errors' => $result['errors'],
                            'totalRows' => count($data),
                            'successCount' => $result['successCount'] ?? 0
                        ]);
                        exit;
                }
                break;

            case 'payroll':
                if (strpos($type, 'Salary Data') !== false) {
                    $errors = importSalaryData($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Salary data imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Tax Information') !== false) {
                    $errors = importTaxInfo($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Tax information imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'access':
                if (strpos($type, 'Access Permissions') !== false) {
                    $errors = importAccessPermissions($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Access permissions imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'User Groups') !== false) {
                    $errors = importUserGroups($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'User groups imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'asset':
                if (strpos($type, 'Assets') !== false) {
                    $errors = importAssets($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Assets imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Maintenance Records') !== false) {
                    $errors = importMaintenanceRecords($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Maintenance records imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'fleet':
                if (strpos($type, 'Vehicles') !== false) {
                    $errors = importVehicles($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Vehicles imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Fuel Records') !== false) {
                    $errors = importFuelRecords($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Fuel records imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'support':
                if (strpos($type, 'Tickets') !== false) {
                    $errors = importTickets($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Tickets imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Categories') !== false) {
                    $errors = importCategories($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Categories imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'crm':
                if (strpos($type, 'Customers') !== false) {
                    $errors = importCustomers($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Customers imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Companies') !== false) {
                    $errors = importCompanies($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Companies imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'inventory':
                if (strpos($type, 'Products') !== false) {
                    $errors = importProducts($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Products imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Suppliers') !== false) {
                    $errors = importSuppliers($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Suppliers imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'project':
                if (strpos($type, 'Projects') !== false) {
                    $errors = importProjects($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Projects imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Tasks') !== false) {
                    $errors = importTasks($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Tasks imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'hr':
                if (strpos($type, 'Employees') !== false) {
                    $errors = importEmployees($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Employees imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Training Records') !== false) {
                    $errors = importTrainingRecords($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Training records imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            case 'invoice':
                if (strpos($type, 'Invoices') !== false) {
                    $errors = importInvoices($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Invoices imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                elseif (strpos($type, 'Products') !== false) {
                    $errors = importProducts($conn, $data);
                    echo json_encode([
                        'success' => empty($errors),
                        'message' => empty($errors) ? 'Products imported successfully' : 'Import completed with errors',
                        'errors' => $errors,
                        'totalRows' => count($data) - 1,
                        'successCount' => empty($errors) ? count($data) - 1 : 0
                    ]);
                    exit;
                }
                break;

            default:
                handleError('Invalid module or import type', [], 400);
        }
    } catch (Exception $e) {
        error_log("Excel processing error: " . $e->getMessage());
        handleError('Error processing Excel file: ' . $e->getMessage());
    }
} catch (Exception $e) {
    handleError('Critical error during import: ' . $e->getMessage());
} 