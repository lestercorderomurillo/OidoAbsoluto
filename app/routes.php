<?php

use Cosmic\HTTP\Server\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\InformationController;
use Cosmic\Bundle\Middlewares\GuestMiddleware;
use Cosmic\Bundle\Middlewares\RegularUserMiddleware;
use Cosmic\Bundle\Middlewares\AdministratorMiddleware;
use Cosmic\Bundle\Middlewares\AuthenticationMiddleware;

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

    $router->get('/', ["index"]);
    $router->get('/index', ["index"]);
    $router->get('/logout', ["logout"]);
    $router->get('/lang', ["lang"]);

    $router->withMiddlewares(GuestMiddleware::class, function (Router $router){

        $router->get('/login', ["login"]);
        $router->post('/login/submit', ["loginSubmit"]);
    
        $router->get('/signup', ["signup"]);
        $router->post('/signup/submit', ["signupSubmit"]);

        $router->get('/forgot', ["resetRequest"]);
        $router->post('/forgot/submit', ["resetRequestSubmit"]);
    
        $router->get('/newpass', ["resetPassword"]);
        $router->post('/newpass/submit', ["resetPasswordSubmit"]);

    });

});

$router->withController(UserController::class, function (Router $router) {

    $router->withMiddlewares(AuthenticationMiddleware::class, function (Router $router) {

        $router->get('/profile', ["profile"]);
        $router->get('/overview', ["overview"]);
        $router->get('/overview/exportTest', ["exportTest"]);

        $router->withMiddlewares(AdministratorMiddleware::class, function (Router $router) {

            $router->get('/lookup', ["lookup"]);
            $router->get('/lookup/exportSurvey', ["exportSurvey"]);
            $router->get('/lookup/exportUserTests', ["exportUserTests"]);
            $router->get('/roleChange', ["roleChange"]);

        });

        $router->withMiddlewares(RegularUserMiddleware::class, function (Router $router) {

            $router->get('/survey', ["survey"]);
            $router->post('/survey/submit', ["surveySubmit"]);
            $router->get('/piano', ["piano"]);
            $router->post('/piano/submit', ["pianoSubmit"]);

        });

    });

});

$router->withController(InformationController::class, function (Router $router) {
        
    $router->get('/about', ["about"]);
    $router->get('/policy', ["policy"]);

});

$router->any(function (Router $router) {
    
    return $router->view("404");

});
