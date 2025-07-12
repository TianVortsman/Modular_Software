<?php
// Database connection
include('main-db.php'); // Ensure this file contains the correct DB connection

// Change these variables to customize the technician's details
$email = "darryl@deason.io"; // Technician's email
$password = "Deason87"; // Technician's password
$name = "Darryl"; // Technician's name
$role = 1; // Technician's role (1, 2, or 3 based on your role definition)

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL statement to insert the technician
$sql = "INSERT INTO technicians (email, password, name, role, created_at) VALUES ($1, $2, $3, $4, NOW())";
$result = pg_prepare($conn, "insert_technician", $sql);

// Execute the statement
$result = pg_execute($conn, "insert_technician", array($email, $hashedPassword, $name, $role));

if ($result) {
    echo "Technician added successfully!";
} else {
    echo "Error adding technician: " . pg_last_error($conn);
}

// Close the connection
pg_close($conn);
?>
