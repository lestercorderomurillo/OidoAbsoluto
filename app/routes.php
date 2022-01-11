<?php

use Cosmic\HTTP\Server\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\InformationController;
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

$router->withController(HomeController::class, function (Router $router) {

    $router->get('/', ["login"]);
    $router->get('/index', ["login"]);

    $router->get('/login', ["login"]);
    $router->post('/login/submit', ["loginSubmit"]);

    $router->get('/signup', ["signup"]);
    $router->post('/signup/submit', ["signupSubmit"]);

    $router->get('/logout', ["logout"]);

    $router->get('/forgot', ["resetRequest"]);
    $router->post('/forgot/submit', ["resetRequestSubmit"]);


    // NOT DONE
    $router->get('/newpass', ["resetPassword"]);

});

$router->withController(UserController::class, function (Router $router) {

    $router->withMiddlewares(Authentication::class, function (Router $router) {
        
        $router->get('/profile', ["profile"]);
        $router->get('/survey', ["survey"]);
        $router->post('/survey/submit', ["surveySubmit"]);
        $router->get('/piano', ["piano"]);
        $router->post('/piano/submit', ["pianoSubmit"]);
        $router->get('/overview', ["overview"]);
        
    });

});

$router->withController(InformationController::class, function (Router $router) {
        
    $router->get('/about', ["about"]);
    $router->get('/policy', ["policy"]);

});

$router->any(function (Router $router) {
    
    return $router->view("404");

});
