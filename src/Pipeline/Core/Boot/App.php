<?php

namespace Pipeline\Core\Boot;

use Pipeline\Core\DI;
use Pipeline\Core\Environment;
use Pipeline\Core\Lifetime;
use Pipeline\Core\Logger;

require_once(dirname(__DIR__, 2) . "/Core/Kernel.php");

abstract class App
{
    public static $app;

    private Environment $environment;

    protected abstract function configure(): void;
    protected abstract function servicesInjection(): void;
    protected abstract function initializeApplication(): void;

    public function __construct()
    {
        self::$app = $this;
        define("__ROOT__", $this->getRootDirectory());
        DI::inject(Lifetime::RequestScoped, Logger::class);
    }

    public function createHostEnvironment()
    {
        $this->environment = new Environment();
        define("__URL__", $this->environment->getConfiguration("application.url"));
        define("__WEB_NAME__", $this->environment->getConfiguration("application.webroot"));
    }

    public function getRuntimeEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getRootDirectory(): string
    {
        return str_replace("\\", "/", dirname(__DIR__, 4) . "\\");
    }

    public static function deploy($app): void
    {
        $app->createHostEnvironment();
        $app->servicesInjection();
        $app->configure();
        $app->initializeApplication();
    }
}
