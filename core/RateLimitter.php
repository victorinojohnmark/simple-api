<?php

class RateLimitter {
    private $limit = 5; // Maximum number of requests allowed
    private $interval = 60; // Time period in seconds

    public function handle() {
        $clientIp = $_SERVER['REMOTE_ADDR']; // Use client IP as identifier
        $key = "rate_limit_{$clientIp}_login";
        $currentTime = time();

        // Fetch rate-limiting data from storage (e.g., session or database)
        $rateLimitData = $_SESSION[$key] ?? ['count' => 0, 'reset_time' => $currentTime + $this->interval];

        // Reset counter if interval has passed
        if ($currentTime > $rateLimitData['reset_time']) {
            $rateLimitData = ['count' => 0, 'reset_time' => $currentTime + $this->interval];
        }

        // Increment count and check limit
        $rateLimitData['count']++;
        $_SESSION[$key] = $rateLimitData;

        if ($rateLimitData['count'] > $this->limit) {
			Response::json([
				'error' => 'Rate limit exceeded. Please try again later.',
			], 429);
            exit;
        }

        return true; // Allow request to proceed
    }
}
