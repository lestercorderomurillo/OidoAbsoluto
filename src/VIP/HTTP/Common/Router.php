<?php

namespace VIP\HTTP\Common;

use App\Controllers;
use VIP\Controller\BaseController;
use VIP\Core\BaseObject;
use VIP\HTTP\Common\Request;
use VIP\HTTP\Common\Route;
use VIP\Factory\ResponseFactory;
use VIP\FileSystem\FilePath;
use VIP\FileSystem\FileSystem;
use VIP\HTTP\Server\Response\AbstractResponse;
use VIP\HTTP\Server\Response\Response;

class Router extends BaseObject
{
    private static array $routes;

    public function __construct()
    {
        FileSystem::requireFromFile(new FilePath(FilePath::DIR_APP, "routes", "php"));
    }

    public function handle(Request $request)
    {
        $system_controller_path = (new FilePath(FilePath::DIR_CONTROLLERS, "SystemController", "php"))->toString();
        if (file_exists($system_controller_path)) {
            require_once($system_controller_path);
        }

        foreach (Router::$routes as $route) {

            if ($route->matchPath($request)) {

                foreach ($route->getMiddlewares() as $middleware) {
                    BaseController::setCurrentMiddleware($middleware->getClassName());
                    $request = $middleware->handle($request);
                }

                $class_name = $route->getControllerName() . "Controller";
                $controller_name = $route->getControllerName();
                $resolve_path = (new FilePath(FilePath::DIR_CONTROLLERS, $class_name, "php"))->toString();

                if (file_exists($resolve_path)) {

                    require_once($resolve_path);
                    $action_name = $route->getActionName();
                    $fully_qualified_class_name = "App\\Controllers\\" . $class_name;
                    $controller = new $fully_qualified_class_name($controller_name);

                    $controller->getLogger()->debug(
                        "{0} has been requested an response from '{1}' action",
                        [$fully_qualified_class_name, $action_name]
                    );

                    if (method_exists($controller, $action_name)) {

                        $expected_number = (new \ReflectionMethod($controller, $action_name))->getNumberOfParameters();
                        if ($expected_number == count($route->getParameters())) {

                            $union = $request->getParameters();
                            foreach ($route->getParameters() as $route_parameter) {
                                if (!isset($union["$route_parameter"])) {
                                    $union["$route_parameter"] = "";
                                }
                            }

                            $response_result = call_user_func_array([$controller, $action_name], $union);

                            if ($response_result instanceof AbstractResponse) {
                                $response = $response_result;
                            } else if (is_string($response_result) || is_int($response_result)) {
                                $response = new Response("$response_result");
                            } else if (!isset($response_result)) {
                                $response = ResponseFactory::createError(500, "At " . $controller_name . "Controller: 
                                    the function \"$action_name()\" must return a value.",);
                            } else {
                                $response = ResponseFactory::createError(500, "At " . $controller_name . "Controller: 
                                    the function \"$action_name()\" must return a valid AbstractResponse object.");
                            }
                        } else {
                            $response = ResponseFactory::createError(500, "Parameter number mismatch (in Routes)");
                        }
                    } else {
                        $response = ResponseFactory::createError(500, "View Not Found (in Routes)");
                    }
                } else {
                    $response = ResponseFactory::createError(500, "Controller Not Found (in Routes)");
                }

                return $response->handle();
            }
        }

        return (ResponseFactory::createError(404))->handle();
    }

    public static function get(string $match, string $controller_name, string $action_name, $parameters = [])
    {
        $current = new Route(__FUNCTION__, $match, $controller_name, $action_name, $parameters);
        Router::$routes[] = $current;
        return $current;
    }

    public static function post(string $match, string $controller_name, string $action_name, $parameters = [])
    {
        $current = new Route(__FUNCTION__, $match, $controller_name, $action_name, $parameters);
        Router::$routes[] = $current;
        return $current;
    }
}
