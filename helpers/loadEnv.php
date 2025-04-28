<?php

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        die("Environment file not found: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) { // Ignore comments
            continue;
        }

        $pair = explode('=', $line, 2); // Split key and value
        if (count($pair) === 2) {
            $key = trim($pair[0]);
            $value = trim($pair[1]);
            putenv("$key=$value"); // Load into the environment
        }
    }
}

function validateEnv() {
    if (!getenv('DB_HOST') || !getenv('DB_NAME') || !getenv('DB_USER') || !getenv('DB_PASS') || !getenv('JWT_SECRET_KEY')) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Missing required environment variables. Please check your .env file.'
        ]));
    }
}
