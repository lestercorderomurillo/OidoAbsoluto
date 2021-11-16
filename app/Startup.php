<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Pipeline\Core\Boot\App;
use Pipeline\Core\Boot\App\MVCApp;
use Pipeline\Core\DI;
use Pipeline\Core\Lifetime;
use Pipeline\Database\SQLDatabase;
use Pipeline\Database\Driver\MySQLDriver;
use Pipeline\Database\Common\ConnectionString;
use function Pipeline\Kernel\configuration;

class PypelineApplication extends MVCApp
{
    protected function configure(): void
    {
        $connection_string = new ConnectionString(
            configuration("database.mysql.server"),
            configuration("database.mysql.db"),
            configuration("database.mysql.user"),
            configuration("database.mysql.pass")
        );

        DI::inject(Lifetime::ContextScoped, SQLDatabase::class, [new MySQLDriver(), $connection_string]);
    }
}

App::deploy(new PypelineApplication());