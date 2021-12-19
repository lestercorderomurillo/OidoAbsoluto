<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Cosmic\Core\Boot\Lifetime;
use Cosmic\Core\Applications\MVCApplication;
use Cosmic\Database\SQLDatabase;
use Cosmic\Database\Driver\MySQLDriver;
use Cosmic\Database\Common\ConnectionString;
use Cosmic\Database\Common\MySQLConnectionString;
use function Cosmic\Core\Boot\deploy;

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

        $this->inject(Lifetime::ContextLifetime, SQLDatabase::class, [new MySQLDriver(), $this->connectionString]);
    }
}

/** Starts the application **/
deploy(new WebApplication());