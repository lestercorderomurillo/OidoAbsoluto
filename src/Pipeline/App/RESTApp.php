<?php

namespace Pipeline\App;

use Pipeline\App\App;
use Pipeline\HTTP\Server\WebServer;

abstract class RESTApp extends App
{
    protected function initializeApplication(): void
    {
        /*$web_server = new WebServer();
        $web_server->run();*/

        exit();
    }
}