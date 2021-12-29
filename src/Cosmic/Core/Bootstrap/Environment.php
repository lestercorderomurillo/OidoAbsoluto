<?php

namespace Cosmic\Core\Bootstrap;

use Cosmic\Core\Types\JSON;
use Cosmic\Utilities\Text;
use Cosmic\FileSystem\Paths\File;

/**
 * This class represents a HTTP environment. An environment
 * can hold dependencies, has configuration access and configure error handlers.
 */
abstract class Environment extends Injectable
{
    /**
     * The list of error codes that should be ignored as they are irrelevant.
     */
    private const errorIgnoreList = ["8192"];

    /**
     * @var mixed $configuration Holds all the configuration data.
     */
    private $configuration;

    /**
     * @var string $urlScheme The number of errors that has ocurred during this runtime.
     */
    private int $failureCount;

    /**
     * @var string $urlScheme The server current URL scheme.
     */
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
        set_error_handler([$this, 'handleErrors']);
        set_exception_handler([$this, 'handleExceptions']);
    }

    /**
     * Callback to convert all errors to PHP exceptions who can be easily caught in a custom error closure.
     * 
     * @return void
     * @throws \ErrorException The native php exception.
     */
    public function handleErrors($errorNumber, $errorString, $errorFile, $errorLine): void
    {
        if (!in_array("$errorNumber", self::errorIgnoreList)) {
            throw new \ErrorException($errorString, $errorNumber, $errorNumber, $errorFile, $errorLine);
        }
    }

    /**
     * Callback to handle all exceptions of the application.
     * @param \Throwable $throwable An error or an exception instance.
     * 
     * @return void
     */
    public function handleExceptions(\Throwable $throwable): void
    {
        $this->printException($throwable);
    }

    /**
     * Print a exception in the console or the browser.
     * @param \Throwable $throwable An error or an exception instance.
     * 
     * @return void
     */
    private function printException(\Throwable $exception): void
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $traces = $exception->getTrace();

        $stackTrace = "";
        $lastElement = end($traces);
        $number = count($traces);
        $time = date(DATE_RFC2822);

        foreach ($traces as $trace) {

            if ($trace == $lastElement) {
                $class = "(main)";
            } else {
                $class = isset($trace['class']) ? $trace['class'] : "(php)";
            }

            $line = isset($trace['line']) ? $trace['line'] : "?";
            $file = Text::getNamespaceBaseName(isset($trace['file']) ? $trace['file'] : "?");

            $class = strtolower($class);
            $class = str_replace("\\", ".", $class);

            if (strlen($class) > 0) {
                $class .= ".";
            }

            $parsedNumber = sprintf('%02d', $number);
            $function = Text::getNamespaceBaseName($trace['function']);
            $stackTrace .= "<div>$parsedNumber at $class$function ($file:$line)</div>";
            $number--;
        }

        die(<<<HTML
        <body style="font-family: monospace;">
            <h4>An error occurred while executing this cosmic application.</h4>
            <span>
                <div><strong>Error message: </strong>$message.</div>
                <br><hr>
                <h4>Error details</h4>
                <div><strong>Line:</strong>$line</div>
                <div><strong>File:</strong>$file</div>
                <div><strong>Internal code:</strong>$code</div>
                <br><hr>
                <h4>Callstack trace</h4>
                $stackTrace
                <br>
                <h4>At $time the application has stopped further execution.</h4>
            </span>
        </body>
        HTML);
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
            throw new \InvalidArgumentException("Unavailable configuration key: $key");
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
