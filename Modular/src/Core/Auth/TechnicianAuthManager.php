<?php
namespace App\Core\Auth;

use App\Services\DatabaseService;

/**
 * TechnicianAuthManager
 * 
 * Handles technician authentication to customer accounts
 */
class TechnicianAuthManager
{
    private $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = DatabaseService::getMainDatabase();
    }
    
    /**
     * Login a technician to a customer account
     * 
     * @param int $technicianId The ID of the technician
     * @param string $accountNumber The account number of the customer
     * @return array Response with success status and redirect URL or error message
     */
    public function loginToCustomerAccount($technicianId, $accountNumber)
    {
        try {
            // Verify account number exists
            $query = "SELECT customer_id, company_name, status FROM customers WHERE account_number = $1";
            $result = $this->db->query($query, [$accountNumber]);
            
            if (!$result) {
                throw new \Exception("Database error: " . $this->db->getLastError());
            }
            
            $customer = $this->db->fetchRow($result);
            
            if (!$customer) {
                throw new \Exception("Customer account not found");
            }
            
            if ($customer['status'] !== 'active') {
                throw new \Exception("Cannot login to inactive customer account");
            }
            
            // Verify technician has permission to access this account
            // This would normally check a permissions table, but for now we'll assume all techs have access
            
            // Generate a secure access token
            $token = bin2hex(random_bytes(32));
            $expiration = time() + 3600; // 1 hour
            
            // Store the token in the database or session
            $_SESSION['tech_access_token'] = $token;
            $_SESSION['tech_account_number'] = $accountNumber;
            $_SESSION['tech_id'] = $technicianId;
            $_SESSION['tech_access_expiration'] = $expiration;
            $_SESSION['tech_logged_in'] = true;
            
            // Also set the new format session variables if they don't exist
            if (!isset($_SESSION['user_type'])) {
                $_SESSION['user_type'] = 'technician';
            }
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['user_id'] = $technicianId;
            }
            
            // Log the access for audit purposes
            $this->logAccess($technicianId, $accountNumber, $token);
            
            return [
                'success' => true,
                'redirect' => "/public/views/dashboard.php?token=" . $token
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify an access token for a customer account
     * 
     * @param string $token The access token
     * @return bool True if the token is valid
     */
    public function verifyAccessToken($token)
    {
        // Check if token is in session and not expired
        if (!isset($_SESSION['tech_access_token']) || 
            $_SESSION['tech_access_token'] !== $token ||
            !isset($_SESSION['tech_access_expiration']) ||
            $_SESSION['tech_access_expiration'] < time()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log technician access to customer account
     * 
     * @param int $technicianId The ID of the technician
     * @param string $accountNumber The account number of the customer
     * @param string $token The access token
     */
    private function logAccess($technicianId, $accountNumber, $token)
    {
        try {
            $query = "
                INSERT INTO technician_access_log (
                    technician_id, account_number, access_token, access_time, ip_address
                ) VALUES (
                    $1, $2, $3, NOW(), $4
                )
            ";
            
            $params = [
                $technicianId,
                $accountNumber,
                $token,
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ];
            
            $this->db->query($query, $params);
        } catch (\Exception $e) {
            // Just log the error but don't stop the process
            error_log("Failed to log technician access: " . $e->getMessage());
        }
    }
} 