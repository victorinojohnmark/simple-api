<?php
class AuthMiddleware {
    public function handle() {
        error_log('AuthMiddleware triggered');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit; // Ensure request halts immediately
        }
    }
}

