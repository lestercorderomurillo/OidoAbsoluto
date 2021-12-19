<?php

namespace Cosmic\Core\Applications;

use Cosmic\Core\Boot\Application;
use Cosmic\Core\Boot\Lifetime;
use Cosmic\HTTP\Server\WebServer;
use Cosmic\HTTP\Server\Router;
use Cosmic\HTTP\Server\Session;

/**
 * This class represents a MVC application.
 */
abstract class MVCApplication extends Application
{
    /**
     * @inheritdoc
     */
    protected function onConfiguration(): void
    {
    }

    /**
     * @inheritdoc
     */
    protected function onServicesInjection(): void
    {
        $this->inject(Lifetime::RequestLifetime, Session::class);
        $this->inject(Lifetime::RequestLifetime, Router::class);
        //$this->inject(Lifetime::RequestLifetime, CosmicDOM::class);
        $this->inject(Lifetime::RequestLifetime, WebServer::class);

        /*$asset_compiler = new SCSSCompiler();

        if ($this->getRuntimeEnvironment()->inProductionMode()) {
            if($asset_compiler->checkForMissingBuildStylesheet()){
                $asset_compiler->compileProjectStylesheets();
            }
        }else{
            $asset_compiler->compileProjectStylesheets();
        }*/
    }

    /**
     * @inheritdoc
     */
    protected function onInitialization(): void
    {
        $this->get(WebServer::class)->run();
    }
}
