<?php
session_start();

// Check if the account number is set in the session
if (!isset($_SESSION['account_number'])) {
    die(json_encode(['error' => 'Account number not set. Unable to connect to the database.']));
}

$account_number = $_SESSION['account_number'];

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = $account_number;

require_once('../../../php/db.php');

try {
    // Initialize PDO connection
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $search = $_POST['search'] ?? '';
    
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone 
        FROM customers 
        WHERE (name ILIKE :search OR email ILIKE :search OR phone ILIKE :search)
        AND account_number = :account_number
        LIMIT 10
    ");
    
    $stmt->execute([
        ':search' => '%' . $search . '%',
        ':account_number' => $_SESSION['account_number']
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);

} catch (PDOException $e) {
    error_log("Error in searchCustomer.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
}
?>
