<?php

namespace Pipeline\App;

use Pipeline\Component\Template;
use Pipeline\Core\Environment;
use Pipeline\DependencyInjection\DependencyManager;
use Pipeline\HTTP\Server\Session;
use Pipeline\Logger\Logger;

require_once(dirname(__DIR__) . "/Core/Accessors.php");

abstract class App
{
    public static $app;

    private Environment $environment;
    private DependencyManager $dependency_manager;

    protected abstract function configure(): void;
    protected abstract function internalConfigure(): void;
    protected abstract function initializeApplication(): void;

    public function __construct()
    {
        define("__ROOT__", str_replace("\\", "/", dirname(__DIR__, 3) . "\\"));
        App::$app = $this;

        $this->dependency_manager = new DependencyManager();
        $this->getDependencyManager()->add(Logger::class, new Logger());
    }

    public function createHost()
    {
        $this->environment = new Environment();

        define("__URL__", $this->environment->getConfiguration("application.url"));
        define("__WEB_NAME__", $this->environment->getConfiguration("application.webroot"));
    }

    public function getDependencyManager(): DependencyManager
    {
        return $this->dependency_manager;
    }

    public function getRuntimeEnvironment(): Environment
    {
        return $this->environment;
    }

    public static function deploy($app): void
    {
        $app->createHost();
        $app->internalConfigure();
        $app->configure();
        $app->initializeApplication();
    }
}
