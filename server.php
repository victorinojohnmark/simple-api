<?php
// Route static files if they exist
if (file_exists(__DIR__ . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH))) {
    return false;
}

// Otherwise, route everything to index.php
require_once __DIR__ . '/index.php';