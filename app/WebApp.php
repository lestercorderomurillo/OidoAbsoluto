<?php

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use Pipeline\App\MVCApp;
use Pipeline\Adapter\MySQLAdapter;
use Pipeline\Core\Boot\AppBase;
use Pipeline\Core\Dependency;
use Pipeline\Database\SQLDatabase;
use Pipeline\Database\Common\ConnectionString;
use function Pipeline\Kernel\configuration;

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

        $this->getDependencyTable()->addInjectable(Dependency::ContextScoped, SQLDatabase::class, [new MySQLAdapter(), $connection_string]);
    }
}

AppBase::deploy(new WebApp());