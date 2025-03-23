<?php
namespace App\Core\Database;

require_once __DIR__ . '/Database.php';

use App\Config\Database as DatabaseConfig;
use \PDO;
use \PDOException;

/**
 * Client Database
 * 
 * Handles connections to client-specific databases using PDO
 */
class ClientDatabase extends Database
{
    private static $instances = [];
    private $accountNumber;
    private $userName;
    
    /**
     * Constructor
     * 
     * @param string $accountNumber Client account number
     * @param string $userName Username for session tracking
     */
    private function __construct(string $accountNumber, string $userName = 'Guest')
    {
        $this->accountNumber = $accountNumber;
        $this->userName = $userName;
        $this->config = DatabaseConfig::getClientConfig($accountNumber);
    }
    
    /**
     * Get instance of the database (Multiton pattern)
     * 
     * @param string $accountNumber Client account number
     * @param string $userName Username for session tracking
     * @return ClientDatabase Instance
     */
    public static function getInstance(string $accountNumber, string $userName = 'Guest'): ClientDatabase
    {
        $key = $accountNumber;
        
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($accountNumber, $userName);
        }
        
        return self::$instances[$key];
    }
    
    /**
     * Connect to the database
     * 
     * @return PDO|null PDO connection or null on failure
     * @throws PDOException On connection failure if not caught internally
     */
    public function connect()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }
        
        try {
            // Create a DSN string with primary host
            $dsn = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['dbname']};";
            
            // Add connection options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => $this->config['connect_timeout']
            ];
            
            try {
                // Try the primary connection
                $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
            } catch (PDOException $e) {
                // If we have a fallback host and this looks like a connection error, try fallback
                if (!empty($this->config['fallback_host']) && strpos($e->getMessage(), 'connect') !== false) {
                    error_log("Primary connection failed, trying fallback host: {$this->config['fallback_host']}");
                    
                    // Create a DSN with fallback host
                    $fallback_dsn = "pgsql:host={$this->config['fallback_host']};port={$this->config['port']};dbname={$this->config['dbname']};";
                    
                    // Try the fallback connection
                    $this->connection = new PDO($fallback_dsn, $this->config['username'], $this->config['password'], $options);
                } else {
                    // If it's not a connection error or we don't have a fallback, rethrow
                    throw $e;
                }
            }
            
            // Set the application-specific settings
            $this->connection->exec("SET app.username = " . $this->connection->quote($this->userName));
            
            // Test the connection
            $test = $this->connection->query('SELECT 1');
            if (!$test) {
                throw new PDOException('Connection test failed');
            }
            
            return $this->connection;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
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
     * Execute a prepared statement query
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return \PDOStatement|false Statement or false on failure
     */
    public function executeQuery(string $query, array $params = [])
    {
        $conn = $this->getConnection();
        
        // Prepare the query
        $stmt = $conn->prepare($query);
        
        // Execute the query
        $stmt->execute($params);
        
        return $stmt;
    }
    
    /**
     * Fetch all rows from a statement
     * 
     * @param \PDOStatement $stmt PDO statement
     * @return array Rows
     */
    public function fetchAll($stmt): array
    {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch a single row from a statement
     * 
     * @param \PDOStatement $stmt PDO statement
     * @return array|false Row or false on failure
     */
    public function fetchRow($stmt)
    {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback a transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }
} 