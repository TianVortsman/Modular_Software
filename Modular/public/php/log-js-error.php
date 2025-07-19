<?php
// Modular/public/php/log-js-error.php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    http_response_code(400);
    exit;
}

$logLine = date('c') . ' | Code: ' . ($input['code'] ?? 'NOCODE') . ' | ' .
    ($input['message'] ?? 'No message') . ' | ' .
    ($input['source'] ?? 'No source') . ' | line ' .
    ($input['lineno'] ?? '??') . ' | col ' .
    ($input['colno'] ?? '??') . ' | stack: ' .
    ($input['stack'] ?? 'No stack') . "\n";

file_put_contents(__DIR__ . '/../../storage/logs/js_errors.log', $logLine, FILE_APPEND);

// Use AI to generate a friendly error message
require_once __DIR__ . '/../../src/Utils/errorHandler.php';
$friendly = getFriendlyMessageFromAI($input['message'] ?? 'A JavaScript error occurred.');

// Always return a friendly error message
if ($friendly) {
    echo json_encode(['success' => false, 'error' => $friendly]);
} else {
    echo json_encode(['success' => false, 'error' => 'Oops! Something went wrong. Please contact Modular Software Support. Error Code: ' . ($input['code'] ?? 'NOCODE')]);
} 