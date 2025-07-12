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
        throw new Exception("Database connection failed");
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
    if (!empty($searchTerm)) {
        $query .= " WHERE (c.company_name ILIKE ? OR c.email ILIKE ? OR c.account_number ILIKE ?)";
        $params = array_fill(0, 3, "%$searchTerm%");
    }

    $query .= " GROUP BY c.customer_id, c.company_name, c.email, c.account_number, c.status, c.last_login, c.created_at
                ORDER BY c.created_at DESC
                LIMIT $limit OFFSET $offset";

    // Execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    // Fetch all customers
    $customers = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
        'error' => $e->getMessage()
    ]);
}

// Close the database connection if it exists
if (isset($conn)) {
    $conn = null;
}
?> 