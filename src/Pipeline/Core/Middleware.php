<?php

namespace Pipeline\Core;

use Pipeline\HTTP\Message;
use Pipeline\Core\ActionsBase;
use Pipeline\Core\Facade\RequestHandlerInterface;

abstract class Middleware extends ActionsBase implements RequestHandlerInterface
{
    public abstract function handle($request): Message;
}
