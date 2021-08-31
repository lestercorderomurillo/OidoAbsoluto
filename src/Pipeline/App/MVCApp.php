<?php

namespace Pipeline\App;

use Pipeline\App\App;
use Pipeline\Pype\AssetCompiler;
use Pipeline\Pype\ViewRenderer;
use Pipeline\HTTP\Server\WebServer;
use function Pipeline\Accessors\Dependency;

abstract class MVCApp extends App
{
    protected function internalConfigure(): void
    {
        $this->getDependencyManager()->add(WebServer::class, new WebServer());
        $this->getDependencyManager()->add(ViewRenderer::class, new ViewRenderer());

        if (!$this->getRuntimeEnvironment()->inProductionMode()) {
            $asset_compiler = new AssetCompiler();
            $asset_compiler->compileProjectStylesheets();
        }
    }

    protected function initializeApplication(): void
    {
        Dependency(WebServer::class)->run();
    }
}
