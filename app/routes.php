<?php

use App\Models\UserInfo;
use App\Controllers\DeveloperController;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Server\Router;
use Cosmic\Prefabs\Middleware\ForceSSL;

use function Cosmic\Core\Boot\app;

$router = app()->get(Router::class);

$router->get('/', function (Request $request) {

    $userAgent = $request->getUserAgent();
    return "<div>user-agent: $userAgent.</div>";

});

$router->get('/path', function (Request $request) {

    $path = $request->getFullPath();
    return "<div>path: $path</div>";
});

$router->get('/form', function (Request $request) {

    $data = var_export($request->getFormData(), true);
    return "<div>data: $data</div>";
});

$router->get('/path/test/', [DeveloperController::class]);
$router->get('/path/test2/', [DeveloperController::class, "testMethod1"]);

$router->groupController(DeveloperController::class, function ($router) {

    $router->get('/path/test3/', "testMethod1");
    $router->get('/path/test4/', "testMethod1");
    $router->get('/path/test5/', "testMethod1");

});

$router->groupMiddlewares(ForceSSL::class, function($router){

    $router->groupController(DeveloperController::class, function ($router) {


        $router->get('/path/test6/', "index");

    
    });

    
});


$router->get("/model",function () {

    $model = new UserInfo();
    $model->firstName = "Lester";
    $model->lastName = "Cordero";

    return $model;
});


$router->any(function () {

    return "404";

});