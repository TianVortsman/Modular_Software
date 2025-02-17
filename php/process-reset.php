<?php
session_start();
include('main-db.php'); // Ensure $conn is a valid resource from pg_connect

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    if ($new_password === $confirm_password) {
        $email = $_SESSION['email'];

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Prepare the SQL query to update the password
        $query = "UPDATE users SET password = $1 WHERE email = $2";  // Using placeholders for binding

        // Prepare the query with pg_prepare
        $result = pg_prepare($conn, "update_password", $query);

        // Bind the parameters and execute the query
        if ($result) {
            $update_result = pg_execute($conn, "update_password", array($hashed_password, $email));

            if ($update_result) {
                // Clear session data after password reset
                unset($_SESSION['otp']);
                unset($_SESSION['email']);
                
                // Success message
                echo "Password reset successful. <a href='index.php'>Sign In</a>";
            } else {
                echo "Failed to reset the password.";
            }
        } else {
            echo "Error preparing the statement.";
        }
    } else {
        echo "Passwords do not match.";
    }
}
?>
