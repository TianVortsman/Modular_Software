<?php

namespace App\Controllers;

class ClockServerController {
    // Cache the server availability status to avoid repeated checks
    private static $serverAvailable = null;
    // How long to cache the server availability status (in seconds)
    private static $cacheTimeout = 60;
    // When the cache was last updated
    private static $lastCheck = 0;

    public static function getStatus($accountNumber) {
        $url = "http://hikvision-server:3000/clock/status/$accountNumber";
        
        // Check if server is available (using cache when possible)
        if (!self::isServerAvailable()) {
            return ['success' => false, 'error' => 'Clock server is unreachable', 'running' => false];
        }
        
        return self::call($url);
    }

    public static function startServer($accountNumber) {
        $url = "http://hikvision-server:3000/clock/start/$accountNumber";
        
        // Check if server is available (using cache when possible)
        if (!self::isServerAvailable()) {
            return ['success' => false, 'error' => 'Clock server is unreachable', 'started' => false];
        }
        
        return self::call($url, 'POST');
    }

    public static function stopServer($accountNumber) {
        $url = "http://hikvision-server:3000/clock/stop/$accountNumber";
        
        // Check if server is available (using cache when possible)
        if (!self::isServerAvailable()) {
            return ['success' => false, 'error' => 'Clock server is unreachable', 'stopped' => false];
        }
        
        return self::call($url, 'POST');
    }

    /**
     * Check if server is available, using cache when possible
     * 
     * @return bool True if server is reachable, false otherwise
     */
    private static function isServerAvailable() {
        // Always check the server (disable cache for debugging)
        $url = "http://hikvision-server:3000/clock/status/ACC001";
        error_log("[ClockServerController] Checking server availability at: $url");
        $result = self::waitForServer($url, 5);
        error_log("[ClockServerController] Server available: " . ($result ? 'YES' : 'NO'));
        return $result;
    }

    /**
     * Utility function to wait for the server to be reachable
     * 
     * @param string $url The URL to check
     * @param int $timeoutSeconds Maximum time to wait in seconds
     * @return bool True if server is reachable, false otherwise
     */
    private static function waitForServer($url, $timeoutSeconds = 10) {
        $start = time();
        
        // Try different hostnames in case Docker DNS is flaky
        $urlVariations = [
            $url,
            str_replace('hikvision-server', 'localhost', $url),
            str_replace('hikvision-server', '127.0.0.1', $url)
        ];
        
        do {
            // Try each URL variation
            foreach ($urlVariations as $testUrl) {
                $opts = [
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 2,         // Short timeout per attempt
                        'ignore_errors' => true // Continue even if there are HTTP errors
                    ]
                ];
                
                $context = stream_context_create($opts);
                
                // Silence warnings with @
                $headers = @get_headers($testUrl, 0, $context);
                
                // If we got any headers, the server is responsive
                if ($headers) {
                    error_log("Successfully connected to clock server at: $testUrl");
                    return true;
                }
            }
            
            // Wait before trying again (500ms)
            usleep(500000);
            
        } while ((time() - $start) < $timeoutSeconds);
        
        error_log("Failed to connect to clock server after $timeoutSeconds seconds");
        return false;
    }

    private static function call($url, $method = 'GET') {
        $opts = [
            "http" => [
                "method" => $method,
                "header" => "Content-Type: application/json",
                "ignore_errors" => true,  // Continue even if server returns an error
                "timeout" => 5            // 5 second timeout
            ]
        ];
        
        try {
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            
            // Get response status code
            $status_line = $http_response_header[0] ?? '';
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1] ?? 500;
            
            // Parse the response
            $result = json_decode($response, true);
            
            // Always include a 'success' field for consistency
            if (is_array($result)) {
                if (!isset($result['success'])) {
                    $result['success'] = ($status >= 200 && $status < 300);
                }
                return $result;
            }
            
            // Handle empty or non-JSON response
            return [
                'success' => ($status >= 200 && $status < 300),
                'running' => isset($result['running']) ? $result['running'] : false,
                'status' => $status
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
} 