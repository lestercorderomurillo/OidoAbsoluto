<?php

namespace Pipeline\HTTP\Common;

use Pipeline\HTTP\Message;

abstract class Request extends Message
{
    protected string $protocol;
    protected string $host;
    protected string $path;
    protected string $method;
    protected array $parameters;
    
    protected $user;
    protected $password;

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
