<?php

class Auth {
    public static function login($user) {
        $_SESSION['user'] = $user;
        $_SESSION['auth_token'] = bin2hex(random_bytes(32));
    }

    public static function logout() {
        session_destroy();
    }

    public static function check() {
        return isset($_SESSION['user']);
    }

    public static function user() {
        return $_SESSION['user'] ?? null;
    }
}
