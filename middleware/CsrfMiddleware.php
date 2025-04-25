<?php

class CsrfMiddleware {
    public function handle() {
        // Validate CSRF token
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::validateToken($token)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                return false; // Stop further execution
            }
        }

        return true; // Proceed to the route
    }
}
