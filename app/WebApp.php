<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Pipeline\App\App;
use Pipeline\App\MVCApp;
use Pipeline\Adapter\MySQLAdapter;
use Pipeline\Database\SQLDatabase;
use Pipeline\Database\Common\ConnectionString;
use function Pipeline\Navigate\configuration;

class WebApp extends MVCApp
{
    protected function configure(): void
    {
        $connection_string = new ConnectionString(
            configuration("database.mysql.server"),
            configuration("database.mysql.db"),
            configuration("database.mysql.user"),
            configuration("database.mysql.pass")
        );

        $this->getDependencyManager()->add("Db", new SQLDatabase(new MySQLAdapter(), $connection_string));
    }
}

App::deploy(new WebApp());