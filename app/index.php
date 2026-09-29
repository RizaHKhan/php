<?php

require __DIR__ . '/vendor/autoload.php';

use App\Router;

// Default PHP sessions use a browser-session cookie: it expires when the browser closes.
// The server-side session file lifetime is controlled by PHP's session.gc_maxlifetime setting.
session_start();

$router = new Router();

echo $router->dispatch($_SERVER['REQUEST_URI'] ?? '/');
