<?php

namespace Pipeline\Core\Boot;

use Pipeline\HTTP\Message;
use Pipeline\Core\Boot\Actions;
use Pipeline\Core\Interfaces\RequestHandlerInterface;

abstract class Middleware extends Actions implements RequestHandlerInterface
{
    public abstract function handle($message): Message;
}
