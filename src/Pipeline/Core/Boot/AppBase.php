<?php

namespace Pipeline\Core\Boot;

use Pipeline\Core\Dependency;
use Pipeline\Core\DependencyTable;
use Pipeline\Core\Environment;
use Pipeline\Trace\Logger;

require_once(dirname(__DIR__, 2) . "/Core/Kernel.php");

abstract class AppBase
{
    public static $app;

    private Environment $environment;
    private DependencyTable $dependency_table;

    protected abstract function configure(): void;
    protected abstract function internalConfigure(): void;
    protected abstract function initializeApplication(): void;

    public function __construct()
    {
        define("__ROOT__", $this->getRootDirectory());
        self::$app = $this;

        $this->dependency_table = new DependencyTable();
        $this->dependency_table->addInjectable(Dependency::RequestScoped, Logger::class);
    }

    public function createHostEnvironment()
    {
        $this->environment = new Environment();
        define("__URL__", $this->environment->getConfiguration("application.url"));
        define("__WEB_NAME__", $this->environment->getConfiguration("application.webroot"));
    }

    public function getDependencyTable(): DependencyTable
    {
        return $this->dependency_table;
    }

    public function getRuntimeEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getRootDirectory(): string{
        return str_replace("\\", "/", dirname(__DIR__, 4) . "\\");
    }

    public static function deploy($app): void
    {
        $app->createHostEnvironment();
        $app->internalConfigure();
        $app->configure();
        $app->initializeApplication();
    }
}
