<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use VIP\App\App;
use VIP\App\MVCApp;
use VIP\Adapter\MySQLAdapter;
use VIP\Database\SQLDatabase;
use VIP\Database\Common\ConnectionString;

class OidoAbsolutoApp extends MVCApp
{
    protected function prepareApp() : void
    {
        $connection_string = new ConnectionString(
            $this->configuration["connection"]["server"],
            $this->configuration["connection"]["database"],
            $this->configuration["connection"]["user"],
            $this->configuration["connection"]["pass"]
        );

        $this->services->getContainer()->add(new SQLDatabase(new MySQLAdapter(), $connection_string));
    }
}

App::deployNow($app = new OidoAbsolutoApp());