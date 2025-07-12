<?php
require_once('main-db.php');
require_once('port_management.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

try {
    $conn->beginTransaction();

    // Get form data
    $companyName = $_POST['company_name'];
    $email = $_POST['email'];
    $accountNumber = $_POST['account_number'];
    
    // Insert the new customer
    $clientDb = 'client_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $accountNumber));
    
    $stmt = $conn->prepare(
        "INSERT INTO customers (company_name, email, account_number, status, client_db) 
         VALUES (?, ?, ?, 'active', ?) 
         RETURNING customer_id"
    );
    $stmt->execute([$companyName, $email, $accountNumber, $clientDb]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $customerId = (int)$row['customer_id'];

    // Assign a port to the new customer
    $port = assignPortToCustomer($conn, $customerId);

    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Customer added successfully',
        'customer_id' => $customerId,
        'assigned_port' => $port
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error adding customer: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error adding customer: ' . $e->getMessage()
    ]);
} 