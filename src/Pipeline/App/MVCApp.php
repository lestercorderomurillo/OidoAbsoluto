<?php

namespace Pipeline\App;

use Pipeline\App\App;
use Pipeline\Component\Template;
use Pipeline\HTTP\Server\Session;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\Renderer\SCSSPreProcessor;
use Pipeline\Renderer\ViewRenderer;

use function Pipeline\Accessors\Dependency;

abstract class MVCApp extends App
{
    protected function internalConfigure(): void
    {
        Session::__static();
        $this->getDependencyManager()->add(WebServer::class, new WebServer());

        Template::__static();
        $this->getDependencyManager()->add(ViewRenderer::class, new ViewRenderer());

        if (!$this->getRuntimeEnvironment()->inProductionMode()) {
            $preprocessor = new SCSSPreProcessor();
            $preprocessor->compileProjectStylesheets();
        }
    }

    protected function initializeApplication(): void
    {
        Dependency(WebServer::class)->run();
    }
}
