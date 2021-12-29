<?php

use App\Controllers\HomeController;
use Cosmic\HTTP\Server\Router;
use function Cosmic\Core\Bootstrap\app;

/** 
 * --- Routes -----------------------------------------------------------------------------
 * 
 * This file contains all the routes(entry points) for this application. 
 * When a route match, the function or the controller passed will be called automatically.
 * 
 * ----------------------------------------------------------------------------------------
 */

$router = app()->get(Router::class);

$router->groupController(HomeController::class, function ($router) {

    $router->get('/', ["login"]);
    $router->get('/login', ["login"]);
    $router->get('/signup', ["signup"]);

});

$router->any(function () {
    return "404";
});
