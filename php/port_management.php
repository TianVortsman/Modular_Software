<?php
require_once('main-db.php');

function findNextAvailablePort($conn, $startPort = 10000, $endPort = 65535) {
    try {
        // Get all currently used ports
        $result = pg_query_params($conn, 
            "SELECT clock_server_port FROM customers WHERE clock_server_port IS NOT NULL ORDER BY clock_server_port",
            []
        );
        
        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }

        $usedPorts = [];
        while ($row = pg_fetch_array($result)) {
            $usedPorts[] = (int)$row['clock_server_port'];
        }

        // If no ports are used, return the start port
        if (empty($usedPorts)) {
            return $startPort;
        }

        // Find the first available port
        $currentPort = $startPort;
        foreach ($usedPorts as $port) {
            if ($port > $currentPort) {
                // Found a gap in port numbers
                return $currentPort;
            }
            $currentPort = $port + 1;
        }

        // If we've reached here, check if the next port is still within range
        if ($currentPort <= $endPort) {
            return $currentPort;
        }

        throw new Exception("No available ports in the specified range");
    } catch (Exception $e) {
        error_log("Error finding next available port: " . $e->getMessage());
        throw $e;
    }
}

function assignPortToCustomer($conn, $customerId) {
    try {
        // Start transaction
        pg_query($conn, "BEGIN");

        // Check if customer already has a port
        $result = pg_query_params($conn,
            "SELECT clock_server_port FROM customers WHERE customer_id = $1",
            [$customerId]
        );

        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);
        if ($row && $row['clock_server_port']) {
            // Customer already has a port
            pg_query($conn, "COMMIT");
            return (int)$row['clock_server_port'];
        }

        // Find next available port
        $port = findNextAvailablePort($conn);

        // Assign port to customer
        $result = pg_query_params($conn,
            "UPDATE customers SET clock_server_port = $1 WHERE customer_id = $2",
            [$port, $customerId]
        );

        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }

        pg_query($conn, "COMMIT");
        return $port;
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");
        error_log("Error assigning port to customer: " . $e->getMessage());
        throw $e;
    }
}

// Function to assign ports to all customers without ports
function assignPortsToExistingCustomers($conn) {
    try {
        // Get all customers without ports
        $result = pg_query_params($conn,
            "SELECT customer_id FROM customers WHERE clock_server_port IS NULL",
            []
        );

        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }

        $results = ['success' => [], 'failed' => []];

        while ($row = pg_fetch_assoc($result)) {
            try {
                $customerId = (int)$row['customer_id'];
                $port = assignPortToCustomer($conn, $customerId);
                $results['success'][] = [
                    'customer_id' => $customerId,
                    'assigned_port' => $port
                ];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'customer_id' => $customerId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    } catch (Exception $e) {
        error_log("Error in bulk port assignment: " . $e->getMessage());
        throw $e;
    }
} 