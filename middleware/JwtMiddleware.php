<?php

class JwtMiddleware {
    public static function handle() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            Response::json(['message' => 'Unauthorized'], 401); // Send unauthorized error
            exit; // Halt the request
        }

        $jwt = substr($authHeader, 7); // Extract JWT token

        try {
            $payload = Jwt::validate($jwt); // Validate JWT token
            $_REQUEST['user_id'] = $payload['sub']; // Set user ID in the request context
        } catch (Exception $e) {
            Response::json(['message' => $e->getMessage()], 403); // Invalid token or expired
            exit;
        }
    }
}