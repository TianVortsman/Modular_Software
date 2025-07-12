<?php
namespace App\Core\Database;

require_once __DIR__ . '/../../Config/Database.php';

use App\Config\Database as DatabaseConfig;
use \PDO;
use \PDOException;

/**
 * Base Database Class (PDO version)
 * 
 * Provides basic database functionality using PDO
 */
abstract class Database
{
    protected $connection = null;
    protected $config = [];
    
    /**
     * Connect to the database
     * 
     * @return PDO|null Database connection
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
     * @return PDO|null Database connection
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
     * @return bool True if the connection is working, false otherwise
     */
    public function testConnection(): bool
    {
        try {
            if (!$this->isConnected()) {
                $this->connect();
            }
            if (!$this->isConnected()) {
                return false;
            }
            // Run a simple test query
            $stmt = $this->connection->query('SELECT 1');
            return $stmt !== false;
        } catch (\Exception $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a query with parameters using PDO
     * 
     * @param string $query SQL query
     * @param array $params Query parameters (associative or positional)
     * @return \PDOStatement|false Query result
     */
    public function query(string $query, array $params = [])
    {
        $conn = $this->getConnection();
        if (!$conn) {
            error_log("Cannot execute query: No valid database connection");
            return false;
        }
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Failed to execute query: ' . $e->getMessage());
            return false;
        }
    }
} 