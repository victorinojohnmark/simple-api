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
    
        // Normalize the URI by trimming trailing slashes, except for the root `/`
        $normalizedUri = rtrim($uri, '/');
        $normalizedUri = $normalizedUri === '' ? '/' : $normalizedUri;

        // error_log("Original URI: $uri");
        // error_log("Normalized URI: $normalizedUri");
    
        // Validate JSON payload for non-GET requests
        // if ($method !== 'GET') {
        //     $rawInput = file_get_contents('php://input');
        //     $jsonInput = json_decode($rawInput, true);
    
        //     if (json_last_error() !== JSON_ERROR_NONE) {
        //         Response::json([
        //             'error' => 'Invalid request payload',
        //         ], 400);
        //         exit;
        //     }
    
        //     // Validate CSRF token
        //     $csrfToken = $jsonInput['csrf_token'] ?? $_POST['csrf_token'] ?? null;
        //     if (!$csrfToken || !Csrf::validateToken($csrfToken)) {
        //         Response::json([
        //             'error' => 'CSRF token mismatch or missing',
        //         ], 403);
        //         exit;
        //     }
        // }
    
        foreach (self::$routes as $route) {
            // Check for dynamic matching using regex
            if ($route['method'] === $method && self::matchWithRegex($route['path'], $normalizedUri, $params)) {
                // Apply middleware before executing the handler
                foreach ($route['middleware'] as $middleware) {
                    (new $middleware)->handle();
                }
    
                // Execute the route handler with extracted parameters
                call_user_func_array($route['handler'], $params);
                return;
            }
    
            // Check for exact static match (trailing slashes handled)
            $routePath = rtrim($route['path'], '/');
            $routePath = $routePath === '' ? '/' : $routePath;
    
            if (
                $route['method'] === $method &&
                ($routePath === $normalizedUri || $routePath . '/' === $normalizedUri)
            ) {
                // Apply middleware before executing the handler
                foreach ($route['middleware'] as $middleware) {
                    (new $middleware)->handle();
                }
    
                // Execute the route handler
                call_user_func($route['handler']);
                return;
            }
        }
    
        // Route not found
        Response::json([
            'error' => '404 Not Found',
        ], 404);
        exit;
    }
    
    private static function matchWithRegex($routePath, $uri, &$params) {
        // Convert placeholders (e.g., {id}) to regex patterns
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_\-]+)', $routePath);
        $regex = '@^' . $regex . '$@'; // Add regex delimiters
    
        // Match the URI against the generated regex pattern
        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches); // Remove the full match
            $params = $matches;    // Extract dynamic parameters
            return true;
        }
    
        return false;
    }    
	
}