<?php

namespace VIP\HTTP\Common;

use VIP\HTTP\Common\Request;

class Route
{
    private string $path;
    private string $method;
    private string $controller_name;
    private string $action_name;
    private array $parameters;
    private array $middlewares;

    public function __construct(string $method, string $path, string $controller_name, string $action_name, array $parameters = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->controller_name = $controller_name;
        $this->action_name = $action_name;
        $this->parameters = $parameters;
        $this->middlewares = [];
    }

    public function setMiddlewares($array_or_one): void
    {
        if (is_array($array_or_one)) {
            foreach ($array_or_one as $middleware) {
                $this->middlewares[] = new $middleware();
            }
        } else {
            $this->middlewares[] = new $array_or_one();
        }
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function matchPath(Request $request): bool
    {
        return (strtolower($this->method) == strtolower($request->getMethod()) && strtolower($this->path) == strtolower($request->getPath()));
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getControllerName(): string
    {
        return $this->controller_name;
    }

    public function getActionName(): string
    {
        return $this->action_name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
