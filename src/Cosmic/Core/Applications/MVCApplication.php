<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Applications;

use Cosmic\Core\Abstracts\Application;
use Cosmic\Core\Logger;
use Cosmic\HTTP\Runnables\HttpServer;
use Cosmic\HTTP\Providers\HttpServerProvider;
use Cosmic\HTTP\Router;
use Cosmic\VDOM\Compiler;

/**
 * This class represents a MVC application.
 */
abstract class MVCApplication extends Application
{
    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        parent::boot();
        
        HttpServerProvider::default();

        app()->singleton(Compiler::class);

        /*$this->primitive(Request::class, Request::intercept());
        $this->singleton(Session::class);
        $this->singleton(Router::class);
        $this->singleton(Compiler::class);
        $this->singleton(Bindings::class);
        $this->singleton(DOMServer::class);
        $this->singleton(HttpServer::class);*/
    }

    /**
     * @inheritdoc
     */
    public function dispose(): void
    {
    }

    /**
     * Run this application.
     */
    public function run(): int
    {
        $httpServer = create(HttpServer::class);
        return $httpServer->run();
    }
}
