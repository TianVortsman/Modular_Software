<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
session_start();

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method. Only POST is allowed.");
    }
    
    // Get the request body
    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody, true);
    
    // Validate input
    if (!isset($data['account_number']) || !isset($data['port'])) {
        throw new Exception("Account number and port are required");
    }
    
    $accountNumber = $data['account_number'];
    $port = intval($data['port']);
    
    // Validate port number
    if ($port < 1024 || $port > 65535) {
        throw new Exception("Invalid port number. Port must be between 1024 and 65535.");
    }
    
    // Include the main database connection
    require_once '../php/main-db.php';
    
    // Check if the connection was successful
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // First check if the port is already assigned to another account
    $checkSql = "SELECT account_number FROM account_numbers WHERE clock_server_port = $1 AND account_number != $2";
    $checkResult = pg_query_params($conn, $checkSql, array($port, $accountNumber));
    
    if (!$checkResult) {
        throw new Exception("Error checking database: " . pg_last_error($conn));
    }
    
    // If port is already in use, return an error
    if (pg_num_rows($checkResult) > 0) {
        $row = pg_fetch_assoc($checkResult);
        throw new Exception("Port {$port} is already assigned to account {$row['account_number']}");
    }
    
    // Update or insert the port in account_numbers table
    $updateSql = "
        UPDATE account_numbers 
        SET clock_server_port = $1 
        WHERE account_number = $2
    ";
    $updateResult = pg_query_params($conn, $updateSql, array($port, $accountNumber));
    
    if (!$updateResult) {
        throw new Exception("Error updating account_numbers table: " . pg_last_error($conn));
    }
    
    // If no rows were affected, the account number might not exist in account_numbers table
    // Try to update customers table as fallback
    if (pg_affected_rows($updateResult) === 0) {
        // Check if the account exists in customers table
        $checkCustomerSql = "SELECT customer_id FROM customers WHERE account_number = $1";
        $checkCustomerResult = pg_query_params($conn, $checkCustomerSql, array($accountNumber));
        
        if (!$checkCustomerResult) {
            throw new Exception("Error checking customers table: " . pg_last_error($conn));
        }
        
        if (pg_num_rows($checkCustomerResult) > 0) {
            // Account exists in customers, update the port
            $updateCustomerSql = "UPDATE customers SET clock_server_port = $1 WHERE account_number = $2";
            $updateCustomerResult = pg_query_params($conn, $updateCustomerSql, array($port, $accountNumber));
            
            if (!$updateCustomerResult) {
                throw new Exception("Error updating customers table: " . pg_last_error($conn));
            }
            
            if (pg_affected_rows($updateCustomerResult) === 0) {
                throw new Exception("Failed to update port. No records affected.");
            }
        } else {
            // Account doesn't exist in customers either
            throw new Exception("Account number not found");
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => "Port updated successfully"
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("update-clock-server-port.php error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Close the database connection
if (isset($conn)) {
    pg_close($conn);
}
?> 