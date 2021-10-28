<?php

namespace Pipeline\Core;

use Pipeline\HTTP\Message;
use Pipeline\HTTP\Common\BaseActions;
use Pipeline\Core\Facade\RequestHandlerInterface;

abstract class Middleware extends BaseActions implements RequestHandlerInterface
{
    public abstract function handle($request): Message;
}
