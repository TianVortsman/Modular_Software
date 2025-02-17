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

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle search query
if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']);
    $sql = "SELECT customer_name, address_line1, address_line2 FROM invoiceCustomers WHERE customer_name LIKE ? LIMIT 5";

    if ($stmt = $conn->prepare($sql)) {
        $searchTerm = "%" . $query . "%";
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = [
                'customer_name' => $row['customer_name'],
                'address_line_1' => $row['address_line1'], // Fixed to match JS
                'address_line_2' => $row['address_line2']  // Fixed to match JS
            ];
        }

        echo json_encode($customers);
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]); // Add detailed error
    }
} else {
    echo json_encode(['error' => 'Invalid request. Missing parameters.']);
}
?>
