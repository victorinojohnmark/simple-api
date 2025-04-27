<?php

Router::get('/', function () {
    Response::json([
        'message' => 'Yey, Pizza API is working!',
    ]);
});