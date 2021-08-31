<?php

namespace Pipeline\Middleware;

use Pipeline\HTTP\Message;
use Pipeline\HTTP\Common\BaseActions;
use Pipeline\HTTP\Common\RequestHandlerInterface;

abstract class Middleware extends BaseActions implements RequestHandlerInterface
{
    public abstract function handle($request): Message;
}
