<?php
namespace App\Core\Database;

require_once __DIR__ . '/Database.php';

use App\Config\Database as DatabaseConfig;

/**
 * Main Database
 * 
 * Handles connections to the main system database
 * 
 * Note: This class has been updated to handle connection failures gracefully.
 * It now suppresses connection errors with @ operator, attempts fallback connections,
 * and ensures methods like executeQuery() handle null connections properly.
 * This prevents fatal errors when the database server is temporarily unavailable.
 */
class MainDatabase extends Database
{
    private static $instance = null;
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->config = DatabaseConfig::getMainConfig();
    }
    
    /**
     * Get instance of the database (Singleton pattern)
     * 
     * @return MainDatabase Instance
     */
    public static function getInstance(): MainDatabase
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Connect to the database
     * 
     * @return resource|null PostgreSQL connection or null on failure
     */
    public function connect()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        try {
            // Create a connection string with main host
            $conn_string = "host={$this->config['host']} " .
                          "port={$this->config['port']} " .
                          "dbname={$this->config['dbname']} " .
                          "user={$this->config['username']} " .
                          "password={$this->config['password']} " .
                          "connect_timeout={$this->config['connect_timeout']} " .
                          "sslmode={$this->config['sslmode']} " .
                          "{$this->config['options']}";
            
            // Establish a connection to PostgreSQL
            $this->connection = @pg_connect($conn_string);
            
            // If connection failed and we have a fallback host, try that
            if (!$this->connection && !empty($this->config['fallback_host'])) {
                error_log("Primary connection failed, trying fallback host: {$this->config['fallback_host']}");
                
                // Create a connection string with fallback host
                $fallback_conn_string = "host={$this->config['fallback_host']} " .
                                       "port={$this->config['port']} " .
                                       "dbname={$this->config['dbname']} " .
                                       "user={$this->config['username']} " .
                                       "password={$this->config['password']} " .
                                       "connect_timeout={$this->config['connect_timeout']} " .
                                       "sslmode={$this->config['sslmode']} " .
                                       "{$this->config['options']}";
                
                $this->connection = @pg_connect($fallback_conn_string);
            }
            
            // Check if the connection succeeded
            if (!$this->connection) {
                throw new \Exception('Database connection failed: Unable to connect to PostgreSQL server.');
            }
            
            return $this->connection;
        } catch (\Exception $e) {
            // Log the error
            error_log('Database connection error: ' . $e->getMessage());
            
            // Return null instead of throwing exception to allow graceful handling
            $this->connection = null;
            return null;
        }
    }
    
    /**
     * Disconnect from the database
     * 
     * @return void
     */
    public function disconnect()
    {
        if ($this->connection !== null) {
            pg_close($this->connection);
            $this->connection = null;
        }
    }
    
    /**
     * Execute a prepared statement query
     * 
     * @param string $name Name of the prepared statement
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return resource|false Query result
     */
    public function executeQuery(string $name, string $query, array $params = [])
    {
        $conn = $this->getConnection();
        
        // Check if connection is valid before attempting to use it
        if (!$conn) {
            error_log("Cannot execute query: No valid database connection");
            return false;
        }
        
        // Prepare the query
        $result = pg_prepare($conn, $name, $query);
        if (!$result) {
            throw new \Exception('Failed to prepare query: ' . pg_last_error($conn));
        }
        
        // Execute the query
        return pg_execute($conn, $name, $params);
    }
    
    /**
     * Fetch all rows from a result
     * 
     * @param \PgSql\Result|resource $result Query result
     * @return array|false Rows or false on failure
     */
    public function fetchAll($result)
    {
        return pg_fetch_all($result);
    }
    
    /**
     * Fetch a single row from a result
     * 
     * @param \PgSql\Result|resource $result Query result
     * @return array|false Row or false on failure
     */
    public function fetchRow($result)
    {
        return pg_fetch_assoc($result);
    }
    
    /**
     * Get the number of rows in a result
     * 
     * @param \PgSql\Result|resource $result Query result
     * @return int Number of rows
     */
    public function numRows($result): int
    {
        return pg_num_rows($result);
    }
    
    /**
     * Get the last error message
     * 
     * @return string Error message
     */
    public function getLastError(): string
    {
        if ($this->connection) {
            return pg_last_error($this->connection);
        }
        return "No valid PostgreSQL connection";
    }
} 