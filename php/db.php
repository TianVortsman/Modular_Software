<?php
// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the account number is set in the session
if (!isset($_SESSION['account_number'])) {
    error_log('Account number not set in session');
    die(json_encode([
        'success' => false,
        'message' => 'Account number not set. Unable to connect to the database.'
    ]));
}

// Get the account number from the session
$account_number = $_SESSION['account_number'];
$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');

// Define database connection parameters
$db_host = 'localhost';
$db_port = '5432';
$db_user = 'Tian'; 
$db_pass = 'Modul@rdev@2024';
$db_name = $account_number;

// Log connection attempt
error_log("Attempting database connection to {$db_name} on {$db_host}:{$db_port}");

// Create a DSN (Data Source Name) string
$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";

try {
    // Establish a connection to PostgreSQL using PDO
    $conn = new PDO($dsn, $db_user, $db_pass);

    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set the application-specific settings
    $conn->exec("SET app.username = " . $conn->quote($userName));
    
    // Test the connection
    $test = $conn->query('SELECT 1');
    if (!$test) {
        throw new PDOException('Connection test failed');
    }
    
    error_log("Successfully connected to database {$db_name}");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    error_log("DSN: {$dsn}");
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}

// Verify user authentication
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // For a regular user
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        error_log("Authenticated as user: {$user_name}");
    } else {
        error_log('User session variables are missing');
        die(json_encode([
            'success' => false,
            'message' => 'User session variables are missing.'
        ]));
    }
} elseif (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
    // For a technician
    if (isset($_SESSION['tech_id']) && isset($_SESSION['tech_name'])) {
        $tech_id = $_SESSION['tech_id'];
        $tech_name = $_SESSION['tech_name'];
        error_log("Authenticated as technician: {$tech_name}");
    } else {
        error_log('Technician session variables are missing');
        die(json_encode([
            'success' => false,
            'message' => 'Technician session variables are missing.'
        ]));
    }
} else {
    error_log('No valid authentication found');
    die(json_encode([
        'success' => false,
        'message' => 'Please log in to continue.'
    ]));
}
?>
