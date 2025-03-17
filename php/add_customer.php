<?php
require_once('main-db.php');
require_once('port_management.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

try {
    pg_query($conn, "BEGIN");

    // Get form data
    $companyName = pg_escape_string($_POST['company_name']);
    $email = pg_escape_string($_POST['email']);
    $accountNumber = pg_escape_string($_POST['account_number']);
    
    // Insert the new customer
    $clientDb = 'client_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $accountNumber));
    
    $result = pg_query_params($conn, 
        "INSERT INTO customers (company_name, email, account_number, status, client_db) 
         VALUES ($1, $2, $3, 'active', $4) 
         RETURNING customer_id",
        [$companyName, $email, $accountNumber, $clientDb]
    );

    if (!$result) {
        throw new Exception(pg_last_error($conn));
    }

    $row = pg_fetch_assoc($result);
    $customerId = (int)$row['customer_id'];

    // Assign a port to the new customer
    $port = assignPortToCustomer($conn, $customerId);

    pg_query($conn, "COMMIT");

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Customer added successfully',
        'customer_id' => $customerId,
        'assigned_port' => $port
    ]);

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");
    error_log("Error adding customer: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error adding customer: ' . $e->getMessage()
    ]);
} 