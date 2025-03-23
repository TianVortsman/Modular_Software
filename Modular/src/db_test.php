<?php
// Database connection test script
require_once __DIR__ . '/Services/DatabaseService.php';

use Services\DatabaseService;

header('Content-Type: text/plain');

echo "Database Connection Test\n";
echo "=======================\n\n";

// System information
echo "System Information:\n";
echo "- PHP Version: " . phpversion() . "\n";
echo "- PostgreSQL Extension: " . (extension_loaded('pgsql') ? "Loaded" : "Not loaded") . "\n";
echo "- Server: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "- OS: " . php_uname() . "\n\n";

// Test the main database connection
echo "Testing main database connection...\n";
if (DatabaseService::testMainDatabaseConnection()) {
    echo "SUCCESS: Main database connection successful.\n";
    
    // Get the database instance for more specific testing
    $db = DatabaseService::getMainDatabase();
    
    try {
        // Test a simple query
        $result = $db->executeQuery("test_query", "SELECT 1 as test");
        if ($result) {
            $row = $db->fetchRow($result);
            echo "Query test successful: " . (isset($row['test']) ? "Value: {$row['test']}" : "No data returned") . "\n";
            
            // Check if we can access the technicians table (common table used during login)
            echo "\nTesting access to critical tables...\n";
            $tableTest = $db->executeQuery("check_tables", "SELECT EXISTS(SELECT 1 FROM information_schema.tables WHERE table_name = 'technicians') as tech_exists, 
                                                          EXISTS(SELECT 1 FROM information_schema.tables WHERE table_name = 'users') as users_exists");
            
            if ($tableTest) {
                $tableInfo = $db->fetchRow($tableTest);
                echo "- technicians table: " . ($tableInfo['tech_exists'] == 't' ? "Exists" : "Missing") . "\n";
                echo "- users table: " . ($tableInfo['users_exists'] == 't' ? "Exists" : "Missing") . "\n";
            } else {
                echo "Could not test table existence.\n";
            }
        } else {
            echo "WARNING: Query test failed.\n";
            echo "Error: " . $db->getLastError() . "\n";
        }
    } catch (\Exception $e) {
        echo "ERROR: Query test failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "ERROR: Main database connection failed.\n";
    
    // Get the database configuration for diagnostics
    $config = new ReflectionClass('Config\Database');
    $method = $config->getMethod('getMainConfig');
    $dbConfig = $method->invoke(null);
    
    echo "\nConfiguration:\n";
    echo "- Host: " . $dbConfig['host'] . "\n";
    echo "- Fallback Host: " . $dbConfig['fallback_host'] . "\n";
    echo "- Port: " . $dbConfig['port'] . "\n";
    echo "- Database: " . $dbConfig['dbname'] . "\n";
    echo "- Username: " . $dbConfig['username'] . "\n";
    echo "- Connect Timeout: " . $dbConfig['connect_timeout'] . "\n";
    echo "- SSL Mode: " . $dbConfig['sslmode'] . "\n";
    
    // Network diagnostics
    echo "\nNetwork diagnostics:\n";
    
    // Test primary host
    echo "- Testing connection to {$dbConfig['host']}:{$dbConfig['port']}... ";
    $socket = @fsockopen($dbConfig['host'], $dbConfig['port'], $errorNo, $errorStr, 5);
    if ($socket) {
        echo "SUCCESS (socket connection established)\n";
        fclose($socket);
    } else {
        echo "FAILED ($errorStr)\n";
    }
    
    // Test fallback host if different
    if ($dbConfig['host'] != $dbConfig['fallback_host']) {
        echo "- Testing connection to {$dbConfig['fallback_host']}:{$dbConfig['port']}... ";
        $socket = @fsockopen($dbConfig['fallback_host'], $dbConfig['port'], $errorNo, $errorStr, 5);
        if ($socket) {
            echo "SUCCESS (socket connection established)\n";
            fclose($socket);
        } else {
            echo "FAILED ($errorStr)\n";
        }
    }
    
    // Check PostgreSQL server status if possible
    if (function_exists('shell_exec')) {
        echo "\nPostgreSQL server diagnostics:\n";
        
        $output = shell_exec('which psql');
        if ($output) {
            echo "- psql found: " . trim($output) . "\n";
            
            $status = shell_exec('pg_isready');
            echo "- pg_isready output: " . ($status ? trim($status) : "No output") . "\n";
            
            // Try to list databases with psql (doesn't expose password in process list)
            echo "- Attempting to list databases with psql:\n";
            $cmd = "PGPASSWORD='" . str_replace("'", "\\'", $dbConfig['password']) . "' psql -h " . 
                   escapeshellarg($dbConfig['host']) . " -p " . escapeshellarg($dbConfig['port']) . 
                   " -U " . escapeshellarg($dbConfig['username']) . " -l 2>&1";
            $output = shell_exec($cmd);
            
            if ($output) {
                if (strpos($output, 'List of databases') !== false) {
                    echo "  SUCCESS - psql can connect and list databases\n";
                } else {
                    echo "  FAILED - psql error output:\n";
                    echo "  " . str_replace("\n", "\n  ", $output) . "\n";
                }
            } else {
                echo "  No output from psql command\n";
            }
        } else {
            echo "- psql not found in path.\n";
        }
    }
}

// Test connection to a client database if account number is provided
if (isset($_GET['account']) && !empty($_GET['account'])) {
    $accountNumber = $_GET['account'];
    echo "\nTesting client database connection for account: $accountNumber...\n";
    
    if (DatabaseService::testClientDatabaseConnection($accountNumber)) {
        echo "SUCCESS: Client database connection successful.\n";
        
        // Get the client database instance for more specific testing
        $clientDb = DatabaseService::getClientDatabase($accountNumber);
        
        try {
            // Test a simple query
            $conn = $clientDb->connect();
            if ($conn) {
                echo "Connection object valid. Testing simple query...\n";
                $stmt = $clientDb->executeQuery("SELECT 1 as test");
                
                if ($stmt) {
                    $row = $clientDb->fetchRow($stmt);
                    echo "Query test successful: " . (isset($row['test']) ? "Value: {$row['test']}" : "No data returned") . "\n";
                } else {
                    echo "WARNING: Query test failed.\n";
                }
            } else {
                echo "WARNING: Connect() returned a valid connection but tests failed.\n";
            }
        } catch (\Exception $e) {
            echo "ERROR: Client database query test failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "ERROR: Client database connection failed.\n";
    }
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
?> 