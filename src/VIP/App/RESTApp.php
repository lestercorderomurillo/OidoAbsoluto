<?php

namespace VIP\App;

use VIP\App\App;
use VIP\HTTP\Router;
use VIP\HTTP\Server\IncomingRequest;

abstract class RESTApp extends App
{
    protected function prepareAppInternal(): void
    {
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
