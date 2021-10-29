<?php

namespace Pipeline\Core\Boot;

use Pipeline\HTTP\Message;
use Pipeline\Core\Boot\ActionsBase;
use Pipeline\Core\Facade\RequestHandlerInterface;

abstract class MiddlewareBase extends ActionsBase implements RequestHandlerInterface
{
    public abstract function handle($message): Message;
}
