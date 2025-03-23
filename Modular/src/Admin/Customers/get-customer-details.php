<?php
// Enable error reporting for debugging but don't display errors
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display errors to prevent HTML output

// Set JSON header right at the start
header('Content-Type: application/json');

try {
    session_start();
    include('../php/main-db.php'); // Global database connection

    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . pg_last_error());
    }

    // Get and validate customer ID
    $customerId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    if (!$customerId) {
        throw new Exception("No customer ID provided");
    }

    // Get basic customer information
    $customerSql = "
        SELECT 
            customer_id,
            company_name,
            email,
            account_number,
            client_db,
            status,
            last_login,
            created_at
        FROM customers 
        WHERE customer_id = $1
    ";

    $result = pg_query_params($conn, $customerSql, array($customerId));
    if (!$result) {
        throw new Exception("Failed to fetch customer details: " . pg_last_error($conn));
    }

    $customerData = pg_fetch_assoc($result);
    if (!$customerData) {
        throw new Exception("Customer not found");
    }

    // Get user statistics directly from users table
    $userStatsSql = "
        SELECT 
            COUNT(*) as total_users,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
            COUNT(CASE WHEN last_login >= NOW() - INTERVAL '30 days' THEN 1 END) as active_last_30_days
        FROM users 
        WHERE customer_id = $1
    ";
    
    $userStatsResult = pg_query_params($conn, $userStatsSql, array($customerId));
    if (!$userStatsResult) {
        throw new Exception("Failed to fetch user statistics: " . pg_last_error($conn));
    }

    $userStats = pg_fetch_assoc($userStatsResult);

    // Get recent user activity
    $recentActivitySql = "
        SELECT 
            u.name,
            u.email,
            u.role,
            u.last_login,
            u.status
        FROM users u
        WHERE u.customer_id = $1
        ORDER BY u.last_login DESC NULLS LAST
        LIMIT 5
    ";

    $recentActivityResult = pg_query_params($conn, $recentActivitySql, array($customerId));
    if (!$recentActivityResult) {
        throw new Exception("Failed to fetch recent activity: " . pg_last_error($conn));
    }

    $recentActivity = array();
    while ($activity = pg_fetch_assoc($recentActivityResult)) {
        $recentActivity[] = array(
            'name' => $activity['name'],
            'email' => $activity['email'],
            'role' => $activity['role'],
            'last_login' => $activity['last_login'],
            'status' => $activity['status']
        );
    }

    // Get account number statistics
    $accountNumbersSql = "
        SELECT COUNT(DISTINCT an.account_number) as total_account_numbers
        FROM users u
        LEFT JOIN account_number an ON u.id = an.user_id
        WHERE u.customer_id = $1
    ";

    $accountNumbersResult = pg_query_params($conn, $accountNumbersSql, array($customerId));
    if (!$accountNumbersResult) {
        throw new Exception("Failed to fetch account numbers statistics: " . pg_last_error($conn));
    }

    $accountNumbersStats = pg_fetch_assoc($accountNumbersResult);

    // Prepare the response data
    $response = array(
        'success' => true,
        'customer' => array(
            'id' => (int)$customerData['customer_id'],
            'company_name' => $customerData['company_name'],
            'email' => $customerData['email'],
            'account_number' => $customerData['account_number'],
            'client_db' => $customerData['client_db'],
            'status' => $customerData['status'],
            'last_login' => $customerData['last_login'],
            'created_at' => $customerData['created_at'],
            'statistics' => array(
                'total_users' => (int)$userStats['total_users'],
                'active_users' => (int)$userStats['active_users'],
                'active_last_30_days' => (int)$userStats['active_last_30_days'],
                'total_account_numbers' => (int)$accountNumbersStats['total_account_numbers']
            ),
            'recent_activity' => $recentActivity
        )
    );

    echo json_encode($response);

} catch (Exception $e) {
    // Log the error
    error_log("get-customer-details.php error: " . $e->getMessage());
    
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