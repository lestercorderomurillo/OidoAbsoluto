<?php

namespace Pipeline\App;


use Pipeline\Core\Dependency;
use Pipeline\Core\Boot\AppBase;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\PypeEngine\PypeCompiler;
use Pipeline\PypeEngine\PypeViewRenderer;
use Pipeline\PypeEngine\Boot\SCSSCompiler;
use function Pipeline\Kernel\dependency;

abstract class MVCApp extends AppBase
{
    protected function internalConfigure(): void
    {
        $this->getDependencyTable()->addInjectable(Dependency::RequestScoped, WebServer::class);
        $this->getDependencyTable()->addInjectable(Dependency::RequestScoped, PypeCompiler::class);
        $this->getDependencyTable()->addInjectable(Dependency::RequestScoped, PypeViewRenderer::class);

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
        dependency(WebServer::class)->run();
    }
}
