<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP\Server;

use Cosmic\Core\Abstracts\Controller;
use Cosmic\Core\Interfaces\ResponseGenerator;
use Cosmic\Core\Types\JSON;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Exceptions\EntryPointException;
use Cosmic\HTTP\Exceptions\InvalidParameterBinding;
use Cosmic\Utilities\Collections;

/**
 * This class represents a entry point. 
 * 
 * A entry point is just a HTTP route that can be matched against when 
 * the user writes an URL in the browser or performs a simple request.
 */
class EntryPoint extends Controller
{
    /**
     * @var string $pathRegex The regex used to match the entry point on the router.
     */
    private string $pathRegex;

    /**
     * @var string $method Can be either 'get' or 'post'.
     */
    private string $method;

    /**
     * @var string[] $middlewares A collection of middlewares for this entry point.
     * Must be already the runtime objects, not the class definition.
     */
    private array $middlewares;

    /**
     * @var string[] $middlewares A collection of middlewares. Works the same as in $middlewares, but
     * their handle action will  be executed after the request has been already sent to the client.
     */
    private array $lateMiddlewares;

    /**
     * @var callable $callable A function that is called when the entry point is matched.
     */
    private $callable;

    /**
     * Constructor. Creates a new entry point instance.
     * 
     * @param string $pathRegex The regular expression to match the current entry point.
     * @param string $method The method used to match the current entry point. Can be "get" or "post" only.
     * @param callable $callable The regular expression to match the current entry point.
     * @param array $middlewares The collection of middlewares to execute before the request has been sent to the closure.
     * @param array $lateMiddlewares The collection of middlewares to execute after the response has been sent to the client.
     */
    public function __construct(string $pathRegex, string $method, callable $callable, array $middlewares = [], array $lateMiddlewares = [])
    {
        $this->pathRegex = $pathRegex;
        $this->method = $method;
        $this->callable = $callable;
        $this->middlewares = $middlewares;
        $this->lateMiddlewares = $lateMiddlewares;
    }

    /**
     * Add a new middleware class to the list of middlewares.
     * 
     * @param string[]|string $middlewares A middleware class, or a collection of middleware classes.
     * @return void
     */
    public function addMiddlewares($middlewares): void
    {
        if (Collections::isList($middlewares)) {
            foreach ($middlewares as $middleware) {
                $this->middlewares[] = $middleware;
            }
        } else {
            $this->middlewares[] = $middlewares;
        }
    }

    /**
     * Add a new late middleware class to the list of middlewares.
     * 
     * @param string[]|string $middlewares A middleware class, or a collection of middleware classes.
     * @return void
     */
    public function addLateMiddlewares($middlewares): void
    {
        if (Collections::isList($middlewares)) {
            foreach ($middlewares as $middleware) {
                $this->lateMiddlewares[] = $middleware;
            }
        } else {
            $this->lateMiddlewares[] = $middlewares;
        }
    }

    /**
     * Remove the given middlewares from the list of scheduled middlewares.
     * 
     * @param string[]|string $middlewares A middleware class, or a collection of middleware classes.
     * @return void
     */
    public function excludeMiddlewares($middlewares): void
    {
        $this->middlewares = array_diff($this->middlewares, Collections::normalizeToList($middlewares));
    }

    /**
     * Remove the given middlewares from the list of scheduled late middlewares. (After the request is sent)
     * 
     * @param string[]|string $middlewares A middleware class, or a collection of middleware classes.
     * @return void
     */
    public function excludeLateMiddlewares($middlewares): void
    {
        $this->lateMiddlewares = array_diff($this->lateMiddlewares, Collections::normalizeToList($middlewares));
    }

    /**
     * Return the stored action to execute for this entry point.
     * 
     * @return \Closure
     */
    public function getClosure(): \Closure
    {
        return $this->closure;
    }

    /**
     * Return true if the entry point matches for this request.
     * 
     * @return bool
     */
    public function match(Request $request): bool
    {
        if ($this->pathRegex == "*") {

            if ($this->method == "*") {

                return true;
            } else if (strtolower($this->method) == strtolower($request->getMethod())) {

                return true;
            }
        }

        return (strtolower($this->method) == strtolower($request->getMethod())
            && strtolower($this->pathRegex) == strtolower($request->getAction()));
    }

    /**
     * Execute this entry point using the closure without arguments.
     * Return the data from the function unaltered. Later on will be parsed to a valid HTTP response.
     * 
     * @return mixed
     */
    /*private function executeUsingNoBinding()
    {
        $entryPoint = $this->closure;
        return $entryPoint();
    }*/

    /**
     * Execute this entry point using reflection to try to match all the formData and dependencies into the closure arguments.
     * Return the data from the function unaltered. Later on will be parsed to a valid HTTP response.
     * 
     * @param Request $request The request to execute.
     * 
     * @return mixed
     * @throws InvalidParameterBinding
     */
    /*private function executeUsingAutowire(Request $request)
    {
        $formData = $request->getFormData();
        $outputParameters = [];

        $reflectionFunction = new \ReflectionFunction($this->closure);
        $parameters = $reflectionFunction->getParameters();

        foreach ($parameters as $parameter) {

            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType) {

                $typeName = $type->getName();

                if (app()->has($typeName)) {

                    $outputParameters[] = app()->get($typeName);
                } else {

                    if (isset($formData[$parameter->getName()])) {

                        $outputParameters[] = $formData[$parameter->getName()];
                    } else {

                        try {
                            $default = $parameter->getDefaultValue();
                        } catch (\Exception $e) {

                            switch ($typeName) {
                                case 'bool':
                                    $default = false;
                                    break;
                                case 'int':
                                    $default = 0;
                                    break;
                                case 'array':
                                    $default = [];
                                    break;
                                case 'string':
                                    $default = __EMPTY__;
                                    break;
                                default:
                                    $default = null;
                                    break;
                            }
                        }

                        switch ($typeName) {
                            case 'bool':
                                $value = $default;
                                break;
                            case 'int':
                                $value = $default;
                                break;
                            case 'array':
                                $value = $default;
                                break;
                            case 'string':
                                $value = $default;
                                break;
                            default:
                                $value = null;
                                break;
                        }

                        $outputParameters[] = $value;
                    }
                }
            } else {

                throw new InvalidParameterBinding("The parameter '$parameter' must have a type in the method definition");
            }
        }

        $entryPoint = $this->closure;
        return $entryPoint(...$outputParameters);
    }*/

    /**
     * Delegate the current request to the corresponding function.
     * Can be either using request binding or parameter binding.
     * 
     * @param Request $request The request to execute.
     * 
     * @return mixed
     * @throws EntryPointException
     */
    /*private function delegate(Request $request)
    {
        $reflectionFunction = new \ReflectionFunction($this->closure);

        if ($reflectionFunction->getNumberOfParameters() == 0) {

            return $this->executeUsingNoBinding();
        }

        return $this->executeUsingAutowire($request);
    }*/

    /**
     * Execute this entry point. Will run all the middlewares and late middlewares.
     * The program flow context will not escape this function. Once a entry point is executed,
     * the php callstack will only go down from this instance for simpler debugging and development. 
     * 
     * @param Request $request The request to execute.
     * 
     * @return void
     */
    public function executeEntryPoint(Request $request): void
    {
        foreach ($this->middlewares as $middleware) {

            if ($request != null && !$request instanceof Response) {
                $middlewareInstance = create($middleware);
                $request = $middlewareInstance->handle($request);
            }
        }

        if ($request != null) {

            if ($request instanceof Response) {

                $request->send();

            }else{

                $response = create($this->callable);

                if (!isset($response) && ob_get_contents() == false) {

                    $response = $this->content("200 OK");

                }else if ($response instanceof ResponseGenerator) {

                    $response = $response->toResponse();

                } else if (is_string($response) || is_int($response)) {

                    $response = $this->content($response);

                } else if (method_exists($response, "toString")) {

                    $response = $this->content($response->toString());

                } else if ($response instanceof JSON || is_array($response) || is_object($response)) {

                    $response = $this->JSON($response);
                }

                if ($response != null) {
                    $response->send();
                }

                foreach ($this->lateMiddlewares as $lateMiddleware) {

                    if ($response != null) {
                        $lateMiddlewareInstance = create($lateMiddleware);
                        $response = $lateMiddlewareInstance->handle($response);
                    }
                }
            } 
        }

        exit();
    }
}
