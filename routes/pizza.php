<?php

Router::group('/pizzas', [AuthMiddleware::class], function () {
    Router::get('/', function () {
        $controller = new PizzaController();
        $controller->listPizzas();
    });

    Router::get('/{id}', function ($id) {
        $controller = new PizzaController();
        $controller->getPizza($id);
    });

    Router::post('/', function () {
        $controller = new PizzaController();
        $controller->createPizza();
    });

    Router::patch('/{id}', function ($id) {
        $controller = new PizzaController();
        $controller->updatePizza($id);
    });

    Router::delete('/{id}', function ($id) {
        $controller = new PizzaController();
        $controller->deletePizza($id);
    });
});