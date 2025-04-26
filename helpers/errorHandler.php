<?php

// Set a custom error handler to manage errors gracefully
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }

    $data = [
        'message' => $message,
        'file' => $file,
        'line' => $line
    ];

    Response::json($data, 500);
    exit();
});