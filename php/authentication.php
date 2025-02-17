<?php
session_start();
include('main-db.php'); // Database connection

// Function to log messages
function log_message($message) {
    $logFile = 'login_log.txt'; // Path to the log file
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'])) {
    // Get form inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    log_message("Login attempt for email: $email");

    // Check if the user is a technician
    $sql = "SELECT * FROM technicians WHERE email = $1";
    $result = pg_prepare($conn, "check_tech", $sql);
    $result = pg_execute($conn, "check_tech", array($email));

    if ($result) {
        log_message("Technician check query executed successfully.");

        if (pg_num_rows($result) == 1) {
            $techUser = pg_fetch_assoc($result);
            unset($_SESSION['account_number']);
            
            // Verify the password
            if (password_verify($password, $techUser['password'])) {
                // Technician login success
                $_SESSION['user_logged_in'] = false;
                $_SESSION['tech_logged_in'] = true;
                $_SESSION['tech_email'] = $techUser['email'];
                $_SESSION['tech_name'] = $techUser['name']; // Store technician's name
                $_SESSION['tech_id'] = $techUser['id']; // Store technician's id

                log_message("Technician login successful: " . $techUser['email']);
                //log_message("");
                
                // Redirect to the admin panel
                header("Location: ../main/techlogin.php");
                exit();
            } else {
                log_message("Invalid password for technician: $email");
                echo "Invalid password for technician.";
                exit();
            }
        } else {
            log_message("No technician found with email: $email");
        }
    } else {
        log_message("Technician check query failed: " . pg_last_error($conn));
    }

// Check if the user is a customer
$sql = "SELECT * FROM users WHERE email = $1";
$result = pg_prepare($conn, "check_user", $sql);
$result = pg_execute($conn, "check_user", array($email));

if ($result) {
    log_message("Customer check query executed successfully.");

    if (pg_num_rows($result) == 1) {
        unset($_SESSION['tech_logged_in']);
        $_SESSION['user_logged_in'] = true;
        $user = pg_fetch_assoc($result);

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // User login success
            $userId = $user['id']; // Fetch user_id instead of account_number_id

            // Fetch all account numbers for the user
            $accountSql = "SELECT account_number, id FROM account_number WHERE user_id = $1";
            $accountResult = pg_prepare($conn, "get_user_accounts", $accountSql);
            $accountResult = pg_execute($conn, "get_user_accounts", array($userId));

            if ($accountResult) {
                log_message("Account number query executed successfully.");

                // Check if multiple account numbers are associated with the user
                if (pg_num_rows($accountResult) > 1) {
                    // More than one account, trigger the modal
                    $accounts = pg_fetch_all($accountResult); // Fetch all account numbers
                    $_SESSION['multiple_accounts'] = $accounts; // Store account options in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    header("Location: ../main/choose-account.php"); // Redirect to choose-account.php
                                        
                    log_message("Multiple accounts found for user: " . $user['email']);
                } elseif (pg_num_rows($accountResult) == 1) {
                    // If only one account number, set it in session directly
                    $account = pg_fetch_assoc($accountResult);
                    $_SESSION['tech_logged_in'] = false;
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['account_number'] = $account['account_number'];
                    $_SESSION['user_id'] = $user['id']; // Set user_id for regular user

                    log_message("User login successful: " . $user['email']);
                    include('db.php'); // Database connection
                    
                    // Redirect to the dashboard
                    header("Location: ../main/dashboard.php");
                    exit();
                } else {
                    log_message("Account not found for user: $email");
                    echo "Account not found.";
                    exit();
                }
            } else {
                log_message("Account number query failed: " . pg_last_error($conn));
            }
        } else {
            log_message("Invalid password for user: $email");
            echo "Invalid password.";
            exit();
        }
    } else {
        log_message("No customer found with email: $email");
    }
} else {
    log_message("Customer check query failed: " . pg_last_error($conn));
}



// If the code reaches here, something went wrong
log_message("Login failed for email: $email");
echo "Invalid email or password.";

} else {
    log_message("Login form was not submitted correctly.");
    echo "Invalid form submission.";
}
?>
