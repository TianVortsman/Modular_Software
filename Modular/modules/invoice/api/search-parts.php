<?php
session_start();

require_once('../../../php/db.php');

try {
    // Initialize PDO connection
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $search = $_POST['search'] ?? '';
    
    $stmt = $pdo->prepare("
        SELECT id, part_number, description, price 
        FROM parts 
        WHERE (part_number ILIKE :search OR description ILIKE :search)
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
    error_log("Error in search-parts.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
}

// Check if the account number is set in the session
if (!isset($_SESSION['account_number'])) {
    die(json_encode(['error' => 'Account number not set. Unable to connect to the database.'])); 
}

// Get the account number from the session
$account_number = $_SESSION['account_number'];

// Define database connection parameters
$db_host = 'localhost'; // Localhost for XAMPP
$db_user = 'root';      // Default user in XAMPP
$db_pass = '';          // Default password in XAMPP
$db_name = $account_number; // Use the account number directly as the DB name

// Create a new database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check if the connection succeeded
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle the search request
if (isset($_GET['field']) && isset($_GET['query'])) {
    $field = $_GET['field']; // Either 'part-name' or 'description'
    $query = $_GET['query'];

    // Validate and sanitize the field parameter
    if (!in_array($field, ['part-name', 'description'], true)) {
        die(json_encode(['error' => 'Invalid search field']));
    }

    // Build the SQL query based on the field
    $sql = $field === 'part-name'
        ? "SELECT part_name, description, unit_price, tax_percentage FROM parts WHERE part_name LIKE ? LIMIT 5"
        : "SELECT part_name, description, unit_price, tax_percentage FROM parts WHERE description LIKE ? LIMIT 5";

    // Prepare and execute the query
    if ($stmt = $conn->prepare($sql)) {
        $searchTerm = "%" . $conn->real_escape_string($query) . "%"; // Sanitize query
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch and format results as JSON
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = [
                'part_name' => $row['part_name'] ?? '',
                'description' => $row['description'] ?? '',
                'unit_price' => number_format((float)$row['unit_price'], 2),
                'tax_percentage' => (float)$row['tax_percentage'] ?? 0 // Ensure it's a float
            ];
        }

        echo json_encode($results);
        $stmt->close();
    } else {
        // Return an error message if the query fails
        echo json_encode(['error' => 'Query preparation failed']);
    }
} else {
    // Return an error if the required parameters are missing
    echo json_encode(['error' => 'Invalid request. Missing parameters.']);
}
?>
