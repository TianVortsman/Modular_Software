<?php
// Enable error reporting for debugging but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header right at the start
header('Content-Type: application/json');

try {
    session_start();
    include('main-db.php');

    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . pg_last_error());
    }

    // Get query parameters with defaults
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Build the query
    $query = "
        SELECT 
            c.customer_id,
            c.company_name,
            c.email,
            c.account_number,
            c.status,
            c.last_login,
            c.created_at,
            COUNT(u.id) as total_users,
            COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_users
        FROM customers c
        LEFT JOIN users u ON u.customer_id = c.customer_id
    ";

    $params = array();
    $paramCount = 1;

    if (!empty($searchTerm)) {
        $query .= " WHERE (c.company_name ILIKE $" . $paramCount . 
                 " OR c.email ILIKE $" . $paramCount . 
                 " OR c.account_number ILIKE $" . $paramCount . ")";
        $params[] = "%" . $searchTerm . "%";
    }

    $query .= " GROUP BY c.customer_id, c.company_name, c.email, c.account_number, c.status, c.last_login, c.created_at
                ORDER BY c.created_at DESC
                LIMIT $limit OFFSET $offset";

    // Execute the query
    $result = !empty($params) ? 
        pg_query_params($conn, $query, $params) :
        pg_query($conn, $query);

    if (!$result) {
        throw new Exception("Failed to fetch customers: " . pg_last_error($conn));
    }

    // Fetch all customers
    $customers = array();
    while ($row = pg_fetch_assoc($result)) {
        $customers[] = array(
            'id' => (int)$row['customer_id'],
            'company_name' => $row['company_name'],
            'email' => $row['email'],
            'account_number' => $row['account_number'],
            'status' => $row['status'],
            'last_login' => $row['last_login'],
            'created_at' => $row['created_at'],
            'total_users' => (int)$row['total_users'],
            'active_users' => (int)$row['active_users']
        );
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'customers' => $customers
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("get-customers.php error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'sql_error' => isset($conn) ? pg_last_error($conn) : null
    ]);
}

// Close the database connection if it exists
if (isset($conn)) {
    pg_close($conn);
}
?> 