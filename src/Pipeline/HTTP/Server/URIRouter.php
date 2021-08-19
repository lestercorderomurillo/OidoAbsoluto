<?php

namespace Pipeline\HTTP\Server;

use Pipeline\App\App;
use Pipeline\Logger\Logger;
use Pipeline\Core\ResultInterface;
use Pipeline\Factory\ResponseFactory;
use Pipeline\Result\ContentResult;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\Hotswap\ChangeDispatcher;
use Pipeline\HTTP\Common\Request;
use Pipeline\HTTP\Common\Route;

use function Pipeline\Accessors\App;
use function Pipeline\Accessors\Dependency;

class URIRouter
{
    private static array $routes;

    public function __construct()
    {
        FileSystem::requireFromFile(new FilePath(BasePath::DIR_APP, "routes", "php"));
    }

    public function handle(Request $request)
    {
        if (App()->getRuntimeEnvironment()->hasHotswapEnabled()) {

            if ($request->getPath() == "/__HOTSWAP" && strtolower($request->getMethod()) == "get") {
                if (isset($request->getParameters()["page"]) && isset($request->getParameters()["timestamp"])) {
                    ChangeDispatcher::requested($request->getParameters()["page"], $request->getParameters()["timestamp"])->handle();
                }
            }
        }

        foreach (self::$routes as $route) {

            if ($route->matchPath($request)) {

                foreach ($route->getMiddlewares() as $middleware) {
                    $request = $middleware->handle($request);
                }

                $class_name = $route->getControllerName() . "Controller";
                $controller_name = $route->getControllerName();

                $resolve_path = (new FilePath(BasePath::DIR_CONTROLLERS, $class_name, "php"))->toString();

                if (file_exists($resolve_path)) {

                    require_once($resolve_path);
                    $action_name = $route->getActionName();
                    $fully_qualified_class_name = "App\\Controllers\\" . $class_name;
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

                            $result = call_user_func_array([$controller, $action_name], $union);

                            if (!isset($result)) {
                                ResponseFactory::createServerResponse(500, "At " . $controller_name . "Controller: 
                                the function \"$action_name()\" must return a value.")->sendAndDiscard();
                            } else if (is_string($result) || is_int($result)) {
                                $result = new ContentResult("$result");
                            } else if ($result instanceof ResultInterface) {
                                $result->handle();
                            } else {
                                ResponseFactory::createServerResponse(500, "At " . $controller_name . "Controller: 
                                the function \"$action_name()\" must return a valid ResultInterface instance.")->sendAndDiscard();
                            }
                        } else {
                            ResponseFactory::createServerResponse(500, "Parameter number mismatch (in Routes)")->sendAndDiscard();
                        }
                    } else {

                        ResponseFactory::createServerResponse(500, "View Not Found (in Routes)")->sendAndDiscard();
                    }
                } else {
                    ResponseFactory::createServerResponse(500, "Controller Not Found (in Routes)")->sendAndDiscard();
                }
            }
        }

        return (ResponseFactory::createServerResponse(404))->send();
    }

    public static function get(string $match, string $controller_name, string $action_name, $parameters = [])
    {
        $current = new Route(__FUNCTION__, $match, $controller_name, $action_name, $parameters);
        self::$routes[] = $current;
        return $current;
    }

    public static function post(string $match, string $controller_name, string $action_name, $parameters = [])
    {
        $current = new Route(__FUNCTION__, $match, $controller_name, $action_name, $parameters);
        self::$routes[] = $current;
        return $current;
    }
}
