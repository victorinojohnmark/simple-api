<?php
require_once __DIR__ . '/core/App.php';

error_log('Index file loaded');

// Dynamically load all controllers, models, and routes
function autoloadFiles($directory) {
    foreach (glob($directory . '/*.php') as $file) {
        require_once $file;
    }
}

// Load core files
autoloadFiles(__DIR__ . '/core');

// Dynamically load middleware files
autoloadFiles(__DIR__ . '/middleware');

// Load controllers, models, and routes
autoloadFiles(__DIR__ . '/controllers');
autoloadFiles(__DIR__ . '/models');
autoloadFiles(__DIR__ . '/routes');

// Run the App
try {
    App::run();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
