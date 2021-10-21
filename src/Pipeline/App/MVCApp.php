<?php

namespace Pipeline\App;

use Pipeline\App\App;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\PypeEngine\PypeCompiler;
use Pipeline\PypeEngine\PypeViewRenderer;
use Pipeline\PypeEngine\Inproc\SCSSCompiler;
use Pipeline\PypeEngine\PypeTemplateBatch;

use function Pipeline\Accessors\Dependency;

abstract class MVCApp extends App
{
    protected function internalConfigure(): void
    {
        //$this->injectScopedDependency(ViewRenderer::class, ["a" => "b", "d" => "e"]);
        //$this->injectTrasientDependency(ViewRenderer::class, new ViewRenderer());
        $this->getDependencyManager()->add(WebServer::class, new WebServer());
        //$this->getDependencyManager()->add(ViewRenderer::class, new ViewRenderer());
        $this->getDependencyManager()->add(PypeCompiler::class, new PypeCompiler());
        $this->getDependencyManager()->add(PypeViewRenderer::class, new PypeViewRenderer());
        $this->getDependencyManager()->add(PypeTemplateBatch::class, new PypeTemplateBatch());

        if (!$this->getRuntimeEnvironment()->inProductionMode()) {
            $asset_compiler = new SCSSCompiler();
            $asset_compiler->compileProjectStylesheets();
        }
    }

    protected function initializeApplication(): void
    {
        Dependency(WebServer::class)->run();
    }
}
