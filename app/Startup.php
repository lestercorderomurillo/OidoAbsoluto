<?php

/** 
 * --- Startup -----------------------------------------------------------------------------
 * 
 * The class in this file is responsible for the application configuration and managing of dependencies.
 * Developers can inject all the required classes for a given lifecycle and use them as needed in controllers.
 * 
 * ----------------------------------------------------------------------------------------
 */

require_once(dirname(__DIR__) . "/vendor/autoload.php");
require_once(dirname(__DIR__) . "/src/Cosmic/Core/Bootstrap/Kernel.php");

use Cosmic\Core\Applications\MVCApplication;
use Cosmic\ORM\Common\ConnectionString;
use Cosmic\ORM\Driver\MySQLDriver;
use Cosmic\ORM\Databases\SQL\SQLDatabase;
use Cosmic\ORM\Databases\SQL\SQLConnectionString;

class WebApplication extends MVCApplication
{
    private ConnectionString $connectionString;

    protected function onConfiguration(): void
    {
        parent::onConfiguration();
        $this->connectionString = new SQLConnectionString();
    }

    protected function onServicesInjection(): void
    {
        parent::onServicesInjection();
        $this->injectSingleton(SQLDatabase::class, [new MySQLDriver(), $this->connectionString]);
    }
}

deploy(new WebApplication());