<?php

use App\Application;

require __DIR__.'/vendor/autoload.php';

$router = Application::router();

echo $router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);
