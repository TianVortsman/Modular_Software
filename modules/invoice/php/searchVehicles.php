<?php
session_start();

// Check if the account number is set in the session
if (!isset($_SESSION['account_number'])) {
    die(json_encode(['error' => 'Account number not set. Unable to connect to the database.']));
}

$account_number = $_SESSION['account_number']; // Retrieve account number from session

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = $account_number; // Use the account-specific database

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check the connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Check if the search query is passed
$search_term = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// SQL query to fetch the required data (vehicle_name, description, unit_price, tax_percentage, vin_number)
$sql = "SELECT vehicle_name, description, unit_price, tax_percentage, vin_number
        FROM vehicles 
        WHERE vehicle_name LIKE '%$search_term%' 
        OR description LIKE '%$search_term%' 
        LIMIT 5";

$result = $conn->query($sql);

// Initialize an array to store the vehicle data
$vehicles = [];

// Check if there are results
if ($result->num_rows > 0) {
    // Fetch the results and store them in the $vehicles array
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = [
            'vehicle_name' => $row['vehicle_name'],
            'description' => $row['description'],
            'unit_price' => $row['unit_price'],
            'tax_percentage' => $row['tax_percentage'],
            'vin_number' => $row['vin_number'], // Include VIN number
        ];
    }

    // Return the data as JSON
    echo json_encode($vehicles);
} else {
    // If no results, return an error message
    echo json_encode(['error' => 'No vehicles found']);
}

// Close the database connection
$conn->close();
?>
