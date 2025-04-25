<?php

// Only allow requests through index.php
if (basename($_SERVER['SCRIPT_FILENAME']) !== 'index.php') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Route static files if they exist
if (file_exists(__DIR__ . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH))) {
    return false;
}

// Route everything to index.php
require_once __DIR__ . '/index.php';