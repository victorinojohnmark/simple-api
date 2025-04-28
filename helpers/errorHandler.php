<?php

// Set a custom error handler to manage errors gracefully
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }

    // JSON-specific error check
    if (strpos($message, 'json_decode') !== false) {
        Response::json([
            'error' => 'Invalid JSON format',
            'details' => $message,
        ], 400);
        exit();
    }

    // General PHP error handling
    $data = [
        'message' => $message,
        'file' => $file,
        'line' => $line,
    ];
    Response::json($data, 500);
    exit();
});

// Validate incoming JSON request only for POST, PUT, PATCH methods
$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
    $rawInput = file_get_contents('php://input');
    $decodedInput = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::json([
            'error' => 'Invalid JSON format: ' . json_last_error_msg(),
        ], 400);
        exit();
    }
}