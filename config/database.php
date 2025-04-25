<?php

require_once __DIR__ . '/../config/config.php';

return [
    'dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
    'user' => DB_USER,
    'password' => DB_PASS
];