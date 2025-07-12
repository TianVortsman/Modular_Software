<?php
namespace App\Core\Database;

require_once __DIR__ . '/Database.php';

use App\Config\Database as DatabaseConfig;
use \PDO;
use \PDOException;

/**
 * Main Database (PDO version)
 * 
 * Handles connections to the main system database using PDO
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
     * Connect to the database using PDO
     * 
     * @return PDO|null PDO connection or null on failure
     */
    public function connect()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        try {
            // Create a DSN string with primary host
            $dsn = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['dbname']};";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => $this->config['connect_timeout'] ?? 5
            ];
            try {
                $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            } catch (PDOException $e) {
                if (!empty($this->config['fallback_host']) && strpos($e->getMessage(), 'connect') !== false) {
                    error_log("Primary connection failed, trying fallback host: {$this->config['fallback_host']}");
                    $fallback_dsn = "pgsql:host={$this->config['fallback_host']};port={$this->config['port']};dbname={$this->config['dbname']};";
                    $this->connection = new PDO($fallback_dsn, $this->config['username'], $this->config['password'], $options);
                } else {
                    throw $e;
                }
            }
            // Test the connection
            $test = $this->connection->query('SELECT 1');
            if (!$test) {
                throw new PDOException('Connection test failed');
            }
            return $this->connection;
        } catch (PDOException $e) {
            error_log("Main DB connection failed: " . $e->getMessage());
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
        $this->connection = null;
    }
    
    /**
     * Execute a prepared statement query using PDO
     *
     * @param string $name Name of the prepared statement (ignored for PDO, kept for compatibility)
     * @param string $query SQL query (can use $1, $2, ... or ? for positional params)
     * @param array $params Query parameters (positional)
     * @return \PDOStatement|false Query result
     */
    public function executeQuery(string $name, string $query, array $params = [])
    {
        $conn = $this->getConnection();
        if (!$conn) {
            error_log("Cannot execute query: No valid database connection");
            return false;
        }
        // Convert $1, $2, ... to ? for PDO if needed
        $pdoQuery = preg_replace('/\$[0-9]+/', '?', $query);
        try {
            $stmt = $conn->prepare($pdoQuery);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Failed to execute query: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fetch all rows from a PDOStatement
     *
     * @param \PDOStatement $result Query result
     * @return array|false Rows or false on failure
     */
    public function fetchAll($result)
    {
        if ($result instanceof \PDOStatement) {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    /**
     * Fetch a single row from a PDOStatement
     *
     * @param \PDOStatement $result Query result
     * @return array|false Row or false on failure
     */
    public function fetchRow($result)
    {
        if ($result instanceof \PDOStatement) {
            return $result->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    /**
     * Get the number of rows in a PDOStatement
     *
     * @param \PDOStatement $result Query result
     * @return int Number of rows
     */
    public function numRows($result): int
    {
        if ($result instanceof \PDOStatement) {
            return $result->rowCount();
        }
        return 0;
    }
    
    /**
     * Get the last error message
     *
     * @return string Error message
     */
    public function getLastError(): string
    {
        if ($this->connection instanceof \PDO) {
            $errorInfo = $this->connection->errorInfo();
            return $errorInfo[2] ?? 'Unknown PDO error';
        }
        return "No valid PDO connection";
    }
} 