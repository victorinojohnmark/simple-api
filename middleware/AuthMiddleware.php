<?php
class AuthMiddleware {
    public function handle() {
        error_log('AuthMiddleware triggered');

        if (!isset($_SESSION['user_id'])) {
            Response::json(['error' => 'Unauthorized'], 401); // Send JSON response
            exit; // Ensure request halts immediately
        }
    }
}

