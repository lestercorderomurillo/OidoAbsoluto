<?php

namespace Pipeline\HTTP\Server;

use Pipeline\Logger\Logger;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\Hotswap\ChangeDispatcher;
use Pipeline\HTTP\Common\Request;
use Pipeline\HTTP\Common\Route;
use Pipeline\HTTP\Common\RouteInterface;
use Pipeline\HTTP\InvalidMessage;
use Pipeline\HTTP\NullMessage;
use Pipeline\Middleware\ForceSSL;
use ReflectionFunction;
use ReflectionObject;

use function Pipeline\Accessors\App;
use function Pipeline\Accessors\Configuration;
use function Pipeline\Accessors\Dependency;

class Router
{
    private static array $routes;

    public function __construct()
    {
        FileSystem::requireFromFile(new FilePath(SystemPath::APP, "routes", "php"));
    }

    public static function setMiddlewares($array_or_one): void
    {
        if (is_array($array_or_one)) {
            foreach ($array_or_one as $middleware) {
                foreach (self::$routes as $route) {
                    $route->setMiddlewares($middleware);
                }
            }
        } else {
            foreach (self::$routes as $route) {
                $route->setMiddlewares($array_or_one);
            }
        }
    }

    public function handle(Request $request)
    {
        $response_sent = false;

        if (App()->getRuntimeEnvironment()->hasHotswapEnabled()) {
            if ($request->getPath() == "/__HOTSWAP" && strtolower($request->getMethod()) == "get") {
                if (isset($request->getParameters()["page"]) && isset($request->getParameters()["timestamp"])) {
                    $result = ChangeDispatcher::requested($request->getParameters()["page"], $request->getParameters()["timestamp"]);
                    $result->toResponse()->sendAndExit();
                }
            }
        }

        // Global default middlewares
        if (Configuration("application.https")) {
            self::setMiddlewares(ForceSSL::class);
        }

        foreach (self::$routes as $route) {

            if ($route->matchPath($request)) {

                $message = $request;
                foreach ($route->getMiddlewares() as $middleware) {
                    $message = $middleware->handle($message);

                    if ($message instanceof ServerResponse) {
                        $message->sendAndExit();
                    }
                }

                $controller_class_name = $route->getControllerName() . "Controller";
                $controller_name = $route->getControllerName();

                $controller_path = FilePath::create(SystemPath::CONTROLLERS, $controller_class_name, "php")->toString();

                if (file_exists($controller_path)) {

                    require_once($controller_path);

                    $action_name = $route->getActionName();
                    $fully_qualified_class_name = "App\\Controllers\\" . $controller_class_name;
                    $controller = new $fully_qualified_class_name($controller_name);

                    Dependency(Logger::class)->debug(
                        "{0} has been requested a response from '{1}' action",
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

                            $reflection_object = new ReflectionObject($controller);
                            $reflection_method = $reflection_object->getMethod($action_name);

                            $reflection_params = $reflection_method->getParameters();
                            $i = 0;
                            foreach ($union as $key => $value) {
                                if($reflection_params[$i++]->getType() == "int"){
                                    $union[$key] = (int)$value;
                                }

                            }
                            /*var_dump($union);
                            die();*/
                            $anything_from_controller = call_user_func_array([$controller, $action_name], $union);
                            $response = $controller->handle($anything_from_controller);

                            if ($response instanceof InvalidMessage) {
                                ServerResponse::create(500, "At " . $controller_name . "Controller: 
                                the function \"$action_name()\" must return a valid ResultInterface instance.")->sendAndExit();
                            } else if ($response instanceof NullMessage) {
                                ServerResponse::create(500, "At " . $controller_name . "Controller: 
                                the function \"$action_name()\" must return a value.")->sendAndExit();
                            }

                            $response_sent = true;
                            $response->send();
                        } else {

                            ServerResponse::create(500, "Parameter number mismatch (in Routes)")->sendAndExit();
                        }
                    } else {

                        ServerResponse::create(500, "View Not Found (in Routes)")->sendAndExit();
                    }
                } else {
                    ServerResponse::create(500, "Controller Not Found (in Routes)")->sendAndExit();
                }
            }
        }

        if (!$response_sent) {
            return (ServerResponse::create(404))->send();
        }
    }

    public static function get(string $match, string $controller_name, string $action_name = null, $parameters = []): Route
    {
        if(!$action_name) $action_name = substr($controller_name, 1);
        $current = new Route(__FUNCTION__, $match, $controller_name, $action_name, $parameters);
        self::$routes[] = $current;
        return $current;
    }

    public static function post(string $match, string $controller_name, string $action_name = null, $parameters = []): Route
    {
        if(!$action_name) $action_name = substr($controller_name, 1);
        $current = new Route(__FUNCTION__, $match, $controller_name, $action_name, $parameters);
        self::$routes[] = $current;
        return $current;
    }
}
