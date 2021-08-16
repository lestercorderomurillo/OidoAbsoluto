<?php

namespace VIP\App;

use Psr\Log\LoggerAwareTrait;
use VIP\Core\BaseObject;
use VIP\Service\ServiceManager;
use VIP\HTTP\Common\Session;
use VIP\Logger\Logger;

abstract class App extends BaseObject
{
    use LoggerAwareTrait;

    public static $app;
    public $configuration;
    
    public static $failure = false;

    protected Session $session;
    protected ServiceManager $services;

    protected abstract function instanceRun(): void;
    protected abstract function prepareApp(): void;
    protected abstract function prepareAppInternal(): void;
    protected abstract function processRequest(): void;

    public function __construct()
    {
        define("__ROOT__", str_replace("\\", "/",dirname(__DIR__, 3) . "\\"));
        $this->configuration = require_once(__ROOT__ . "/app/configuration.php");
        define("__URL__", $this->configuration["application"]["url"]);
        define("__WEB_NAME__", $this->configuration["application"]["content"]);

        $this->setLogger(new Logger());

        $this->session = new Session();
        $this->services = new ServiceManager();
    }

    public static function deployNow($App): void
    {
        App::$app = $App;
        App::$app->instanceRun();
    }

    public function inDevelopmentMode(): bool
    {
        return $this->configuration["application"]["development"];
    }

    public function hasHotswapEnabled(): bool
    {
        return $this->configuration["settings"]["hotswap"];
    }

    public function setFailure(): void
    {
        self::$failure = true;
    }

    public function hasFailed(): bool
    {
        return self::$failure;
    }

    public function shouldLogNormalEvents(): bool
    {
        return $this->configuration["settings"]["logging"];
    }

    public function shouldLogErrorEvents(): bool
    {
        return $this->configuration["settings"]["errorLogging"];
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getServices(): ServiceManager
    {
        return $this->services;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}
