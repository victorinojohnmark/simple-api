<?php

class Csrf {
    public static function generateToken($forceRegenerate = false) {
		if ($forceRegenerate || empty($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
	}

    public static function validateToken($token) {
        return hash_equals($_SESSION['csrf_token'], $token);
    }

	public static function getToken() {
		return $_SESSION['csrf_token'] ?? null;
	}
}
