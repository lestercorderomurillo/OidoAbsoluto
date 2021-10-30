<?php

namespace Pipeline\App;

use Pipeline\Core\Boot\AppBase;

abstract class RESTApp extends AppBase
{
    protected function initializeApplication(): void
    {
        exit();
    }
}