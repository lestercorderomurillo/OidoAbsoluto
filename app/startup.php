<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Pipeline\App\App;
use Pipeline\App\MVCApp;
use Pipeline\Adapter\MySQLAdapter;
use Pipeline\Database\SQLDatabase;
use Pipeline\Database\Common\ConnectionString;
use function Pipeline\Accessors\Configuration;

class OidoAbsolutoApp extends MVCApp
{
 
    protected function configure(): void
    {
        $connection_string = new ConnectionString(
            Configuration("database.mysql.server"),
            Configuration("database.mysql.db"),
            Configuration("database.mysql.user"),
            Configuration("database.mysql.pass")
        );

        $this->getDependencyManager()->add("Db", new SQLDatabase(new MySQLAdapter(), $connection_string));
    }
}

App::deploy(new OidoAbsolutoApp());