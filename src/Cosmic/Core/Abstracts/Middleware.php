<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

use Cosmic\HTTP\Request;
use Cosmic\Core\Abstracts\Controller;
use Cosmic\Core\Interfaces\RequestHandlerInterface;

/**
 * The basic abstract class for all middlewares.
 */
abstract class Middleware extends Controller implements RequestHandlerInterface
{
    /**
     * @inheritdoc
     */
    public abstract function handle(Request $request): Request;
}
