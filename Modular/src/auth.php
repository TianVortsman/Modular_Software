<?php
require_once __DIR__ . '/Controllers/AuthController.php';

use App\Controllers\AuthController;

try {
    // Initialize the auth controller
    $authController = new AuthController();
    
    // Determine the action
    $action = $_GET['action'] ?? '';
    
    // Execute the appropriate action
    if ($action === 'logout') {
        $result = $authController->logout();
        header("Location: " . $result['redirect']);
        exit();
    } else if ($action === 'password_reset') {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
            $result = $authController->initiatePasswordReset($_POST['email']);
            if ($result['success']) {
                header("Location: " . $result['redirect']);
                exit();
            } else {
                echo $result['message'];
                exit();
            }
        }
        
        // Display password reset form by redirecting
        header("Location: ../public/reset-password.php");
        exit();
    } else if ($action === 'verify_otp') {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'], $_POST['new-password'], $_POST['confirm-password'])) {
            $result = $authController->verifyOtpAndResetPassword(
                $_POST['otp'], 
                $_POST['new-password'], 
                $_POST['confirm-password']
            );
            if ($result['success']) {
                header("Location: " . $result['redirect']);
                exit();
            } else {
                echo $result['message'];
                exit();
            }
        }
        
        // Display OTP verification form by redirecting
        header("Location: ../public/verify-otp.php");
        exit();
    } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'])) {
        // Handle login request
        $email = trim($_POST['email']);
        
        // Log login attempt
        error_log("Login attempt from {$_SERVER['REMOTE_ADDR']} with email: " . $email);
        
        $result = $authController->login($email, $_POST['password']);
        
        if ($result['success']) {
            error_log("Login successful for $email. Redirecting to: " . $result['redirect']);
            header("Location: " . $result['redirect']);
            exit();
        } else {
            error_log("Login failed for $email. Reason: " . $result['message']);
            
            // Redirect back to login page with specific error
            if (stripos($result['message'], 'Database connection') !== false) {
                header("Location: ../public/index.php?error=db_connection");
            } else {
                header("Location: ../public/index.php?error=auth");
            }
            exit();
        }
    } else {
        // Show the login form if no action is specified
        header("Location: ../public/index.php");
        exit();
    }
} catch (\Exception $e) {
    // Log the error
    error_log("Critical error in auth.php: " . $e->getMessage());
    
    // Display an error message
    echo "System is currently unavailable. Please try again later.";
    exit();
} 