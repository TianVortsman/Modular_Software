<?php
session_start();
include('main-db.php'); // Ensure $conn is a valid resource from pg_connect

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp_entered = $_POST['otp']; // The OTP entered by the user
    $new_user_password = $_POST['new-password']; // The new password
    $confirm_password = $_POST['confirm-password']; // The confirmed password

    // Check if the entered OTP matches the one stored in session
    if ($otp_entered == $_SESSION['otp']) {
        // Check if the new password and confirmation match
        if ($new_user_password === $confirm_password) {
            // Get the email from session
            $user_email = $_SESSION['otp_email']; 

            // Hash the new password
            $hashed_user_password = password_hash($new_user_password, PASSWORD_DEFAULT); // Hash the password

            // Debugging: Print the raw and hashed passwords
            echo "Raw Password: " . htmlspecialchars($new_user_password) . "<br>";
            echo "Hashed Password: " . htmlspecialchars($hashed_user_password) . "<br>";

            // Log the raw and hashed passwords to the text file
            $log_file = 'otp_log.txt';
            $log_entry = "Raw Password: " . $new_user_password . "\nHashed Password: " . $hashed_user_password . "\n";
            file_put_contents($log_file, $log_entry, FILE_APPEND); // Append to the log file

            // Determine the table to update based on the session variable
            if (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
                $table = 'technicians';
            } else {
                $table = 'users';
            }

            try {
                // Prepare the SQL statement to update the password
                $query = "UPDATE $table SET password = $1 WHERE email = $2";
                $result = pg_prepare($conn, "update_password", $query);

                // Execute the prepared statement
                $result = pg_execute($conn, "update_password", array($hashed_user_password, $user_email));

                // Check if the query executed successfully
                if ($result) {
                    echo "Password updated successfully!";

                    // Fetch the new hashed password from the database for verification
                    // Prepare the query to get the updated password
                    $query = "SELECT password FROM $table WHERE email = $1";
                    $result = pg_prepare($conn, "select_password", $query);
                    $result = pg_execute($conn, "select_password", array($user_email));

                    if ($result && pg_num_rows($result) > 0) {
                        // Fetch the new password from the result
                        $row = pg_fetch_assoc($result);

                        // Log the newly hashed password from the database
                        $log_entry = "New Hashed Password from DB: " . $row['password'] . "\n";
                        file_put_contents($log_file, $log_entry, FILE_APPEND); // Append to the log file
                    }

                    session_destroy(); // Destroy the session to log the user out
                    header("Location: ../index.php"); // Redirect to login page
                    exit();
                } else {
                    echo "Failed to update password: " . pg_last_error($conn); // Display the error message
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage(); // Error handling
            }
        } else {
            echo "Passwords do not match. Please try again."; // Password mismatch error
        }
    } else {
        echo "Invalid OTP. Please try again."; // OTP validation error
    }
}
?>
