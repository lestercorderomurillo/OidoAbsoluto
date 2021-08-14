<?php

namespace VIP\HTTP\Common;

use App\Controllers;
use VIP\Core\BaseObject;
use VIP\HTTP\Common\Request;
use VIP\HTTP\Common\Route;
use VIP\HTTP\Server\Response\AbstractResponse;
use VIP\HTTP\Server\Response\Response;
use VIP\Factory\ResponseFactory;

class Router extends BaseObject
{
    private static array $routes;

    public function __construct()
    {
        require_once(__RDIR__ . "/app/routes.php");
    }

    public function handle(Request $request)
    {
        $system_controller_path = __RDIR__ . "/app/controllers/SystemController.php";
        if (file_exists($system_controller_path)) {
            require_once($system_controller_path);
        }

        foreach (Router::$routes as $route) {

            if ($route->matchPath($request)) {

                foreach ($route->getMiddlewares() as $middleware) {
                    $request = $middleware->handle($request);
                }

                $class_name = $route->getControllerName() . "Controller";
                $controller_name = $route->getControllerName();
                $resolve_path = __RDIR__ . "/app/controllers/$class_name.php";

                if (file_exists($resolve_path)) {

                    require_once($resolve_path);
                    $action_name = $route->getActionName();
                    $fully_qualified_class_name = "App\\Controllers\\" . $class_name;
                    $controller = new $fully_qualified_class_name($controller_name);

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
