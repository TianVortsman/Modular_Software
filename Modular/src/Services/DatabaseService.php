<?php
namespace App\Services;

require_once __DIR__ . '/../Core/Database/MainDatabase.php';
require_once __DIR__ . '/../Core/Database/ClientDatabase.php';

use App\Core\Database\MainDatabase;
use App\Core\Database\ClientDatabase;

/**
 * Database Service
 * 
 * Service for database operations
 */
class DatabaseService
{
    /**
     * Get the main database connection
     * 
     * @return MainDatabase Main database connection
     */
    public static function getMainDatabase(): MainDatabase
    {
        return MainDatabase::getInstance();
    }
    
    /**
     * Get a client database connection
     * 
     * @param string $accountNumber Client account number
     * @param string $userName Username for session tracking
     * @return ClientDatabase Client database connection
     */
    public static function getClientDatabase(string $accountNumber, string $userName = 'Guest'): ClientDatabase
    {
        return ClientDatabase::getInstance($accountNumber, $userName);
    }
    
    /**
     * Get the appropriate database based on current session
     * 
     * @return MainDatabase|ClientDatabase Database connection
     */
    public static function getCurrentDatabase()
    {
        if (isset($_SESSION['account_number'])) {
            $accountNumber = $_SESSION['account_number'];
            $userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
            return self::getClientDatabase($accountNumber, $userName);
        }
        
        return self::getMainDatabase();
    }
    
    /**
     * Test the main database connection
     * 
     * @return bool True if the connection is working, false otherwise
     */
    public static function testMainDatabaseConnection(): bool
    {
        $db = self::getMainDatabase();
        return $db->testConnection();
    }
    
    /**
     * Test a client database connection
     * 
     * @param string $accountNumber Client account number
     * @param string $userName Username for session tracking
     * @return bool True if the connection is working, false otherwise
     */
    public static function testClientDatabaseConnection(string $accountNumber, string $userName = 'Guest'): bool
    {
        $db = self::getClientDatabase($accountNumber, $userName);
        return $db->testConnection();
    }
}