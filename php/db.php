<?php
// Check if the account number is set in the session
if (!isset($_SESSION['account_number'])) {
    die("Account number not set. Unable to connect to the database.");
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

// Create a DSN (Data Source Name) string
$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";

try {
    // Establish a connection to PostgreSQL using PDO
    $conn = new PDO($dsn, $db_user, $db_pass);

    // Set the application-specific settings
    $conn->exec("SET app.username = '{$userName}'");
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // For a regular user
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
    } else {
        // Handle case where user session variables are not set
        die("User session variables are missing.");
    }
} elseif (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
    // For a technician
    if (isset($_SESSION['tech_id']) && isset($_SESSION['tech_name'])) {
        $tech_id = $_SESSION['tech_id'];
        $tech_name = $_SESSION['tech_name'];
    } else {
        // Handle case where technician session variables are not set
        die("Technician session variables are missing.");
    }
} else {
    // If neither user nor technician is logged in
    die("Please log in to continue.");
}
?>
