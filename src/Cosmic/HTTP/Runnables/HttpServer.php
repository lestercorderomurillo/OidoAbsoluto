<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP\Runnables;

use Cosmic\FileSystem\FS;
use Cosmic\Core\Interfaces\RunnableInterface;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Router;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 */
class HttpServer implements RunnableInterface
{
    private Router $router;
    private Request $request;

    public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->request = $request;
    }
    /**
     * @inheritdoc
     */
    public function run(): int
    {
        FS::import("app/Routes.php");
        $this->router->process($this->request);

        return 0;
    }
}
