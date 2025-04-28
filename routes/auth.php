<?php

// Router::get('/csrf-token', function () {
//     Response::json([
//         'csrf_token' => Csrf::getToken()
//     ]);
// });

Router::group('/auth', [AuthMiddleware::class], function () {
    Router::post('/login', function () {
        $controller = new AuthController();
        $controller->login();
    }, [RateLimitterMiddleware::class]);

    Router::post('/register', function () {
        $controller = new AuthController();
        $controller->register();
    });

    Router::post('/logout', function () {
        $controller = new AuthController();
        $controller->logout();
    });

    Router::get('/user', function () {
        $controller = new AuthController();
        $controller->getAuthenticatedUser();
    });
}, ['/register','/login']);
