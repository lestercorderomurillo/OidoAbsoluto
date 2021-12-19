<?php

namespace Cosmic\Core\Boot;

use Cosmic\HTTP\Request;
use Cosmic\Core\Boot\Actions;
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
