<?php

namespace Pipeline\Core;

use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\Traits\DefaultAccessorTrait;
use Pipeline\Utilities\Text;

class Environment
{
    use DefaultAccessorTrait;

    private $configuration;

    private int $failure_count;
    private string $url_scheme;

    public function __construct()
    {
        $this->failure_count = 0;

        $config_path = (new Path(ServerPath::APP, "configuration", "php"))->toString();
        $this->configuration = require_once($config_path);

        $url_scheme = "http";
        if ($this->configuration["application.https"] == true) {
            $url_scheme = "https";
        }

        $this->configuration["application.url"] = "$url_scheme://" . $this->configuration["application.url"];

        if (!Text::endsWith($this->configuration["application.url"], "/")) {
            $this->configuration["application.url"] .= "/";
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (Text::startsWith($errstr, "Undefined index")) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        });
    }

    public function getConfiguration(string $key): string
    {
        if (!isset($this->configuration[$key])) {
            throw new \InvalidArgumentException("Unavailable configuration key [$key]. Check your configuration file.");
        }
        return $this->configuration[$key];
    }

    public function inProductionMode(): bool
    {
        return $this->getConfiguration("application.production");
    }

    public function hasHotswapEnabled(): bool
    {
        return ($this->getConfiguration("development.hotswap") && !$this->inProductionMode());
    }

    public function hasDevelopmentLoggingEnabled(): bool
    {
        return $this->getConfiguration("development.logging");
    }

    public function hasErrorLoggingEnabled(): bool
    {
        return $this->getConfiguration("development.errorLogging");
    }

    public function notifyFailure(): void
    {
        $this->failure_count++;
    }

    public function hasFailed(): bool
    {
        return ($this->failure_count > 0);
    }
}
