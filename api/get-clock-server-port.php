<?php
session_start();
// Enable error reporting for debugging but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
include('../php/main-db.php');

// Check if account number is provided
if (!isset($_GET['account_number']) || empty($_GET['account_number'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Account number is required'
    ]);
    exit;
}

// Get account number and ensure it's treated as a string
$account_number = trim($_GET['account_number']);

try {
    // First verify if the account number exists - using case-insensitive comparison
    $check_query = "SELECT COUNT(*) FROM customers WHERE UPPER(account_number) = UPPER($1)";
    $check_result = pg_query_params($conn, $check_query, [$account_number]);
    if (!$check_result) {
        throw new Exception("Failed to check account existence: " . pg_last_error($conn));
    }
    
    $count = pg_fetch_result($check_result, 0, 0);
    if ($count == 0) {
        echo json_encode([
            'success' => false,
            'error' => "Account number '$account_number' not found in database",
            'debug_info' => [
                'account_number' => $account_number,
                'query' => $check_query,
                'count' => $count
            ]
        ]);
        exit;
    }

    // Get the actual column names from the table
    $columns_query = "SELECT column_name 
                     FROM information_schema.columns 
                     WHERE table_name = 'customers'";
    $columns_result = pg_query($conn, $columns_query);
    if (!$columns_result) {
        throw new Exception("Failed to get table columns: " . pg_last_error($conn));
    }

    $columns = [];
    while ($column_row = pg_fetch_assoc($columns_result)) {
        $columns[] = $column_row['column_name'];
    }

    // Now get the customer data with only existing columns - using case-insensitive comparison
    $query = "SELECT * FROM customers WHERE UPPER(account_number) = UPPER($1)";
    
    $result = pg_query_params($conn, $query, [$account_number]);
    if (!$result) {
        throw new Exception("Database query failed: " . pg_last_error($conn));
    }

    $row = pg_fetch_assoc($result);
    if (!$row) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch customer data',
            'debug_info' => [
                'account_number' => $account_number,
                'query' => $query,
                'available_columns' => $columns,
                'pg_last_error' => pg_last_error($conn)
            ]
        ]);
        exit;
    }

    // Debug: Log the actual data
    error_log("Debug - Row data for account $account_number: " . print_r($row, true));

    // Check for port in any column that might contain it
    $port = null;
    $port_column = null;
    
    // List of possible port column names
    $possible_port_columns = ['clock_server_port', 'port', 'clock_port', 'server_port'];
    
    foreach ($possible_port_columns as $column) {
        if (isset($row[$column]) && !empty($row[$column])) {
            $port = $row[$column];
            $port_column = $column;
            break;
        }
    }

    // If we found a port
    if ($port !== null) {
        echo json_encode([
            'success' => true,
            'port' => $port,
            'source' => 'customers',
            'debug_info' => [
                'account_number' => $account_number,
                'found_in_column' => $port_column,
                'available_columns' => $columns,
                'row_data' => $row
            ]
        ]);
        exit;
    }

    // If not found
    echo json_encode([
        'success' => false,
        'error' => 'No port found for this account number',
        'debug_info' => [
            'account_number' => $account_number,
            'available_columns' => $columns,
            'row_data' => $row
        ]
    ]);

} catch (Exception $e) {
    error_log("get-clock-server-port.php error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'account_number' => $account_number,
            'sql_error' => isset($conn) ? pg_last_error($conn) : null
        ]
    ]);
}

// Close the database connection if it exists
if (isset($conn)) {
    pg_close($conn);
}
?> 