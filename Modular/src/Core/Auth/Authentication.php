<?php
namespace App\Core\Auth;

require_once __DIR__ . '/../../Services/DatabaseService.php';

use App\Services\DatabaseService;

/**
 * Authentication Class
 * 
 * Handles user authentication including login, logout, password reset and OTP verification
 */
class Authentication
{
    private $db;
    private $logFile = 'login_log.txt';

    /**
     * Constructor
     * 
     * Initializes the database connection
     */
    public function __construct()
    {
        // Only start session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = DatabaseService::getMainDatabase();
        
        // Ensure the database is connected
        $connection = $this->db->connect();
        
        // If connection fails, log it but don't throw an exception
        // This allows the Authentication class to at least render login forms
        // even if the database is temporarily unavailable
        if (!$connection) {
            error_log("Warning: Database connection failed in Authentication constructor");
            $this->logMessage("Database connection failed");
        }
    }

    /**
     * Main entry point that handles all authentication requests
     * 
     * @return void
     */
    public static function handleRequest()
    {
        // Create instance of self
        $auth = new self();
        
        // Determine request type
        $requestType = $_GET['action'] ?? '';
        
        if ($requestType === 'logout') {
            // Handle logout request
            $result = $auth->logout();
            header("Location: " . $result['redirect']);
            exit();
        } else if ($requestType === 'password_reset') {
            // Handle password reset request
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
                $result = $auth->initiatePasswordReset($_POST['email']);
                if ($result['success']) {
                    header("Location: " . $result['redirect']);
                    exit();
                } else {
                    echo $result['message'];
                    exit();
                }
            } else {
                // Display password reset form
                $auth->renderPasswordResetForm();
                exit();
            }
        } else if ($requestType === 'verify_otp') {
            // Handle OTP verification request
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'], $_POST['new-password'], $_POST['confirm-password'])) {
                $result = $auth->verifyOtpAndResetPassword(
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
            } else {
                // Display OTP verification form
                $auth->renderOtpVerificationForm();
                exit();
            }
        } else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'])) {
            // Handle login request
            $result = $auth->login($_POST['email'], $_POST['password']);
            
            if ($result['success']) {
                header("Location: " . $result['redirect']);
                exit();
            } else {
                echo $result['message'];
                exit();
            }
        } else {
            echo "Invalid request";
            exit();
        }
    }

    /**
     * Log messages to a file for debugging
     * 
     * @param string $message Message to log
     * @return void
     */
    private function logMessage($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    /**
     * Login a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    public function login($email, $password)
    {
        if (empty($email) || empty($password)) {
            $this->logMessage("Login attempt with empty credentials");
            error_log("Login failed: Empty credentials");
            return [
                'success' => false,
                'message' => 'Email and password are required',
                'redirect' => null
            ];
        }

        $email = trim($email);
        $password = trim($password);
        
        error_log("Starting login process for email: $email");
        $this->logMessage("Login attempt for email: $email");
        
        // First try to authenticate as a technician
        $techResult = $this->authenticateTechnician($email, $password);
        error_log("Technician authentication result: " . ($techResult['success'] ? 'SUCCESS' : 'FAILED') . " - " . $techResult['message']);
        
        if ($techResult['success']) {
            return $techResult;
        }
        
        // If not a technician, try to authenticate as a regular user
        $userResult = $this->authenticateUser($email, $password);
        error_log("User authentication result: " . ($userResult['success'] ? 'SUCCESS' : 'FAILED') . " - " . $userResult['message']);
        
        if ($userResult['success']) {
            return $userResult;
        }

        // If neither technician nor customer authentication succeeded
        $this->logMessage("Login failed for email: $email");
        error_log("Login failed: Invalid credentials");
        return [
            'success' => false,
            'message' => 'Invalid email or password',
            'redirect' => null
        ];
    }

    /**
     * Authenticate a technician
     * 
     * @param string $email Technician email
     * @param string $password Technician password
     * @return array Authentication result
     */
    private function authenticateTechnician($email, $password)
    {
        try {
            error_log("Attempting technician authentication for: $email");
            
            $sql = "SELECT * FROM technicians WHERE email = $1";
            // Use a unique statement name with timestamp to avoid conflicts
            $stmtName = "check_tech_" . time() . rand(1000, 9999);
            
            error_log("Executing technician query with statement: $stmtName");
            $result = $this->db->executeQuery($stmtName, $sql, array($email));
            
            if ($result === false) {
                $error = $this->db->getLastError();
                $this->logMessage("Technician check query failed: " . $error);
                error_log("Technician query failed: " . $error);
                
                // Check if it's a connection issue
                if (stripos($error, 'connect') !== false || $this->db->getConnection() === null) {
                    return [
                        'success' => false,
                        'message' => 'Database connection issue. Please try again later.',
                        'redirect' => null
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Database error',
                    'redirect' => null
                ];
            }

            $this->logMessage("Technician check query executed successfully.");
            error_log("Technician query execution successful");

            if ($this->db->numRows($result) == 1) {
                $techUser = $this->db->fetchRow($result);
                error_log("Technician found. Checking password...");
                
                // Verify the password
                $verified = password_verify($password, $techUser['password']);
                error_log("Password verification result: " . ($verified ? "Success" : "Failed") . 
                          " (Hash starts with: " . substr($techUser['password'], 0, 10) . "...)");
                
                if ($verified) {
                    // Technician login success
                    $_SESSION['user_logged_in'] = false;
                    $_SESSION['tech_logged_in'] = true;
                    $_SESSION['tech_email'] = $techUser['email'];
                    $_SESSION['tech_name'] = $techUser['name'];
                    $_SESSION['tech_id'] = $techUser['id'];
                    
                    // Add new format session variables for compatibility with newer code
                    $_SESSION['user_type'] = 'technician';
                    $_SESSION['user_id'] = $techUser['id'];
                    
                    // Clear any old session data
                    if (isset($_SESSION['account_number'])) {
                        unset($_SESSION['account_number']);
                    }
                    if (isset($_SESSION['user_email'])) {
                        unset($_SESSION['user_email']);
                    }
                    if (isset($_SESSION['multiple_accounts'])) {
                        unset($_SESSION['multiple_accounts']);
                    }

                    $this->logMessage("Technician login successful: " . $techUser['email']);
                    error_log("Technician login successful - Session setup complete");
                    
                    return [
                        'success' => true,
                        'message' => 'Technician login successful',
                        'redirect' => '../../../public/admin/techlogin.php'
                    ];
                } else {
                    $this->logMessage("Invalid password for technician: $email");
                    error_log("Invalid password for technician: Password verification failed");
                    return [
                        'success' => false,
                        'message' => 'Invalid password for technician',
                        'redirect' => null
                    ];
                }
            } else {
                error_log("No technician found with email: $email");
            }
        } catch (\Exception $e) {
            $this->logMessage("Error in technician authentication: " . $e->getMessage());
            error_log("Exception in technician authentication: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error during authentication',
                'redirect' => null
            ];
        }

        return [
            'success' => false,
            'message' => 'Not a technician account',
            'redirect' => null
        ];
    }

    /**
     * Authenticate a regular user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    private function authenticateUser($email, $password)
    {
        try {
            error_log("Attempting user authentication for: $email");
            
            $sql = "SELECT * FROM users WHERE email = $1";
            // Use a unique statement name with timestamp to avoid conflicts
            $stmtName = "check_user_" . time() . rand(1000, 9999);
            
            error_log("Executing user query with statement: $stmtName");
            $result = $this->db->executeQuery($stmtName, $sql, array($email));

            if ($result === false) {
                $error = $this->db->getLastError();
                $this->logMessage("Customer check query failed: " . $error);
                error_log("User query failed: " . $error);
                
                // Check if it's a connection issue
                if (stripos($error, 'connect') !== false || $this->db->getConnection() === null) {
                    return [
                        'success' => false,
                        'message' => 'Database connection issue. Please try again later.',
                        'redirect' => null
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => 'Database error',
                    'redirect' => null
                ];
            }

            $this->logMessage("Customer check query executed successfully.");
            error_log("User query execution successful");

            if ($this->db->numRows($result) == 1) {
                $user = $this->db->fetchRow($result);
                error_log("User found. Checking password...");
                
                // Verify the password
                $verified = password_verify($password, $user['password']);
                error_log("Password verification result: " . ($verified ? "Success" : "Failed") . 
                          " (Hash starts with: " . substr($user['password'], 0, 10) . "...)");
                
                if ($verified) {
                    $userId = $user['id'];
                    error_log("User password verified. Getting account numbers.");

                    // Fetch all account numbers for the user
                    $accountSql = "SELECT account_number, id FROM account_number WHERE user_id = $1";
                    $accStmtName = "get_user_accounts_" . time() . rand(1000, 9999);
                    error_log("Executing account query with statement: $accStmtName");
                    $accountResult = $this->db->executeQuery($accStmtName, $accountSql, array($userId));

                    if ($accountResult === false) {
                        $this->logMessage("Account number query failed: " . $this->db->getLastError());
                        error_log("Account number query failed: " . $this->db->getLastError());
                        return [
                            'success' => false,
                            'message' => 'Database error when retrieving accounts',
                            'redirect' => null
                        ];
                    }
                    
                    $this->logMessage("Account number query executed successfully.");
                    error_log("Account number query successful. Found " . $this->db->numRows($accountResult) . " accounts");

                    // Check if multiple account numbers are associated with the user
                    if ($this->db->numRows($accountResult) > 1) {
                        // More than one account, trigger the account selection page
                        $accounts = $this->db->fetchAll($accountResult);
                        error_log("Multiple accounts found. Setting up session for account selection.");
                        
                        // Clear any tech session data
                        $_SESSION['tech_logged_in'] = false;
                        if (isset($_SESSION['tech_email'])) {
                            unset($_SESSION['tech_email']);
                        }
                        if (isset($_SESSION['tech_name'])) {
                            unset($_SESSION['tech_name']);
                        }
                        if (isset($_SESSION['tech_id'])) {
                            unset($_SESSION['tech_id']);
                        }
                        
                        // Set user session data
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['multiple_accounts'] = $accounts;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $email;
                        
                        $this->logMessage("Multiple accounts found for user: " . $user['email']);
                        error_log("User login successful - Multiple accounts - Session setup complete");
                        
                        return [
                            'success' => true,
                            'message' => 'Multiple accounts found',
                            'redirect' => '../../../public/account/choose-account.php'
                        ];
                    } elseif ($this->db->numRows($accountResult) == 1) {
                        // If only one account number, set it in session directly
                        $account = $this->db->fetchRow($accountResult);
                        error_log("Single account found. Setting up session with account: " . $account['account_number']);
                        
                        // Clear any tech session data
                        $_SESSION['tech_logged_in'] = false;
                        if (isset($_SESSION['tech_email'])) {
                            unset($_SESSION['tech_email']);
                        }
                        if (isset($_SESSION['tech_name'])) {
                            unset($_SESSION['tech_name']);
                        }
                        if (isset($_SESSION['tech_id'])) {
                            unset($_SESSION['tech_id']);
                        }
                        
                        // Set user session data
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['account_number'] = $account['account_number'];
                        $_SESSION['user_id'] = $user['id'];

                        $this->logMessage("User login successful: " . $user['email']);
                        error_log("User login successful - Single account - Session setup complete");
                        
                        return [
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => '../../../public/views/dashboard.php'
                        ];
                    } else {
                        $this->logMessage("Account not found for user: $email");
                        error_log("No accounts found for user with ID: " . $userId);
                        return [
                            'success' => false,
                            'message' => 'Account not found',
                            'redirect' => null
                        ];
                    }
                } else {
                    $this->logMessage("Invalid password for user: $email");
                    error_log("Invalid password for user: Password verification failed");
                    return [
                        'success' => false,
                        'message' => 'Invalid password',
                        'redirect' => null
                    ];
                }
            } else {
                error_log("No user found with email: $email");
            }
        } catch (\Exception $e) {
            $this->logMessage("Error in user authentication: " . $e->getMessage());
            error_log("Exception in user authentication: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error during authentication',
                'redirect' => null
            ];
        }

        return [
            'success' => false,
            'message' => 'User not found',
            'redirect' => null
        ];
    }

    /**
     * Handles user logout
     * 
     * @return array Logout result with status and redirect URL
     */
    public function logout()
    {
        // Clear all session data
        $_SESSION = array();
        
        // If session cookie exists, destroy it
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logout successful',
            'redirect' => '../../../public/index.php'
        ];
    }

    /**
     * Initiates the password reset process by checking email and sending OTP
     * 
     * @param string $email User email
     * @return array Result with status and redirect URL
     */
    public function initiatePasswordReset($email)
    {
        if (empty($email)) {
            return [
                'success' => false,
                'message' => 'Email is required',
                'redirect' => null
            ];
        }

        $email = trim($email);
        $this->logMessage("Password reset attempt for email: $email");

        try {
            // Check if the email exists in the technicians table
            $query_tech = "SELECT * FROM technicians WHERE email = $1";
            $techStmtName = "check_tech_reset_" . time() . rand(1000, 9999);
            $result_tech = $this->db->executeQuery($techStmtName, $query_tech, array($email));
            
            if ($result_tech && $this->db->numRows($result_tech) > 0) {
                // Email found in technicians table
                $tech = $this->db->fetchRow($result_tech);

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

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'redirect' => '?action=verify_otp'
                ];
            } else {
                // Check if the email exists in the users table
                $query_user = "SELECT * FROM users WHERE email = $1";
                $userStmtName = "check_user_reset_" . time() . rand(1000, 9999);
                $result_user = $this->db->executeQuery($userStmtName, $query_user, array($email));

                if ($result_user && $this->db->numRows($result_user) > 0) {
                    // Email found in users table
                    $user = $this->db->fetchRow($result_user);

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

                    return [
                        'success' => true,
                        'message' => 'OTP sent successfully',
                        'redirect' => '?action=verify_otp'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Email not found',
                        'redirect' => null
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logMessage("Error in password reset: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'redirect' => null
            ];
        }
    }

    /**
     * Verifies OTP and resets the password
     * 
     * @param string $otp One-time password
     * @param string $newPassword New password
     * @param string $confirmPassword Confirm password
     * @return array Result with status and redirect URL
     */
    public function verifyOtpAndResetPassword($otp, $newPassword, $confirmPassword)
    {
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email'])) {
            return [
                'success' => false,
                'message' => 'Password reset session expired',
                'redirect' => null
            ];
        }

        // Verify OTP
        if ($_SESSION['otp'] != $otp) {
            return [
                'success' => false,
                'message' => 'Invalid OTP',
                'redirect' => null
            ];
        }

        // Verify passwords match
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Passwords do not match',
                'redirect' => null
            ];
        }

        $email = $_SESSION['otp_email'];

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            if (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
                // Update technician password
                $query = "UPDATE technicians SET password = $1 WHERE email = $2";
                $updateTechStmtName = "update_tech_pwd_" . time() . rand(1000, 9999);
                $updateResult = $this->db->executeQuery($updateTechStmtName, $query, array($hashedPassword, $email));
            } else {
                // Update user password
                $query = "UPDATE users SET password = $1 WHERE email = $2";
                $updateUserStmtName = "update_user_pwd_" . time() . rand(1000, 9999);
                $updateResult = $this->db->executeQuery($updateUserStmtName, $query, array($hashedPassword, $email));
            }

            if (!$updateResult) {
                return [
                    'success' => false,
                    'message' => 'Failed to update password: ' . $this->db->getLastError(),
                    'redirect' => null
                ];
            }

            // Clear OTP session data
            unset($_SESSION['otp']);
            unset($_SESSION['otp_email']);

            // Set a success message in session
            $_SESSION['password_reset_success'] = true;
            
            // Redirect to login page
            return [
                'success' => true,
                'message' => 'Password reset successful',
                'redirect' => '../../../public/index.php'
            ];
        } catch (\Exception $e) {
            $this->logMessage("Error updating password: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'redirect' => null
            ];
        }
    }

    /**
     * Renders the password reset form
     */
    private function renderPasswordResetForm()
    {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <link rel="stylesheet" href="../../../public/assets/css/root.css">
            <link rel="stylesheet" href="../../../public/assets/css/passreset.css">
            <title>Password Reset</title>
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <img src="../../../public/assets/img/Logo.webp" alt="Logo" class="logo">
                    <h1>Password Reset</h1>
                    <form action="?action=password_reset" method="POST">
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                        </div>
                        <button type="submit" class="btn">Send OTP</button>
                    </form>
                    <div class="message">
                        <p>Remembered your password? <a href="../../../public/index.php" data-direction="backward">Sign In</a></p>
                    </div>
                </div>
            </div>
            <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
            <script src="../../../public/assets/js/page-transitions.js"></script>
        </body>
        </html>';
    }

    /**
     * Renders the OTP verification form
     */
    private function renderOtpVerificationForm()
    {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <link rel="stylesheet" href="../../../public/assets/css/root.css">
            <link rel="stylesheet" href="../../../public/assets/css/passreset.css">
            <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
            <title>Verify OTP</title>
        </head>
        <body>
            <div class="container">
                <div class="form-container">
                    <h1>Verify OTP</h1>
                    <form action="?action=verify_otp" method="POST">
                        <div class="input-group">
                            <label for="otp">Enter OTP:</label>
                            <input type="text" id="otp" name="otp" required>
                        </div>
                        <div class="input-group">
                            <label for="new-password">New Password:</label>
                            <input type="password" id="new-password" name="new-password" required>
                        </div>
                        <div class="input-group">
                            <label for="confirm-password">Confirm Password:</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>
                        </div>
                        <button type="submit" class="btn">Verify OTP</button>
                    </form>
                </div>
            </div>
        </body>
        </html>';
    }
}

// Entry point to handle direct requests to this file
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    Authentication::handleRequest();
} 