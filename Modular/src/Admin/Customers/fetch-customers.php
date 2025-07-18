<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header right at the start
header('Content-Type: application/json');

try {
    session_start();
    include('../php/main-db.php'); // Global database connection

    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Get query parameters with defaults
    $searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $sortColumn = isset($_GET['sortColumn']) ? $_GET['sortColumn'] : 'company_name';
    $sortDirection = isset($_GET['sortDirection']) ? strtoupper($_GET['sortDirection']) : 'ASC';

    // Validate and sanitize inputs
    $limit = min(max($limit, 1), 100); // Limit between 1 and 100
    $page = max($page, 1);
    $offset = ($page - 1) * $limit;

    // Validate sort direction
    $sortDirection = in_array($sortDirection, ['ASC', 'DESC']) ? $sortDirection : 'ASC';

    // Validate sort column
    $allowedColumns = [
        'name' => 'c.company_name',
        'company' => 'c.company_name',
        'email' => 'c.email',
        'account_number' => 'c.account_number',
        'devices' => 'total_devices',
        'status' => 'c.status',
        'lastLogin' => 'c.last_login'
    ];

    // Default to company_name if invalid sort column
    $sortColumn = isset($allowedColumns[$sortColumn]) ? $allowedColumns[$sortColumn] : 'c.company_name';

    // Build the WHERE clause
    $whereConditions = [];
    $params = [];
    if (!empty($searchTerm)) {
        $whereConditions[] = "(c.company_name ILIKE ? OR c.email ILIKE ? OR c.account_number ILIKE ?)";
        $params = array_merge($params, array_fill(0, 3, "%$searchTerm%"));
    }
    if ($status !== 'all') {
        $whereConditions[] = "c.status = ?";
        $params[] = $status;
    }
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // First, check if the customers table exists
    $tableCheckSql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'customers'
    )";
    $tableCheckStmt = $conn->query($tableCheckSql);
    $tableExists = $tableCheckStmt->fetchColumn();
    if (!$tableExists) {
        throw new Exception("Customers table does not exist");
    }

    // Count total customers
    $countSql = "
        SELECT COUNT(*) 
        FROM customers c 
        $whereClause
    ";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalCustomers = $countStmt->fetchColumn();

    // Check if devices table exists before joining
    $devicesExistStmt = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'devices'
    )");
    $hasDevicesTable = $devicesExistStmt->fetchColumn();

    // Main query with all necessary fields
    $sql = "
        SELECT 
            c.customer_id,
            c.company_name,
            c.email,
            c.account_number,
            c.status,
            c.last_login" . 
            ($hasDevicesTable ? ",
            COALESCE(d.total_devices, 0) as total_devices,
            COALESCE(d.active_devices, 0) as active_devices" : ",
            0 as total_devices,
            0 as active_devices") . "
        FROM customers c " .
        ($hasDevicesTable ? "
        LEFT JOIN (
            SELECT 
                customer_id,
                COUNT(*) as total_devices,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_devices
            FROM devices 
            GROUP BY customer_id
        ) d ON d.customer_id = c.customer_id" : "") . "
        $whereClause
        ORDER BY $sortColumn $sortDirection
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $customers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format the data
        $customers[] = [
            'id' => (int)$row['customer_id'],
            'customer_name' => $row['company_name'],
            'company_name' => $row['company_name'],
            'email' => $row['email'],
            'account_number' => $row['account_number'],
            'total_devices' => (int)$row['total_devices'],
            'active_devices' => (int)$row['active_devices'],
            'status' => $row['status'] ?? 'unknown',
            'last_login' => $row['last_login']
        ];
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'totalCustomers' => (int)$totalCustomers,
        'page' => $page,
        'limit' => $limit,
        'totalPages' => ceil($totalCustomers / $limit),
        'customers' => $customers
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("fetch-customers.php error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Close the database connection if it exists
if (isset($conn)) {
    pg_close($conn);
}
?>