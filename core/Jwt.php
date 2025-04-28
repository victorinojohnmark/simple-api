<?php 

class Jwt {
    private static $secretKey;

	public static function initialize() {
        self::$secretKey = defined('JWT_SECRET_KEY')
            ? JWT_SECRET_KEY
            : getenv('JWT_SECRET_KEY');

        if (!self::$secretKey) {
            throw new Exception('JWT_SECRET_KEY is not defined');
        }

    }

    public static function generate(array $payload): string {
		self::initialize();

        // Header
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $base64Header = self::base64UrlEncode($header);

        // Payload
        $payload['iat'] = time();
        $payload['exp'] = time() + 3600; // Expires in 1 hour
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        // Signature
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$secretKey, true);
        $base64Signature = self::base64UrlEncode($signature);

        // Combine all parts
        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function validate(string $jwt) {
		self::initialize();
		
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception("Invalid token format");
        }

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        // Validate header
        $header = json_decode(self::base64UrlDecode($base64Header), true);
        if ($header['alg'] !== 'HS256' || $header['typ'] !== 'JWT') {
            throw new Exception("Invalid token header");
        }

        // Recreate the signature
        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "$base64Header.$base64Payload", self::$secretKey, true)
        );

        if ($signature !== $base64Signature) {
            throw new Exception("Invalid token signature");
        }

        // Decode the payload
        $payload = json_decode(self::base64UrlDecode($base64Payload), true);

        // Validate claims
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            throw new Exception("Token has expired");
        }
        if (isset($payload['iat']) && $payload['iat'] > time()) {
            throw new Exception("Token issued in the future");
        }

        return $payload; // Token is valid, return the payload
    }

    private static function base64UrlEncode($data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}