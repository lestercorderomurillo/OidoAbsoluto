<?php

namespace Pipeline\Core\Boot\App;

use Pipeline\Core\Boot\App;

abstract class RESTApp extends App
{
    protected function initializeApplication(): void
    {
        exit();
    }
}