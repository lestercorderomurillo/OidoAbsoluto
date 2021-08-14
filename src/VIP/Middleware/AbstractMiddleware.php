<?php

namespace VIP\Middleware;

use VIP\HTTP\Common\Request;

abstract class AbstractMiddleware
{
    public abstract function handle(Request $request) : Request;
}
