<?php

use App\Controllers\HomeController;
use App\Controllers\UserController;
use Cosmic\HTTP\Server\Router;
use Cosmic\Bundle\Middlewares\Authentication;

/** 
 * --- Routes -----------------------------------------------------------------------------
 * 
 * This file contains all the routes(entry points) for this application. 
 * When a route match, the function or the controller passed will be called automatically.
 * 
 * @var Router $router The Router instance.
 */

$router = app()->get(Router::class);

$router->groupController(HomeController::class, function (Router $router) {

    $router->get('/', ["login"]);
    $router->get('/index', ["login"]);

    $router->get('/login', ["login"]);
    $router->post('/login/submit', ["loginSubmit"]);

    $router->get('/signup', ["signup"]);
    $router->post('/signup/submit', ["signupSubmit"]);





    // NOT DONE
    $router->get('/forgot', ["resetRequest"]);
    $router->get('/newpass', ["resetPassword"]);

});

$router->groupController(UserController::class, function (Router $router) {

    $router->groupMiddlewares([Authentication::class], function (Router $router) {
        
        $router->get('/profile', ["profile"]);
        $router->get('/survey', ["survey"]);
        
    });

    $router->get('/logout', ["logout"]);

});

$router->any(function (Router $router) {
    
    return $router->view("404");

});
