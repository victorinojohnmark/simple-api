<?php

Router::get('/', function () {
    echo json_encode(['message' => 'Yey, Pizza API is working!']);
});

Router::get('/pizza', function () {
    $controller = new PizzaController();
    $controller->listPizzas();
}, [AuthMiddleware::class]);

Router::post('/pizza/create', function () {
    $controller = new PizzaController();
    $controller->createPizza();
});
