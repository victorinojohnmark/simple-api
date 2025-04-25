<?php

Router::get('/csrf-token', function () {
    echo json_encode(['csrf_token' => Csrf::getToken()]);
});

Router::post('/auth/login', function () {
    $controller = new AuthController();
    $controller->login();
});

Router::post('/auth/register', function () {
    $controller = new AuthController();
    $controller->register();
});

Router::post('/auth/logout', function () {
    $controller = new AuthController();
    $controller->logout();
}, [AuthMiddleware::class]);

Router::get('/auth/user', function () {
    $controller = new AuthController();
    $controller->getAuthenticatedUser();
}, [AuthMiddleware::class]);

Router::get('/csrf-token', function () {
    echo json_encode(['csrf_token' => Csrf::getToken()]);
});
