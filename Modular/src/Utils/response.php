<?php
/**
 * HTTP Response utilities for APIs
 */

/**
 * Send an HTTP response with appropriate status code and JSON content
 * 
 * @param int $statusCode HTTP status code
 * @param string $message Message explaining the response
 * @param array $data Optional data to include in the response
 * @return void Outputs JSON response and exits
 */
function httpResponse($statusCode, $message, $data = []) {
    // Set HTTP response code
    http_response_code($statusCode);
    
    // Set content type header
    header('Content-Type: application/json');
    
    // Allow cross-origin requests if needed for this API
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Prepare response data
    $response = [
        'status' => $statusCode,
        'message' => $message
    ];
    
    // Add any additional data
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    // Output JSON response
    echo json_encode($response);
    exit;
}

/**
 * Send a success response (HTTP 200)
 * 
 * @param string $message Success message
 * @param array $data Optional data to include
 * @return void Outputs JSON response and exits
 */
function successResponse($message, $data = []) {
    httpResponse(200, $message, array_merge(['success' => true], $data));
}

/**
 * Send an error response (HTTP 400 by default)
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code (defaults to 400)
 * @param array $data Optional data to include
 * @return void Outputs JSON response and exits
 */
function errorResponse($message, $statusCode = 400, $data = []) {
    httpResponse($statusCode, $message, array_merge(['success' => false], $data));
} 