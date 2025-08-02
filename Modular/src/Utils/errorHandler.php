<?php
// Modular/src/Utils/errorHandler.php

function generateErrorCode() {
    return strtoupper(substr(md5(uniqid('', true)), 0, 6)); // e.g. A1B2C3
}

/**
 * Enhanced AI error handler that includes form data for better context
 * @param string $error - The technical error message
 * @param array|null $formData - Form data that was submitted (for better guidance)
 * @param string|null $context - Additional context about what the user was trying to do
 * @return string|null - User-friendly message from AI
 */
function getFriendlyMessageFromAI($error, $formData = null, $context = null) {
    try {
        $config = require __DIR__ . '/../Config/app.php';
        $endpoint = $config['AI_ENDPOINT'] ?? null;
        
        // If no AI endpoint is configured, return null immediately
        if (!$endpoint) {
            return null;
        }

        // Use the model name from LM Studio or fallback
        $model = getenv('AI_MODEL') ?: 'nous-hermes-2-mistral-7b-dpo';

        $userContent = $error;
        
        // Add context if provided
        if ($context) {
            $userContent = "Context: $context\n\nError: $error";
        }
        
        // Add form data for better guidance
        if ($formData && is_array($formData)) {
            $formDataStr = json_encode($formData, JSON_PRETTY_PRINT);
            $userContent .= "\n\nForm data submitted: " . $formDataStr;
        }

        $systemPrompt = "You are a helpful assistant that rewrites technical errors into short, friendly messages for users. 

If the error is something the user can fix (like invalid input, missing required fields, validation errors):
- Explain exactly what's wrong and how to fix it
- If form data is provided, reference specific field names that need attention
- Use clear, simple language
- Be specific about which field(s) need to be filled or corrected

If the error is not fixable by the user (like database errors, server errors, missing tables, code bugs, permission issues):
- In production: Just say 'Something went wrong. Please contact support.'
- In development: You can mention the technical issue briefly

Always keep responses concise (max 1-2 sentences) and actionable.";

        $data = [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $userContent]
            ],
            "temperature" => 0.3,
            "max_tokens" => 256,
            "stream" => false
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => 10, // Increased timeout for AI response
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);

        // Suppress all errors to prevent cascading failures
        $result = @file_get_contents($endpoint, false, $context);

        if (!$result) return null;

        // Log the raw AI response for debugging
        error_log('[AI_DEBUG] Raw AI response: ' . $result);
        
        $json = json_decode($result, true);
        
        // Log the parsed JSON for debugging
        error_log('[AI_DEBUG] Parsed JSON: ' . json_encode($json));
        
        $content = $json['choices'][0]['message']['content'] ?? null;
        
        // Log the extracted content
        error_log('[AI_DEBUG] Extracted content: ' . ($content ?: 'NULL'));
        
        // Only return content if it's a helpful response (not just "Sorry," or similar)
        if ($content && strlen(trim($content)) > 5 && !preg_match('/^(Sorry|Error|Oops|Failed)$/i', trim($content))) {
            return $content;
        }
        
        error_log('[AI_DEBUG] Content too short or incomplete, returning null');
        return null;
        
    } catch (Exception $e) {
        // Completely silent failure - no logging at all
        return null;
    }
}

/**
 * Centralized API error response function
 * This should be used by ALL API endpoints for consistent error handling
 * 
 * @param string $error - The technical error message
 * @param array|null $formData - Form data that was submitted  
 * @param string|null $context - Additional context
 * @param string $errorCode - Custom error code (optional)
 * @param int $httpCode - HTTP status code (default 500)
 */
function sendApiErrorResponse($error, $formData = null, $context = null, $errorCode = null, $httpCode = 500) {
    $config = require __DIR__ . '/../Config/app.php';
    $env = $config['APP_ENV'] ?? 'Production';
    
    if (!$errorCode) {
        $errorCode = generateErrorCode();
    }

    // Always log the original error first
    $logEntry = date('c') . " | Code: $errorCode | Context: " . ($context ?: 'N/A') . " | Error: $error";
    if ($formData) {
        $logEntry .= " | Form Data: " . json_encode($formData);
    }
    $logEntry .= "\n";
    
    file_put_contents(__DIR__ . '/../../storage/logs/php_errors.log', $logEntry, FILE_APPEND);

    // Get AI-friendly message (now enabled)
    $friendlyMessage = getFriendlyMessageFromAI($error, $formData, $context);
    
    // Log the AI response if available
    if ($friendlyMessage) {
        $aiLogEntry = date('c') . " | Code: $errorCode | AI Response: $friendlyMessage\n";
        file_put_contents(__DIR__ . '/../../storage/logs/php_errors.log', $aiLogEntry, FILE_APPEND);
    }

    http_response_code($httpCode);
    header('Content-Type: application/json');

    if ($env === 'development') {
        // DEV: show both technical and AI-friendly message
        echo json_encode([
            'success' => false,
            'message' => $friendlyMessage ?: $error,
            'technical_error' => $error,
            'error_code' => $errorCode,
            'form_data' => $formData,
            'context' => $context
        ]);
    } else {
        // PRODUCTION: show only user-friendly message
        echo json_encode([
            'success' => false,
            'message' => $friendlyMessage ?: "Something went wrong. Please contact support. Error Code: $errorCode",
            'error_code' => $errorCode
        ]);
    }
    exit;
}

/**
 * Centralized success response function for consistency
 */
function sendApiSuccessResponse($data = null, $message = 'Success', $additional = []) {
    header('Content-Type: application/json');
    
    $response = array_merge([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], $additional);
    
    echo json_encode($response);
    exit;
}

/**
 * Handle PHP errors and exceptions
 */
function handleError($errno, $errstr, $errfile, $errline) {
    $fullError = "[PHP] $errstr in $errfile on line $errline";
    
    // Capture current request data for better error context
    $formData = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawData = file_get_contents('php://input');
        if ($rawData) {
            $formData = json_decode($rawData, true);
        }
        if (!$formData) {
            $formData = $_POST;
        }
    }
    
    sendApiErrorResponse($fullError, $formData, 'PHP Runtime Error');
}

/**
 * Handle uncaught exceptions  
 */
function handleException($exception) {
    $fullError = "[Exception] " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    
    // Capture current request data for better error context
    $formData = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawData = file_get_contents('php://input');
        if ($rawData) {
            $formData = json_decode($rawData, true);
        }
        if (!$formData) {
            $formData = $_POST;
        }
    }
    
    sendApiErrorResponse($fullError, $formData, 'Uncaught Exception');
}

/**
 * Handle fatal errors
 */
function handleFatalError() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $fullError = "[Fatal] " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'];
        sendApiErrorResponse($fullError, null, 'Fatal Error');
    }
}

// Register all error handlers globally
set_error_handler('handleError');
set_exception_handler('handleException');
register_shutdown_function('handleFatalError'); 