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
        WHERE customer_id = ? 
        ORDER BY name ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$customerId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as &$row) {
        $row['id'] = (int)$row['id'];
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