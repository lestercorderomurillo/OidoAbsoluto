<?php

namespace Cosmic\Core\Bootstrap;

use Cosmic\HTTP\Request;
use Cosmic\Core\Bootstrap\Actions;
use Cosmic\Core\Interfaces\RequestHandlerInterface;

/**
 * The basic abstract class for all middlewares.
 */
abstract class Middleware extends Actions implements RequestHandlerInterface
{
    /**
     * @inheritdoc
     */
    public abstract function handle(Request $request): Request;
}
