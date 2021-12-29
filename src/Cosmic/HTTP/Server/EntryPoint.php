<?php

namespace Cosmic\HTTP\Server;

use Cosmic\Core\Bootstrap\Actions;
use Cosmic\Core\Bootstrap\Middleware;
use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\Core\Types\JSON;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Exceptions\EntryPointException;
use Cosmic\HTTP\Exceptions\InvalidParameterBinding;
use Cosmic\Utilities\Collection;

use function Cosmic\Core\Bootstrap\app;

/**
 * This class represents a entry point. 
 * 
 * A entry point is just a HTTP route that can be matched against when 
 * the user writes an URL in the browser or performs a simple request.
 */
class EntryPoint extends Actions
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
     * @var Middleware[] $middlewares A collection of middlewares for this entry point.
     * Must be already the runtime objects, not the class definition.
     */
    private array $middlewares;

    /**
     * @var Middleware[] $middlewares A collection of middlewares. Works the same as in $middlewares, but
     * their handle action will  be executed after the request has been already sent to the client.
     */
    private array $lateMiddlewares;

    /**
     * @var \Closure $closure A function that is called when the entry point is matched.
     */
    private \Closure $closure;

    /**
     * Constructor.
     * 
     * @param string $pathRegex The regular expression to match the current entry point.
     * @param string $method The method used to match the current entry point. Can be "get" or "post" only.
     * @param \Closure $closure The regular expression to match the current entry point.
     * @param array $middlewares The collection of middlewares to execute before the request has been sent to the closure.
     * @param array $lateMiddlewares The collection of middlewares to execute after the response has been sent to the client.
     */
    public function __construct(string $pathRegex, string $method, \Closure $closure, array $middlewares = [], array $lateMiddlewares = [])
    {
        $this->pathRegex = $pathRegex;
        $this->method = $method;
        $this->closure = $closure;
        $this->middlewares = $middlewares;
        $this->lateMiddlewares = $lateMiddlewares;
    }

    /**
     * Add a new middleware class to the list of middlewares.
     * 
     * @param string $middleware A middleware class.
     * 
     * @return void
     */
    public function addMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Remove the passed middlewares from the list of registered middlewares.
     * 
     * @param string[]|string $middleware A middleware class, or a collection of middleware classes.
     * 
     * @return void
     */
    public function excludeMiddlewares($middlewares): void
    {
        $this->middlewares = array_diff($this->middlewares, Collection::normalize($middlewares));
    }

    /**
     * Sets the middlewares for this entry point. Should be only the class name.
     * This method is not expecting any instances.
     * 
     * @param Middleware[]|Middleware $middlewares The collection of middlewares. Accepts single instance.
     * 
     * @return void
     */
    public function setMiddlewares($middlewares): void
    {
        $this->middlewares = Collection::normalize($middlewares);
    }

    /**
     * Sets the lateMiddlewares for this entry point. 
     * This kind of middlewares are executed after the response has been sent to the end user.
     * 
     * @param Middleware[]|Middleware $middlewares The collection of middlewares. Accepts single instance.
     * 
     * @return void
     */
    public function setLateMiddlewares($lateMiddlewares): void
    {
        $this->lateMiddlewares = Collection::normalize($lateMiddlewares);
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
    private function executeUsingNoBinding()
    {
        $entryPoint = $this->closure;
        return $entryPoint();
    }

    /**
     * Execute this entry point using reflection to try to match all the formData and dependencies into the closure arguments.
     * Return the data from the function unaltered. Later on will be parsed to a valid HTTP response.
     * 
     * @param Request $request The request to execute.
     * 
     * @return mixed
     * @throws InvalidParameterBinding
     */
    private function executeUsingAutowire(Request $request)
    {
        $formData = $request->getFormData();
        $outputParameters = [];

        $reflectionFunction = new \ReflectionFunction($this->closure);
        $parameters = $reflectionFunction->getParameters();

        foreach ($parameters as $parameter) {

            $type = $parameter->getType();

            if($type instanceof \ReflectionNamedType && app()->has($type->getName())){

                $outputParameters[] = app()->get($type->getName());

            }else{

                if (isset($formData[$parameter->getName()])) {
                    $outputParameters[] = $formData[$parameter->getName()];
                } else {
                    throw new InvalidParameterBinding("Unmatched parameter '$parameter' from the route.");
                }

            }

        }

        $entryPoint = $this->closure;
        return $entryPoint(...$outputParameters);
    }

    /**
     * Delegate the current request to the corresponding function.
     * Can be either using request binding or parameter binding.
     * 
     * @param Request $request The request to execute.
     * 
     * @return mixed
     * @throws EntryPointException
     */
    private function delegate(Request $request)
    {
        $reflectionFunction = new \ReflectionFunction($this->closure);

        if ($reflectionFunction->getNumberOfParameters() == 0) {

            return $this->executeUsingNoBinding();

        }
        
        return $this->executeUsingAutowire($request);

    }

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

            if ($request != null) {
                $middlewareInstance = new $middleware();
                $request = $middlewareInstance->handle($request);
            }
        }

        if ($request != null) {

            if (!$request instanceof Response) {

                $response = null;
                $request = $this->delegate($request);

                if (!isset($request) && ob_get_contents() == false) {

                    $response = $this->content("Timestamp:" . time());

                } else if ($request instanceof Response) {

                    $response = $request;

                } else if ($request instanceof ResultGeneratorInterface) {

                    $response = $request->toResponse();

                } else if (is_string($request) || is_int($request)) {

                    $response = $this->content($request);

                } else if (method_exists($request, "toString")) {

                    $response = $this->content($request->toString());

                } else if ($request instanceof JSON || is_array($request) || is_object($request)) {

                    $response = $this->JSON($request);

                }

                if ($response != null) {
                    $response->send();
                }

                foreach ($this->lateMiddlewares as $lateMiddleware) {

                    if ($response != null) {
                        $lateMiddlewareInstance = new $lateMiddleware();
                        $response = $lateMiddlewareInstance->handle($response);
                    }
                }

            } else {

                $request->send();
            }
        }

        exit();
    }
}
