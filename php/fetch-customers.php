<?php
session_start();
include('../php/main-db.php'); // Global database connection

// Get the search term (if any)
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

// Get the page and limit parameters from the request
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build the SQL query to search customers
$searchSql = "SELECT COUNT(*) FROM customers WHERE company_name ILIKE '%$searchTerm%' OR email ILIKE '%$searchTerm%' OR account_number ILIKE '%$searchTerm%'";
$countResult = pg_query($conn, $searchSql);
$totalCustomers = pg_fetch_result($countResult, 0, 0);  // Get the total count

// Fetch customer data based on limit, offset, and search term
$sql = "SELECT company_name, email, account_number FROM customers WHERE company_name ILIKE '%$searchTerm%' OR email ILIKE '%$searchTerm%' OR account_number ILIKE '%$searchTerm%' LIMIT $limit OFFSET $offset";
$result = pg_query($conn, $sql);

// Check if the query was successful
if (!$result) {
    die(json_encode(["error" => pg_last_error($conn)]));
}

// Fetch all customer data
$customers = [];
while ($row = pg_fetch_assoc($result)) {
    $customers[] = $row;
}

// Return the customer data and the total count as a JSON response
echo json_encode([
    'totalCustomers' => $totalCustomers,
    'customers' => $customers
]);
?>