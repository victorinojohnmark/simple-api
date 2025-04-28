<?php

class RateLimitterMiddleware {
    private $limit = 5; // Maximum number of requests allowed
    private $interval = 60; // Time period in seconds

    public function handle() {
        $identifier = $this->getIdentifier(); // Get user/session identifier
        $key = "rate_limit_{$identifier}_login";
        $currentTime = time();
    
        // Fetch rate-limiting data from storage
        $rateLimitData = $this->getRateLimitData($key, $currentTime);

        error_log("RateLimiter Key: " . $key);
        error_log("RateLimiter Data: " . json_encode($rateLimitData));
    
        // Check if interval has passed and reset if necessary
        if ($currentTime > $rateLimitData['reset_time']) {
            // Reset counter ONLY if the interval has passed
            $rateLimitData = [
                'count' => 1, // Start new count
                'reset_time' => $currentTime + $this->interval
            ];
        } else {
            // Otherwise, increment the count
            $rateLimitData['count']++;
        }
    
        // Store updated data
        $this->saveRateLimitData($key, $rateLimitData);
    
        // Check rate limit
        if ($rateLimitData['count'] > $this->limit) {
            Response::json([
                'error' => 'Rate limit exceeded. Please try again later.',
            ], 429);
            exit;
        }
    
        return true; // Allow request to proceed
    }

    private function getIdentifier() {
        // Use session-based identifier if user is logged in with session
        if (Auth::check()) { // Session-based check
            return session_id(); // Unique session ID
        }

        // Use token-based identifier if JWT is present
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $jwt = substr($authHeader, 7); // Extract token
            try {
                $payload = Jwt::validate($jwt);
                return $payload['sub']; // Use user ID (sub) from JWT payload
            } catch (Exception $e) {
                Response::json(['error' => $e->getMessage()], 403); // Invalid token
                exit;
            }
        }

        // Fallback: Use client IP address
        return $_SERVER['REMOTE_ADDR']; // IP-based fallback
    }

    private function getRateLimitData($key, $currentTime) {
        // Use session-based storage for session users
        if (Auth::check()) {
            return $_SESSION[$key] ?? ['count' => 0, 'reset_time' => $currentTime + $this->interval];
        }
    
        // Implement token-based storage (Example: Redis or database for token users)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            // Example of retrieving rate-limit data from Redis or similar (pseudo-code)
            // $rateLimitData = Redis::get($key);
            // return $rateLimitData ?? ['count' => 0, 'reset_time' => $currentTime + $this->interval];
        }
    
        // Default fallback for unauthenticated users (IP-based tracking)
        return $_SESSION[$key] ?? ['count' => 0, 'reset_time' => $currentTime + $this->interval];
    }

    private function saveRateLimitData($key, $data) {
        // Save rate-limiting data for session users
        if (Auth::check()) {
            $_SESSION[$key] = $data;
            return;
        }
    
        // Implement token-based storage (Example: Redis or database for token users)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            // Example of saving rate-limit data to Redis or similar (pseudo-code)
            // Redis::set($key, $data, $this->interval);
            return;
        }
    
        // Default fallback for unauthenticated users (IP-based tracking)
        $_SESSION[$key] = $data; // Save data temporarily in session
    }
}