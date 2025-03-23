<?php
namespace App\Controllers;

require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Services/DatabaseService.php';

use App\Services\AuthService;
use App\Services\DatabaseService;

/**
 * Auth Controller
 * 
 * Controller for user authentication operations
 */
class AuthController
{
    private $authService;
    private $dbService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authService = AuthService::getAuthenticator();
        $this->dbService = DatabaseService::getMainDatabase();
    }
    
    /**
     * Handle login requests
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    public function login($email, $password)
    {
        return $this->authService->login($email, $password);
    }
    
    /**
     * Handle logout requests
     * 
     * @return array Logout result
     */
    public function logout()
    {
        return $this->authService->logout();
    }
    
    /**
     * Handle password reset requests
     * 
     * @param string $email User email
     * @return array Password reset result
     */
    public function initiatePasswordReset($email)
    {
        return $this->authService->initiatePasswordReset($email);
    }
    
    /**
     * Handle OTP verification and password reset
     * 
     * @param string $otp One-time password
     * @param string $newPassword New password
     * @param string $confirmPassword Confirm password
     * @return array Password reset result
     */
    public function verifyOtpAndResetPassword($otp, $newPassword, $confirmPassword)
    {
        return $this->authService->verifyOtpAndResetPassword($otp, $newPassword, $confirmPassword);
    }
    
    /**
     * Get a user by email
     * 
     * @param string $email User email
     * @return array|null User data or null if not found
     */
    public function getUserByEmail($email)
    {
        try {
            $db = $this->dbService;
            $sql = "SELECT * FROM users WHERE email = $1";
            $stmtName = "get_user_by_email_" . time() . rand(1000, 9999);
            $result = $db->executeQuery($stmtName, $sql, array($email));
            
            if ($result && $db->numRows($result) == 1) {
                return $db->fetchRow($result);
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return null;
        }
    }
} 