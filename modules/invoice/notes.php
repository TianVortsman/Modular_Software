<?php
require 'vendor/autoload.php'; // Ensure you have PhpSpreadsheet installed
use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
$conn = pg_connect("host=localhost dbname=your_db user=your_user password=your_pass");

if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}

// Check if file was uploaded
if (isset($_FILES['excel_file']) && is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    // Load the Excel workbook
    $spreadsheet = IOFactory::load($file);

    // Define expected sheet names and their corresponding database tables
    $sheets = [
        "products" => "product",
        "vehicles" => "vehicle",
        "extras"   => "extra"
    ];

    $errors = [];
    $processedSheets = [];

    // Loop through expected sheets
    foreach ($sheets as $sheetName => $tableName) {
        // Check if the sheet exists
        if (!$spreadsheet->sheetNameExists($sheetName)) {
            $errors[] = "Sheet '$sheetName' not found in the uploaded file.";
            continue;
        }

        $sheet = $spreadsheet->getSheetByName($sheetName);
        $sheetData = $sheet->toArray(null, true, true, true);

        // Skip header row
        $isFirstRow = true;
        $rowCount = 0;

        foreach ($sheetData as $row) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            // Ensure row is not empty
            if (empty(array_filter($row))) {
                continue;
            }

            // Validate and sanitize data based on the sheet type
            if ($sheetName === "products") {
                list($prod_id, $prod_name, $prod_descr, $prod_price, $prod_stock, $rowError) = validateProductRow($row);
            } elseif ($sheetName === "vehicles") {
                list($veh_id, $veh_make, $veh_model, $veh_year, $veh_price, $rowError) = validateVehicleRow($row);
            } elseif ($sheetName === "extras") {
                list($extra_id, $extra_name, $extra_price, $rowError) = validateExtraRow($row);
            }

            if (!empty($rowError)) {
                $errors[] = "Error in '$sheetName' on row " . ($rowCount + 2) . ": " . implode(", ", $rowError);
                continue;
            }

            // Prepare and execute the INSERT statement
            if ($sheetName === "products") {
                $query = "INSERT INTO product (prod_id, prod_name, prod_descr, prod_price, prod_stock) VALUES ($1, $2, $3, $4, $5)";
                $params = [$prod_id, $prod_name, $prod_descr, $prod_price, $prod_stock];
            } elseif ($sheetName === "vehicles") {
                $query = "INSERT INTO vehicle (veh_id, veh_make, veh_model, veh_year, veh_price) VALUES ($1, $2, $3, $4, $5)";
                $params = [$veh_id, $veh_make, $veh_model, $veh_year, $veh_price];
            } elseif ($sheetName === "extras") {
                $query = "INSERT INTO extra (extra_id, extra_name, extra_price) VALUES ($1, $2, $3)";
                $params = [$extra_id, $extra_name, $extra_price];
            }

            $result = pg_query_params($conn, $query, $params);

            if (!$result) {
                $errors[] = "Failed to insert row in '$sheetName' on row " . ($rowCount + 2) . ": " . pg_last_error($conn);
            } else {
                $processedSheets[$sheetName] = true;
            }

            $rowCount++;
        }

        if ($rowCount === 0) {
            $errors[] = "Sheet '$sheetName' is empty. No data inserted.";
        }
    }

    // Final output
    if (!empty($errors)) {
        echo "Errors found:<br>" . implode("<br>", $errors);
    } else {
        echo "Data successfully imported for sheets: " . implode(", ", array_keys($processedSheets));
    }
}

/**
 * Validate product row data
 */
function validateProductRow($row) {
    $errors = [];
    $prod_id = filter_var($row['A'], FILTER_VALIDATE_INT);
    $prod_name = trim($row['B']);
    $prod_descr = trim($row['C']);
    $prod_price = filter_var($row['D'], FILTER_VALIDATE_FLOAT);
    $prod_stock = filter_var($row['E'], FILTER_VALIDATE_INT);

    if ($prod_id === false) $errors[] = "Invalid product ID";
    if (empty($prod_name)) $errors[] = "Product name cannot be empty";
    if ($prod_price === false) $errors[] = "Invalid product price";
    if ($prod_stock === false) $errors[] = "Invalid stock quantity";

    return [$prod_id, $prod_name, $prod_descr, $prod_price, $prod_stock, $errors];
}

/**
 * Validate vehicle row data
 */
function validateVehicleRow($row) {
    $errors = [];
    $veh_id = filter_var($row['A'], FILTER_VALIDATE_INT);
    $veh_make = trim($row['B']);
    $veh_model = trim($row['C']);
    $veh_year = filter_var($row['D'], FILTER_VALIDATE_INT);
    $veh_price = filter_var($row['E'], FILTER_VALIDATE_FLOAT);

    if ($veh_id === false) $errors[] = "Invalid vehicle ID";
    if (empty($veh_make)) $errors[] = "Vehicle make cannot be empty";
    if (empty($veh_model)) $errors[] = "Vehicle model cannot be empty";
    if ($veh_year === false || $veh_year < 1900 || $veh_year > date("Y")) $errors[] = "Invalid vehicle year";
    if ($veh_price === false) $errors[] = "Invalid vehicle price";

    return [$veh_id, $veh_make, $veh_model, $veh_year, $veh_price, $errors];
}

/**
 * Validate extra row data
 */
function validateExtraRow($row) {
    $errors = [];
    $extra_id = filter_var($row['A'], FILTER_VALIDATE_INT);
    $extra_name = trim($row['B']);
    $extra_price = filter_var($row['C'], FILTER_VALIDATE_FLOAT);

    if ($extra_id === false) $errors[] = "Invalid extra ID";
    if (empty($extra_name)) $errors[] = "Extra name cannot be empty";
    if ($extra_price === false) $errors[] = "Invalid extra price";

    return [$extra_id, $extra_name, $extra_price, $errors];
}
?>
