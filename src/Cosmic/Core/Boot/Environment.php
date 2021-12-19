<?php

namespace Cosmic\Core\Boot;

use Cosmic\Core\Types\JSON;
use Cosmic\Utilities\Text;
use Cosmic\FileSystem\Paths\File;

/**
 * This class represents a HTTP environment. An environment
 * can hold dependencies, has configuration access and configure error handlers.
 */
abstract class Environment extends DependencyContainer
{
    private $configuration;
    private int $failureCount;
    private string $urlScheme;

    /**
     * Creates a new running host environment.
     * They manage configuration, reloading, exceptions and errors.
     * 
     * @return void
     */
    protected function createHostEnvironment(): void
    {
        $this->failureCount = 0;
        $this->configuration = JSON::from(new File("app/Configuration.json"))->toArray();
    }

    /**
     * Setup the server scheme for the next request.
     * 
     * @return void
     */
    protected function setupServerScheme(): void
    {
        $this->urlScheme = "http";

        if ($this->configuration["application.https"] == true) {
            $this->urlScheme = "https";
        }

        if (!Text::endsWith($this->configuration["application.url"], "/")) {
            $this->configuration["application.url"] .= "/";
        }
    }


    /**
     * Function to manage php and exceptions.
     * 
     * @return void
     */
    protected function setupExceptionHandlers(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {

            if (Text::startsWith($errstr, "Undefined index")) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }

        });
    }

    /**
     * Gets a configuration by it's key. If not present, will throw an exception.
     * On error, this method will thrown an exception.
     * 
     * @param string $key The key you want to retrieve.
     * 
     * @return string
     * @throws InvalidArgumentException
     */
    public function getConfiguration(string $key): string
    {
        if (!isset($this->configuration[$key])) {
            throw new \InvalidArgumentException("Unavailable configuration key: $key.");
        }
        return $this->configuration[$key];
    }

    /**
     * Gets the environment assigned HTTP Scheme. It's highly recommended to always use HTTPS.
     * 
     * @return string
     */
    public function getURLScheme(): string
    {
        return $this->urlScheme;
    }

    /**
     * Checks if the application is running in production mode.
     * 
     * @return bool
     */
    public function inProductionMode(): bool
    {
        return $this->getConfiguration("application.production");
    }

    /**
     * Checks if instant reload is enabled in the environment. This function always returns false in production mode.
     * 
     * @return bool
     */
    public function hasInstantReloadEnabled(): bool
    {
        return ($this->getConfiguration("framework.reload") && !$this->inProductionMode());
    }

    /**
     * Checks if development logging is enabled. This function will be useful for debugging in development environments.
     * 
     * @return bool
     */
    public function hasDevelopmentLoggingEnabled(): bool
    {
        return $this->getConfiguration("framework.logging");
    }

    /**
     * Checks if error logging is enabled. Should not be disabled unless needed.
     * 
     * @return bool
     */
    public function hasErrorLoggingEnabled(): bool
    {
        return $this->getConfiguration("framework.errorLogging");
    }

    /**
     * Notify the environment that some errors have been encountered.
     * 
     * @return void
     */
    public function notifyFailure(): void
    {
        $this->failureCount++;
    }

    /**
     * Check if an error has been encountered previously.
     * 
     * @return bool
     */
    public function hasFailed(): bool
    {
        return ($this->failureCount > 0);
    }
}
