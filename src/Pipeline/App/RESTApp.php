<?php

namespace Pipeline\App;

use Pipeline\App\App;

abstract class RESTApp extends App
{
    protected function initializeApplication(): void
    {
        exit();
    }
}