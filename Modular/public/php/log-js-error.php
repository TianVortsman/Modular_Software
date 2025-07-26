<?php
// Modular/public/php/log-js-error.php
require_once __DIR__ . '/../../src/Utils/errorHandler.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendApiErrorResponse('Invalid input data', null, 'JavaScript Error Logging', 'INVALID_INPUT', 400);
}

$errorMessage = $input['message'] ?? 'No message';
$source = $input['source'] ?? 'No source';
$lineno = $input['lineno'] ?? '??';
$colno = $input['colno'] ?? '??';
$stack = $input['stack'] ?? 'No stack';
$errorCode = $input['code'] ?? 'NOCODE';
$url = $input['url'] ?? 'Unknown URL';
$userAgent = $input['userAgent'] ?? 'Unknown User Agent';
$timestamp = $input['timestamp'] ?? date('c');

// Build comprehensive log entry
$logLine = "$timestamp | Code: $errorCode | URL: $url | Error: $errorMessage | Source: $source | Line: $lineno | Column: $colno | Stack: $stack | UserAgent: $userAgent\n";

file_put_contents(__DIR__ . '/../../storage/logs/js_errors.log', $logLine, FILE_APPEND);

// Use the centralized AI error handler with context
$context = "JavaScript error occurred on page: $url";
$formData = [
    'source' => $source,
    'line' => $lineno,
    'column' => $colno,
    'stack' => $stack,
    'userAgent' => $userAgent,
    'url' => $url
];

// Get AI-friendly message
$friendlyMessage = getFriendlyMessageFromAI($errorMessage, $formData, $context);

// Send consistent response
if ($friendlyMessage) {
    sendApiSuccessResponse(null, $friendlyMessage);
} else {
    sendApiSuccessResponse(null, "Something went wrong. Please contact support. Error Code: $errorCode");
} 