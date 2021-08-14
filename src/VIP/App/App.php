<?php

namespace VIP\App;

use VIP\Core\BaseObject;
use VIP\HTTP\Common\Session;
use VIP\Service\ServiceManager;

abstract class App extends BaseObject
{
    public static $app;
    public $configuration;

    protected Session $session;
    protected ServiceManager $services;

    protected abstract function instanceRun(): void;
    protected abstract function prepareApp(): void;
    protected abstract function prepareAppInternal(): void;
    protected abstract function processRequest(): void;

    public function __construct()
    {
        define("__RDIR__", dirname(__DIR__, 3));
        $this->configuration = require_once(__RDIR__ . "/app/configuration.php");
        $this->defineConstants();

        $this->session = new Session();
        $this->services = new ServiceManager();
    }

    public static function deployNow($App): void
    {
        App::$app = $App;
        App::$app->instanceRun();
    }

    protected function inDevelopmentMode(): bool
    {
        return $this->configuration["application"]["development"];
    }

    protected function defineConstants(): void
    {
        define("__URL__", $this->configuration["application"]["url"]);
        define("__WEB__", __URL__ . $this->configuration["application"]["content"] . "/");
        define("__FWEB__", $this->configuration["application"]["content"]);
        define("__LWEB__", __RDIR__ . "/" . $this->configuration["application"]["content"] . "/");
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getServices(): ServiceManager
    {
        return $this->services;
    }
}
