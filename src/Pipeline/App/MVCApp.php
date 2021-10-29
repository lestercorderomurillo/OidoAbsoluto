<?php

namespace Pipeline\App;

use Pipeline\App\App;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\PypeEngine\PypeCompiler;
use Pipeline\PypeEngine\PypeViewRenderer;
use Pipeline\PypeEngine\Boot\SCSSCompiler;

use function Pipeline\Kernel\Dependency;

abstract class MVCApp extends App
{
    protected function internalConfigure(): void
    {
        //$this->injectScopeddependency(ViewRenderer::class, ["a" => "b", "d" => "e"]);
        //$this->injectTrasientdependency(ViewRenderer::class, new ViewRenderer());

        $this->getDependencyManager()->add(WebServer::class, new WebServer());
        $this->getDependencyManager()->add(PypeCompiler::class, new PypeCompiler());
        $this->getDependencyManager()->add(PypeViewRenderer::class, new PypeViewRenderer());

        if (!$this->getRuntimeEnvironment()->inProductionMode()) {
            $asset_compiler = new SCSSCompiler();
            $asset_compiler->compileProjectStylesheets();
        }
    }

    protected function initializeApplication(): void
    {
        dependency(WebServer::class)->run();
    }
}
