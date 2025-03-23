<?php
namespace App\UI;

/**
 * SidebarManager Class
 * 
 * Manages sidebar information including user details and account information
 */
class SidebarManager
{
    private $userName;
    private $accountNumber;
    private $isLoggedIn;
    private $isTechnician;
    private $hasMultipleAccounts;
    
    /**
     * Constructor
     * Initializes the sidebar with session data
     */
    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize properties
        $this->initialize();
    }
    
    /**
     * Initialize sidebar properties from session
     */
    private function initialize()
    {
        // Check if user is logged in as technician
        $this->isTechnician = isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true;
        
        // Check if user is logged in as regular user
        $this->isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
        
        // Check if user has multiple accounts
        $this->hasMultipleAccounts = isset($_SESSION['multiple_accounts']) && is_array($_SESSION['multiple_accounts']) && count($_SESSION['multiple_accounts']) > 0;
        
        // Set account number
        if (isset($_SESSION['account_number']) && !empty($_SESSION['account_number'])) {
            $this->accountNumber = $_SESSION['account_number'];
        } elseif (isset($_SESSION['tech_account_number']) && !empty($_SESSION['tech_account_number'])) {
            $this->accountNumber = $_SESSION['tech_account_number'];
        } else {
            $this->accountNumber = "N/A";
        }
        
        // Set user name
        if ($this->isTechnician && isset($_SESSION['tech_name'])) {
            $this->userName = $_SESSION['tech_name'];
        } elseif (isset($_SESSION['user_name'])) {
            $this->userName = $_SESSION['user_name'];
        } elseif (isset($_SESSION['tech_email'])) {
            $this->userName = $_SESSION['tech_email'];
        } elseif (isset($_SESSION['user_email'])) {
            $this->userName = $_SESSION['user_email'];
        } else {
            $this->userName = "Guest";
        }
    }
    
    /**
     * Get the current user's name for display
     * 
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
    
    /**
     * Get the current account number for display
     * 
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
    
    /**
     * Check if the user has multiple accounts
     * 
     * @return bool
     */
    public function hasMultipleAccounts()
    {
        return $this->hasMultipleAccounts;
    }
    
    /**
     * Check if logged in as a technician
     * 
     * @return bool
     */
    public function isTechnician()
    {
        return $this->isTechnician;
    }
    
    /**
     * Check if logged in as a regular user
     * 
     * @return bool
     */
    public function isUser()
    {
        return $this->isLoggedIn;
    }
    
    /**
     * Get the notification count
     * 
     * @return int
     */
    public function getNotificationCount()
    {
        // This would typically come from a database
        // For now, just return 0
        return 0;
    }
    
    /**
     * Get the user role for display
     * 
     * @return string
     */
    public function getUserRole()
    {
        if ($this->isTechnician) {
            return "Technician";
        } elseif ($this->isLoggedIn) {
            return "User";
        } else {
            return "Guest";
        }
    }
    
    /**
     * Generate the appropriate logout URL based on user type
     * 
     * @return string The logout URL
     */
    public function getLogoutUrl()
    {
        if ($this->isTechnician) {
            // Technicians go back to techlogin.php
            return "/public/admin/techlogin.php";
        } else {
            // Regular users fully log out
            return "/src/auth.php?action=logout";
        }
    }
    
    /**
     * Get the account click behavior
     * Returns JS function to call or empty string if no action
     * 
     * @return string JavaScript function call or empty string
     */
    public function getAccountClickBehavior()
    {
        if ($this->isLoggedIn && $this->hasMultipleAccounts) {
            // Only users with multiple accounts should have a click behavior
            return "window.location.href='/public/account/choose-account.php'";
        }
        
        // For technicians and users with single account, no action
        return "";
    }
    
    /**
     * Check if the account number should be clickable
     * 
     * @return bool True if the account number should be clickable
     */
    public function isAccountClickable()
    {
        return $this->isLoggedIn && $this->hasMultipleAccounts;
    }
    
    /**
     * Get CSS class for account number based on click behavior
     * 
     * @return string CSS class string
     */
    public function getAccountCssClass()
    {
        return $this->isAccountClickable() ? "clickable-account" : "";
    }
}
?> 