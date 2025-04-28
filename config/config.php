<?php

define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));

define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY'));

// Define the upload directory
define('UPLOAD_DIR', getenv('UPLOAD_DIR') ?: __DIR__ . '/../uploads/');
