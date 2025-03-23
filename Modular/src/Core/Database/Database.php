<?php
namespace App\Core\Database;

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database as DatabaseConfig;

/**
 * Base Database Class
 * 
 * Provides basic database functionality
 */
abstract class Database
{
    protected $connection = null;
    protected $config = [];
    
    /**
     * Connect to the database
     * 
     * @return mixed Database connection
     */
    abstract public function connect();
    
    /**
     * Disconnect from the database
     * 
     * @return void
     */
    abstract public function disconnect();
    
    /**
     * Check if connected to the database
     * 
     * @return bool True if connected, false otherwise
     */
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }
    
    /**
     * Get the database connection
     * 
     * @return mixed Database connection
     */
    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Test the database connection
     * 
     * This method safely tests if the database connection is working without throwing
     * exceptions. It's useful for:
     * 1. Diagnostic scripts like db_test.php
     * 2. Graceful handling of connection issues in the application
     * 3. Validating connection parameters before executing queries
     * 
     * @return bool True if the connection is working, false otherwise
     */
    public function testConnection(): bool
    {
        try {
            // Try to connect if not already connected
            if (!$this->isConnected()) {
                $this->connect();
            }
            
            // Check if the connection was successful
            if (!$this->isConnected()) {
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return mixed Query result
     */
    public function query(string $query, array $params = [])
    {
        $conn = $this->getConnection();
        
        // Check if connection is valid before attempting to use it
        if (!$conn) {
            error_log("Cannot execute query: No valid database connection");
            return false;
        }
        
        // Replace any named parameters with positional parameters
        // PostgreSQL uses $1, $2, etc. instead of :param_name
        $modifiedQuery = $query;
        if (strpos($query, ':') !== false) {
            $paramMap = [];
            $index = 1;
            
            // Extract named parameters and create a mapping
            preg_match_all('/:([a-zA-Z0-9_]+)/', $query, $matches);
            foreach ($matches[1] as $paramName) {
                if (!isset($paramMap[$paramName])) {
                    $paramMap[$paramName] = $index++;
                }
            }
            
            // Replace named parameters with positional parameters
            foreach ($paramMap as $name => $position) {
                $modifiedQuery = str_replace(":$name", "\$$position", $modifiedQuery);
            }
            
            // Reorder parameters to match positional order
            $reorderedParams = [];
            foreach ($matches[1] as $paramName) {
                if (isset($params[$paramName])) {
                    $position = $paramMap[$paramName];
                    $reorderedParams[$position] = $params[$paramName];
                }
            }
            
            // Sort by key to ensure correct order
            ksort($reorderedParams);
            $params = array_values($reorderedParams);
        }
        
        // Generate a unique statement name
        $statementName = 'stmt_' . md5($modifiedQuery . microtime());
        
        // Prepare the query
        $prepareResult = pg_prepare($conn, $statementName, $modifiedQuery);
        if (!$prepareResult) {
            error_log('Failed to prepare query: ' . pg_last_error($conn));
            return false;
        }
        
        // Execute the query
        return pg_execute($conn, $statementName, array_values($params));
    }
} 