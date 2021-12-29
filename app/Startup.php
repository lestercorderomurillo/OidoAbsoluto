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

use Cosmic\Core\Bootstrap\Lifetime;
use Cosmic\Core\Applications\MVCApplication;
use Cosmic\Database\SQLDatabase;
use Cosmic\Database\Driver\MySQLDriver;
use Cosmic\Database\Common\ConnectionString;
use Cosmic\Database\Common\MySQLConnectionString;
use function Cosmic\Core\Bootstrap\deploy;

class WebApplication extends MVCApplication
{
    private ConnectionString $connectionString;

    protected function onConfiguration(): void
    {
        parent::onConfiguration();

        $this->connectionString = new MySQLConnectionString();
    }

    protected function onServicesInjection(): void
    {
        parent::onServicesInjection();
        $this->injectSingleton(SQLDatabase::class, [new MySQLDriver(), $this->connectionString]);
    }
}

deploy(new WebApplication());