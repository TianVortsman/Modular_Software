<?php
namespace App\Services;

/**
 * Auth Service
 * 
 * Service for authentication operations
 */
class AuthService
{
    /**
     * Get an instance of the Authentication class
     * 
     * @return \App\Core\Auth\Authentication
     */
    public static function getAuthenticator()
    {
        require_once __DIR__ . '/../Core/Auth/Authentication.php';
        return new \App\Core\Auth\Authentication();
    }
} 