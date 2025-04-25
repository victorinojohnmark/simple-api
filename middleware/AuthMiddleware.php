<?php

class AuthMiddleware {
    public function handle() {
        // Check if the user is authenticated
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return false; // Stop further execution
        }

        return true; // Proceed to the route
    }
}
