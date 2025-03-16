<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    session_start();
    include('../php/main-db.php');

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
    if (!$customerId) {
        throw new Exception("No customer ID provided");
    }

    $query = "
        SELECT 
            id,
            name,
            email,
            role,
            status,
            last_login,
            created_at
        FROM users 
        WHERE customer_id = $1 
        ORDER BY name ASC
    ";

    $result = pg_query_params($conn, $query, array($customerId));
    if (!$result) {
        throw new Exception("Failed to fetch users: " . pg_last_error($conn));
    }

    $users = array();
    while ($row = pg_fetch_assoc($result)) {
        $users[] = array(
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'status' => $row['status'],
            'last_login' => $row['last_login'],
            'created_at' => $row['created_at']
        );
    }

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    pg_close($conn);
}
?> 