<?php
session_start();
include('main-db.php'); // Ensure $conn is a valid resource from pg_connect

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    try {
        // Query to check if the email exists in the technicians table
        $query_tech = "SELECT * FROM technicians WHERE email = $1";
        $result_tech = pg_prepare($conn, "check_tech_email", $query_tech);
        
        // Execute the prepared statement
        $result_tech = pg_execute($conn, "check_tech_email", array($email));
        
        if ($result_tech && pg_num_rows($result_tech) > 0) {
            // Email found in technicians table
            $tech = pg_fetch_assoc($result_tech);

            $_SESSION['tech_logged_in'] = true;
            $_SESSION['tech_email'] = $tech['email'];
            $_SESSION['userName'] = $tech['name'];

            // Generate a random 6-digit OTP
            $otp = rand(100000, 999999);

            // Store OTP in session for later use
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;

            // Log the OTP to a file for testing purposes
            file_put_contents('otp_log.txt', "OTP for $email: $otp\n", FILE_APPEND);

            // Redirect to OTP verification page
            header("Location: verify-otp.php");
            exit();
        } else {
            // Query to check if the email exists in the users table
            $query_user = "SELECT * FROM users WHERE email = $1";
            $result_user = pg_prepare($conn, "check_user_email", $query_user);

            // Execute the prepared statement
            $result_user = pg_execute($conn, "check_user_email", array($email));

            if ($result_user && pg_num_rows($result_user) > 0) {
                // Email found in users table
                $user = pg_fetch_assoc($result_user);

                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['userName'] = $user['name'];
                $_SESSION['account_number'] = $user['account_number'];

                // Generate a random 6-digit OTP
                $otp = rand(100000, 999999);

                // Store OTP in session for later use
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_email'] = $email;

                // Log the OTP to a file for testing purposes
                file_put_contents('otp_log.txt', "OTP for $email: $otp\n", FILE_APPEND);

                // Redirect to OTP verification page
                header("Location: verify-otp.php");
                exit();
            } else {
                echo "Email not found.";
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
