<?php

class Router {
    private static $routes = [];
    private static $csrfProtectedMethods = ['POST', 'PUT', 'DELETE']; // Requests that require CSRF validation

    public static function add($method, $path, $handler, $middleware = []) {
        $method = strtoupper($method);

        // Automatically add CSRF middleware for applicable methods
        if (in_array($method, self::$csrfProtectedMethods)) {
            $middleware[] = CsrfMiddleware::class;
        }

        self::$routes[] = ['method' => $method, 'path' => $path, 'handler' => $handler, 'middleware' => $middleware];
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

    public static function delete($path, $handler, $middleware = []) {
        self::add('DELETE', $path, $handler, $middleware);
    }

    public static function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

        // Return 404 if route is not found
        http_response_code(404);
        echo json_encode(['error' => '404 not found']);
    }
}