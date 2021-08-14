<?php

namespace VIP\App;

use VIP\App\App;
use VIP\HTTP\Common\Router;
use VIP\HTTP\Server\IncomingRequest;
use VIP\Renderer\ViewRenderer;
use VIP\Renderer\PreProcessor;

abstract class MVCApp extends App
{
    protected function prepareAppInternal(): void
    {
        if ($this->inDevelopmentMode()) {
            $this->services->getContainer()->add(new PreProcessor($this->configuration["debugging"]["process_scss"]));
        }

        $this->services->getContainer()->add(new ViewRenderer($this->configuration["application"]["name"], $this->configuration["debugging"]["render_log"]));
    }

    protected function processRequest(): void
    {
        $router = new Router();
        $router->handle(new IncomingRequest());
    }

    protected function instanceRun(): void
    {
        $this->prepareAppInternal();
        $this->prepareApp();
        $this->services->runForAllServices();
        $this->processRequest();
        exit();
    }
}
