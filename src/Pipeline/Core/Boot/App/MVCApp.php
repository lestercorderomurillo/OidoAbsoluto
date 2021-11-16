<?php

namespace Pipeline\Core\Boot\App;

use Pipeline\Core\Dependency;
use Pipeline\Core\Boot\App;
use Pipeline\Core\DI;
use Pipeline\Core\Lifetime;
use Pipeline\DOM\Compiler;
use Pipeline\DOM\PypeDOM;
use Pipeline\DOM\SCSSCompiler;
use Pipeline\DOM\ViewRenderer;
use Pipeline\HTTP\Server\WebServer;

abstract class MVCApp extends App
{
    protected function servicesInjection(): void
    {
        DI::inject(Lifetime::RequestScoped, PypeDOM::class);
        DI::inject(Lifetime::RequestScoped, WebServer::class);
        DI::inject(Lifetime::RequestScoped, Compiler::class);
        DI::inject(Lifetime::RequestScoped, ViewRenderer::class);

        $asset_compiler = new SCSSCompiler();

        if ($this->getRuntimeEnvironment()->inProductionMode()) {
            if($asset_compiler->checkForMissingBuildStylesheet()){
                $asset_compiler->compileProjectStylesheets();
            }
        }else{
            $asset_compiler->compileProjectStylesheets();
        }

    }

    protected function initializeApplication(): void
    {
        DI::getDependency(WebServer::class)->run();
    }
}
