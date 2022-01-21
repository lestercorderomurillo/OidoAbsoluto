<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP;

use Cosmic\Core\Abstracts\Controller;
use Cosmic\Core\Exceptions\ControllerException;
use Cosmic\HTTP\Request;
use Cosmic\FileSystem\FS;
use Cosmic\FileSystem\Exceptions\IOException;
use Cosmic\FileSystem\Paths\FilePath;
use Cosmic\HTTP\Middlewares\SSLMiddleware;
use Cosmic\HTTP\Server\EntryPoint;
use Cosmic\Utilities\Collections;
use Cosmic\Utilities\Strings;

/**
 * A router class that manages the entry points for this application. 
 * 
 * When a entry point is registered, the router will remember the newly added regex pattern and will try match it against all subsequents incoming user requests.
 * On match, the router will execute the entry point closure and finalize the application after. All middlewares and late-middlewares will be executed.
 * When the closure for the entry point is called, the application will not exit that specific scope until the application has been terminated.
 */
class Router extends Controller
{
    /**
     * @var EntryPoint[] $entryPoints A collection of entry points for this application.
     */
    private array $entryPoints;

    /**
     * @var Middleware[] $middlewares A collection of middlewares for this application.
     */
    private array $middlewares;

    /**
     * @var Middleware[] $lateMiddlewares A collection of middlewares for this application. 
     * This holds middlewares that execute after the application has already finished.
     */
    private array $lateMiddlewares;

    /**
     * @var string $controllerClassName If not null, this will be the default controller class to use for path matching.
     */
    private string $controllerClassName;

    /**
     * Constructor. By default, it will try to add some default middlewares to the application.
     * Example: The SSL middleware is automatically added if "application.https" key is set to true in the configuration file.
     */
    public function __construct()
    {
        $this->entryPoints = [];
        $this->middlewares = [];
        $this->lateMiddlewares = [];
        $this->controllerClassName = __EMPTY__;

        if (configuration("application.https")) {
            $this->middlewares[] = SSLMiddleware::class;
        }
    }

    /**
     * Apply a controller group to the given closure. All routes registed inside will have the passed controller as 
     * their default controller, and only the Controller must be passed in the array.
     * 
     * @param string $controllerClassName The class used as the controller.
     * @param \Closure $closure Apply a controller to all the routes registered inside the closure.
     * 
     * @return Router The router instance with a scoped controller.
     */
    public function withController(string $controllerClassName, \Closure $closure): Router
    {
        $previous = $this->controllerClassName;
        $this->controllerClassName = $controllerClassName;
        $closure($this);
        $this->controllerClassName = $previous;
        return $this;
    }

    /**
     * Apply a middleware group to the given closure. 
     * 
     * @param string[]|string $middlewares A collection of middlewares classes that will be applied to all routes registered in the closure.
     * Normalized by default if passed a single element instead. This method doesn't expect instances, but class definitions.
     *
     * @param \Closure $closure The scoped closure to be executed.
     * 
     * @return Router The router instance using a group of middlewares.
     */
    public function withMiddlewares($middlewares, \Closure $closure): Router
    {
        $middlewares = Collections::normalizeToList($middlewares);
        $previous = $this->middlewares;
        $this->middlewares = Collections::merge($previous, $middlewares);
        $closure($this);
        $this->middlewares = $previous;
        return $this;
    }

    /**
     * Apply a late middleware group to the given closure. 
     * 
     * @param string[]|string $lateMiddlewares A collection of late-middlewares classes that will be applied to all routes registered in the closure.
     * Normalized by default if passed a single element instead. This method doesn't expect instances, but class definitions.
     * 
     * @param \Closure $closure The scoped closure to be executed.
     * 
     * @return Router The router instance using a group of late middlewares.
     */
    public function groupLateMiddlewares($lateMiddlewares, \Closure $closure): Router
    {
        $lateMiddlewares = Collections::normalizeToList($lateMiddlewares);
        $previous = $this->lateMiddlewares;
        $this->lateMiddlewares = $lateMiddlewares;
        $closure($this);
        $this->lateMiddlewares = $previous;
        return $this;
    }

    /**
     * Register a new entry point for this application.
     * 
     * @param string $pathRegex The path to use to create this entry point.
     * @param string $method The method used. Can be "get" or "post" only.
     * @param string|Closure|array|null $mixed A closure to call when this entry point is called. If provided an array instead,
     * then this method will try to create the controller class, inject all dependencies and call the method automatically.
     * If passed an string, will try to normalize the string to a one element array instead.
     * If passed null, then it will try to use the default controller class previously registered. 
     * If not provided, then this method finally will throw an exception.
     * 
     * @return EntryPoint The registered entry point.
     * 
     * @throws IOException
     * @throws ControllerException
     * @throws InvalidArgumentException
     */
    public function registerEntryPoint(string $pathRegex, string $method, $mixed = null): EntryPoint
    {
        $entryPoint = null;

        if ($mixed == null) {

            if ($this->controllerClassName != __EMPTY__) {

                $reflectionClass = new \ReflectionClass($this->controllerClassName);
                $controllerActionName = "index";

                if (!$reflectionClass->hasMethod($controllerActionName)) {
                    throw new ControllerException("The controller doesn't have a method named " . $controllerActionName);
                }

                $reflectionMethod = $reflectionClass->getMethod($controllerActionName);
                $entryPoint = new EntryPoint($pathRegex, $method, $reflectionMethod->getClosure());
            } else {

                throw new \InvalidArgumentException("A entry point or a group doesn't have a controller binded");
            }
        } else if (is_array($mixed) || is_string($mixed)) {

            $mixed = Collections::normalizeToList($mixed);

            if (count($mixed) == 2) {

                $controllerClassName = $mixed[0];
                $controllerActionName = tryGet($mixed[1], "index");

            } else if (count($mixed) == 1) {

                if ($this->controllerClassName != __EMPTY__) {
                    $controllerClassName = $this->controllerClassName;
                    $controllerActionName = $mixed[0];
                } else {
                    $controllerClassName = $mixed[0];
                    $controllerActionName = "index";
                }
            }

            $controllerName = Strings::getClassBaseName($controllerClassName);
            $controllerPath = new FilePath("app/Controllers/$controllerName.php");

            if (!FS::exists($controllerPath)) {

                throw new IOException("Controller file does not exist in app/Controllers/ folder");
            }

            FS::import($controllerPath, true, true);

            $controllerInstance = app()->create($controllerClassName);

            $reflectionClass = new \ReflectionClass($controllerClassName);

            if (!$reflectionClass->hasMethod($controllerActionName)) {
                throw new ControllerException("The controller doesn't have a method named " . $controllerActionName);
            }

            $reflectionMethod = $reflectionClass->getMethod($controllerActionName);
            $closure = $reflectionMethod->getClosure($controllerInstance);
            $closure->bindTo($controllerInstance);

            $entryPoint = new EntryPoint($pathRegex, $method, $closure);

        } else if ($mixed instanceof \Closure) {
            $entryPoint = new EntryPoint($pathRegex, $method, $mixed);
        } else {
            throw new \InvalidArgumentException("Must be either a closure or an array with the class and the action method ");
        }

        if ($this->middlewares !== null) {
            $entryPoint->addMiddlewares($this->middlewares);
        }

        if ($this->lateMiddlewares !== null) {
            $entryPoint->addLateMiddlewares($this->lateMiddlewares);
        }

        $this->entryPoints[] = $entryPoint;

        return $entryPoint;
    }

    /**
     * Add a new entry point to the routes. This entry point is registed using the GET method.
     * 
     * @param string $pathRegex The path to use to create this entry point.
     * @param \Closure|array|null $mixed A closure to execute when this entry point is called. If provided an array instead,
     * then this method will try to create the controller class, inject all dependencies and call the passed method automatically.
     * If passed null, then it will try to use the default controller class previously registered. 
     * If not provided, then it will throw an exception.
     * @return EntryPoint The registered entry point.
     * @throws \Exception On entry point creation failure.
     */
    public function get(string $pathRegex, $mixed = null): EntryPoint
    {
        return $this->registerEntryPoint($pathRegex, "get", $mixed);
    }

    /**
     * Add a new entry point to the routes. This entry point is registed using the POST method.
     * 
     * @param string $pathRegex The path to use to create this entry point.
     * @param \Closure|array|null $mixed A closure to call when this entry point is called. If provided an array instead,
     * then this method will try to create the controller class, inject all dependencies and call the passed method automatically.
     * If passed null, then it will try to use the default controller class previously registered. 
     * If not provided, then it will throw an exception.
     * @return EntryPoint The registered entry point.
     * @throws \Exception On entry point creation failure.
     */
    public function post(string $pathRegex, $mixed = null): EntryPoint
    {
        return $this->registerEntryPoint($pathRegex, "post", $mixed);
    }

    /**
     * Match any route after no other catched the given path. This entry point is registed using the GET and POST method.
     * 
     * @param \Closure|array|null $mixed A closure to call when this entry point is called. If provided an array instead,
     * then this method will try to create the controller class, inject all dependencies and call the passed closure or method automatically.
     * If passed null, then it will try to use the default controller class previously registered. 
     * If not provided, then it will throw an exception.
     * @return EntryPoint The registered entry point.
     * @throws \Exception On entry point creation failure.
     */
    public function any($mixed = null): EntryPoint
    {
        return $this->registerEntryPoint("*", "*", $mixed);
    }

    /**
     * Executes a request. The application will try to match all routes against the given request, and stop once
     * at least one matches. The application context cannot go out of the scope of this function call.
     * 
     * @param Request $request The HTTP request about to be processed.
     * @return void
     */
    public function process(Request $request): void
    {
        foreach ($this->entryPoints as $entryPoint) {
            if ($entryPoint->match($request)) {
                $entryPoint->executeEntryPoint($request);
            }
        }
    }
}
