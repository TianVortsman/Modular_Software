<?php
namespace App\Config;

/**
 * Database Configuration
 * 
 * Provides configuration for different database connections
 */
class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Load configuration from environment or config file
        $this->host = getenv('DB_HOST') ?: 'postgres';
        $this->port = getenv('DB_PORT') ?: '5432';
        $this->db_name = getenv('DB_NAME') ?: 'postgres';
        $this->username = getenv('DB_USER') ?: 'postgres';
        $this->password = getenv('DB_PASSWORD') ?: 'postgres';
    }
    
    /**
     * Connect to the database
     * @return PDO database connection object
     */
    public function connect() {
        $this->conn = null;
        
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
            $this->conn = new \PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw $e;
        }
        
        return $this->conn;
    }
    
    /**
     * Get the database connection
     * @return PDO existing connection or new connection
     */
    public function getConnection() {
        if (!$this->conn) {
            return $this->connect();
        }
        return $this->conn;
    }
    
    /**
     * Switch to a different database
     * @param string $dbName New database name to connect to
     * @return PDO New database connection
     */
    public function switchDatabase($dbName) {
        // Close existing connection if open
        $this->conn = null;
        
        // Set the new database name
        $this->db_name = $dbName;
        
        // Connect to the new database
        return $this->connect();
    }

    /**
     * Get main database connection configuration
     * 
     * @return array Configuration for main database
     */
    public static function getMainConfig(): array
    {
        // Read from environment variables if available (Docker)
        $host = getenv('DB_HOST') ?: 'postgres';
        $username = getenv('DB_USER') ?: 'Tian';
        $password = getenv('DB_PASSWORD') ?: 'Modul@rdev@2024';
        
        return [
            'host' => $host,
            'port' => '5432',
            'dbname' => 'modular_system',
            'username' => $username,
            'password' => $password,
            // Add connection timeout and other options
            'connect_timeout' => 5,      // 5 seconds timeout
            'sslmode' => 'prefer',       // Prefer SSL if available
            'options' => '',             // Additional connection options
            'fallback_host' => 'postgres'  // Try this Docker service if primary host fails
        ];
    }
    
    /**
     * Get client database connection configuration
     * 
     * @param string $account_number Client account number
     * @return array Configuration for client database
     */
    public static function getClientConfig(string $account_number): array
    {
        // Debug logging
        error_log("[DatabaseConfig] getClientConfig called with account_number: " . $account_number);
        
        // Read from environment variables if available (Docker)
        $host = getenv('DB_HOST') ?: 'postgres';
        $username = getenv('DB_USER') ?: 'Tian';
        $password = getenv('DB_PASSWORD') ?: 'Modul@rdev@2024';
        
        $config = [
            'host' => $host,
            'port' => '5432',
            'dbname' => $account_number,
            'username' => $username,
            'password' => $password,
            // Add connection timeout and other options
            'connect_timeout' => 5,      // 5 seconds timeout
            'sslmode' => 'prefer',       // Prefer SSL if available
            'options' => '',             // Additional connection options
            'fallback_host' => 'postgres'  // Try this Docker service if primary host fails
        ];
        
        // Debug logging
        error_log("[DatabaseConfig] Client config created with dbname: " . $config['dbname']);
        
        return $config;
    }
} 