<?php

class CsrfMiddleware {
    public function handle() {
        // Validate CSRF token for non-GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $token = $_POST['csrf_token'] ?? null;

            // Check JSON payload if CSRF token is not in $_POST
            if (!$token) {
                $rawInput = file_get_contents('php://input');
                $jsonInput = json_decode($rawInput, true);

                // Handle JSON parsing errors
                // if (json_last_error() !== JSON_ERROR_NONE) {
                //     http_response_code(400); // Bad Request
                //     echo json_encode(['error' => 'Invalid request payload']);
                //     exit;
                // }

                $token = $jsonInput['csrf_token'] ?? null;
            }

            // Validate CSRF token
            if (!$token || !Csrf::validateToken($token)) {
                http_response_code(403); // Forbidden
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
        }

        return true; // Proceed to the route
    }
}