<?php
class AuthMiddleware {
    public function handle() {
        // error_log('AuthMiddleware triggered');
    
        // Check for session-based authentication
        if (Auth::check()) {
            // error_log('Session-based authentication detected');
            return; // User authenticated via session
        }
    
        // Check for token-based authentication
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $jwt = substr($authHeader, 7);
    
            try {
                $payload = Jwt::validate($jwt); // Validate token
                // error_log('Token-based authentication detected');
                $_REQUEST['user_id'] = $payload['sub']; // Extract user ID
                return; // User authenticated via token
            } catch (Exception $e) {
                error_log('Token validation failed: ' . $e->getMessage());
                Response::json(['error' => $e->getMessage()], 403);
                exit;
            }
        }
    
        // Default fallback
        error_log('No valid authentication method detected');
        Response::json(['error' => 'Unauthorized: No valid authentication method'], 401);
        exit;
    }
}