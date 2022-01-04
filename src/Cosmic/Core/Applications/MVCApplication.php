<?php

namespace Cosmic\Core\Applications;

use Cosmic\Core\Bootstrap\Application;
use Cosmic\Binder\DOM;
use Cosmic\Binder\Compiler;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Server\WebServer;
use Cosmic\HTTP\Server\DOMServer;
use Cosmic\HTTP\Server\Router;
use Cosmic\HTTP\Server\Session;

/**
 * This class represents a MVC application.
 */
abstract class MVCApplication extends Application
{
    /**
     * @var bool $compileStylesheet On true, the server will compile the stylesheets on the onInitialization() method.
     */
    private bool $compileStylesheet = false;

    /**
     * @inheritdoc
     */
    protected function onConfiguration(): void
    {
        if(FileSystem::exists(new File(__CONTENT__ . "output/build.css"))){
            $this->compileStylesheet = true;
        }
    }

    /**
     * @inheritdoc
     */
    protected function onServicesInjection(): void
    {
        $this->injectPrimitive(Request::class, Request::intercept());
        $this->injectSingleton(Session::class);
        $this->injectSingleton(Router::class);
        $this->injectSingleton(Compiler::class);
        $this->injectSingleton(DOM::class);
        $this->injectSingleton(DOMServer::class);
        $this->injectSingleton(WebServer::class);
    }

    /**
     * @inheritdoc
     */
    protected function onInitialization(): void
    {
        $this->get(DOMServer::class)->run();

        if($this->compileStylesheet){
            $this->get(Compiler::class)->compileStylesheet();
        }

        $this->get(WebServer::class)->run();
    }
}
