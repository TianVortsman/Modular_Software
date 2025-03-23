<?php
namespace Models;

/**
 * User Model
 * 
 * Represents a user in the system
 */
class User
{
    private $id;
    private $name;
    private $email;
    private $accountNumbers = [];
    
    /**
     * Constructor
     * 
     * @param array $userData User data from database
     */
    public function __construct($userData = [])
    {
        if (!empty($userData)) {
            $this->id = $userData['id'] ?? null;
            $this->name = $userData['name'] ?? null;
            $this->email = $userData['email'] ?? null;
            
            // Account numbers would be populated separately
        }
    }
    
    /**
     * Get user ID
     * 
     * @return int User ID
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get user name
     * 
     * @return string User name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get user email
     * 
     * @return string User email
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Get account numbers
     * 
     * @return array Account numbers
     */
    public function getAccountNumbers()
    {
        return $this->accountNumbers;
    }
    
    /**
     * Set account numbers
     * 
     * @param array $accountNumbers Account numbers
     */
    public function setAccountNumbers($accountNumbers)
    {
        $this->accountNumbers = $accountNumbers;
    }
} 