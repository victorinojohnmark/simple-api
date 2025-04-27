<?php
require_once __DIR__ . '/core/App.php';

require_once __DIR__ . '/helpers/loadEnv.php';
loadEnv(__DIR__ . '/.env');
validateEnv();

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

require_once __DIR__ . '/helpers/errorHandler.php';

App::run();
