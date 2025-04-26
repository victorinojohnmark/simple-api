<?php

class Router {
    private static $routes = [];
    private static $currentGroup = null;

    public static function add($method, $path, $handler, $middleware = []) {
        $method = strtoupper($method);

        // Apply group prefix and middleware unless excluded
        if (self::$currentGroup) {
            $path = self::$currentGroup['prefix'] . $path;

            // Remove excluded middleware if needed
            if (!in_array($path, self::$currentGroup['excluded_routes'])) {
                $middleware = array_merge(self::$currentGroup['middleware'], $middleware);
            }
        }

        self::$routes[] = ['method' => $method, 'path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public static function group($prefix, $middleware, $callback, $excluded_routes = []) {
        self::$currentGroup = [
            'prefix' => $prefix,
            'middleware' => $middleware,
            'excluded_routes' => array_map(function ($route) use ($prefix) {
                return $prefix . $route;
            }, $excluded_routes)
        ];
    
        // Execute callback to register routes within group
        $callback();
    
        // Reset group after execution
        self::$currentGroup = null;
    }

    public static function get($path, $handler, $middleware = []) {
        self::add('GET', $path, $handler, $middleware);
    }

    public static function post($path, $handler, $middleware = []) {
        self::add('POST', $path, $handler, $middleware);
    }

    public static function put($path, $handler, $middleware = []) {
        self::add('PUT', $path, $handler, $middleware);
    }

    public static function patch($path, $handler, $middleware = []) {
        self::add('PATCH', $path, $handler, $middleware);
    }

    public static function delete($path, $handler, $middleware = []) {
        self::add('DELETE', $path, $handler, $middleware);
    }

    public static function resolve() {
		$method = $_SERVER['REQUEST_METHOD'];
		$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	
		// Validate JSON payload for non-GET requests
		if ($method !== 'GET') {
			$rawInput = file_get_contents('php://input');
			json_decode($rawInput, true);
	
			if (json_last_error() !== JSON_ERROR_NONE) {
                // Invalid JSON payload → Send 400 response
                Response::json([
                    'error' => 'Invalid request payload',
                ], 400);

				exit;
			}
		}
	
		foreach (self::$routes as $route) {
			if ($route['method'] === $method && $route['path'] === $uri) {
				// Apply middleware before executing handler
				foreach ($route['middleware'] as $middleware) {
					(new $middleware)->handle();
				}
				call_user_func($route['handler']);
				return;
			}
		}
	
		// Route does not exist → Send 404 response
        Response::json([
            'error' => '404 Not Found',
        ], 404);
        
		exit;
	}
	
}