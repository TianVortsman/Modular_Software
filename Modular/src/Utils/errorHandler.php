<?php
// Modular/src/Utils/errorHandler.php

function generateErrorCode() {
    return strtoupper(substr(md5(uniqid('', true)), 0, 6)); // e.g. A1B2C3
}

function getFriendlyMessageFromAI($error, $formData = null) {
    $config = require __DIR__ . '/../Config/app.php';
    $endpoint = $config['AI_ENDPOINT'];

    // Use the model name from LM Studio or fallback
    $model = getenv('AI_MODEL') ?: 'nous-hermes-2-mistral-7b-dpo';

    $userContent = $error;
    if ($formData) {
        $userContent .= "\n\nForm data submitted: " . json_encode($formData);
    }

    $data = [
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => "You are a helpful assistant that rewrites technical errors into short, friendly messages for users. If the error is something the user can fix (like invalid input), explain how to fix it. If the error is not fixable by the user (like a missing database table, server error, or code bug), and the environment is production, do NOT show technical details—just say: 'Something went wrong. Please contact support.' Always keep your response concise (max 1-2 sentences)."],
            ["role" => "user", "content" => $userContent]
        ],
        "temperature" => 0.5,
        "max_tokens" => 256,
        "stream" => false
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => json_encode($data),
            'timeout' => 4 // seconds
        ]
    ];

    $context  = stream_context_create($options);

    // --- Start timing ---
    $start = microtime(true);

    $result = @file_get_contents($endpoint, false, $context);

    // --- End timing ---
    $end = microtime(true);
    $duration = $end - $start;
    error_log("[AI Error Handler] Time taken: " . round($duration, 3) . " seconds");

    if (!$result) return null;

    $json = json_decode($result, true);
    return $json['choices'][0]['message']['content'] ?? null;
}

function handleError($errno, $errstr, $errfile, $errline) {
    $config = require __DIR__ . '/../Config/app.php';
    $env = $config['APP_ENV'] ?? 'development';

    $fullError = "[PHP] $errstr in $errfile on line $errline";
    $errorCode = generateErrorCode();

    // Always log the original error first
    $log = date('c') . " | Code: $errorCode | $fullError\n";
    file_put_contents(__DIR__ . '/../../storage/logs/php_errors.log', $log, FILE_APPEND);

    if ($env === 'development') {
        // DEV: show full error
        echo json_encode(['error' => $fullError]);
    } else {
        // PRODUCTION
        // 1. Send to AI
        $friendly = getFriendlyMessageFromAI($fullError);

        // 2. Log full error with code and AI message
        $log = date('c') . " | Code: $errorCode | $fullError | AI: " . ($friendly ?? 'N/A') . "\n";
        file_put_contents(__DIR__ . '/../../storage/logs/php_errors.log', $log, FILE_APPEND);

        // 3. Show AI message or fallback
        echo json_encode([
            'error' => $friendly ?? "Please contact Modular Software Support. Error Code: $errorCode"
        ]);
    }

    http_response_code(500);
    exit;
}

// Register the error handler globally
set_error_handler('handleError');

// Register a global exception handler
set_exception_handler(function($exception) {
    handleError($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
});

// Register a shutdown handler for fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        handleError($error['type'], $error['message'], $error['file'], $error['line']);
    }
}); 