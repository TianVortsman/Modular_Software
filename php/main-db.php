<?php
$db_host = 'localhost';   // Database host (XAMPP or other setup)
$db_port = '5432';        // Default PostgreSQL port
$db_user = 'Tian';    // Replace with your PostgreSQL username
$db_pass = 'Modul@rdev@2024'; // Replace with your PostgreSQL password
$db_name = 'modular_system';

// Create a connection string
$conn_string = "host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass";

// Establish a connection to PostgreSQL
$conn = pg_connect($conn_string);

// if ($conn){
// echo "Connected to the PostgreSQL database successfully!!!!";
// }
// Check if the connection succeeded
if (!$conn) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . pg_last_error()
    ]));
}