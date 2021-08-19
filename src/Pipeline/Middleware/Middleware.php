<?php

namespace Pipeline\Middleware;

use Pipeline\Logger\Logger;
use Pipeline\HTTP\Common\Request;

use function Pipeline\DependencyInjection\Dependency;

abstract class Middleware
{
    public function stopRequestForwarding()
    {
        Dependency(Logger::class)->debug("{0} catch the request. Will not forward to the next handler.", [static::class]);
    }

    public abstract function handle(Request $request): Request;
}
