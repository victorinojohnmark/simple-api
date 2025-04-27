<?php

require_once 'Router.php';
require_once 'DB.php';
require_once 'Auth.php';
require_once 'Csrf.php';

class App {
    public static function run() {
        session_start();
        Csrf::generateToken();
        Router::resolve();
        DB::testConnection();
    }
}
