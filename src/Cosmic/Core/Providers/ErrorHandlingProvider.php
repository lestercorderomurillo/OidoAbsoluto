<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Providers;

use Cosmic\Utilities\Strings;
use Cosmic\Core\Abstracts\AutoProvider;
use Psr\Log\LogLevel;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 */
class ErrorHandlingProvider extends AutoProvider
{
    /**
     * The list of error codes that should be ignored as they are irrelevant.
     */
    private const errorIgnoreList = ["8192"];

    /**
     * @inheritdoc
     */
    public static function boot(): void
    {
        set_error_handler(\Closure::fromCallable('static::handleErrors'));
        set_exception_handler(\Closure::fromCallable('static::handleExceptions'));
    }

    /**
     * @inheritdoc
     */
    public static function provide()
    {
        cout("ErrorHandlingProvider has finished.");
    }

    /**
     * handleErrors
     *
     * @param  mixed $errorNumber
     * @param  mixed $errorString
     * @param  mixed $errorFile
     * @param  mixed $errorLine
     * @return void
     */
    public static function handleErrors($errorNumber, $errorString, $errorFile, $errorLine): void
    {
        if (!in_array("$errorNumber", self::errorIgnoreList)) {
            throw new \ErrorException($errorString, $errorNumber, $errorNumber, $errorFile, $errorLine);
        }
    }

    /**
     * handleExceptions
     *
     * @param  mixed $throwable
     * @return void
     */
    public static function handleExceptions(\Throwable $throwable): void
    {
        if (configuration("application.production") == true) {
            die(<<<HTML
                Ha ocurrido un error, contacte al desarrollador: lestercorderomurillo@gmail.com
            HTML);
        }

        $message = $throwable->getMessage();
        $code = $throwable->getCode();
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        $traces = $throwable->getTrace();

        $stackTrace = __EMPTY__;
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
            $file = Strings::getClassBaseName(isset($trace['file']) ? $trace['file'] : "?");

            $class = strtolower($class);
            $class = strtr($class, ["\\" => "."]);

            if (strlen($class) > 0) {
                $class .= ".";
            }

            $parsedNumber = sprintf('%02d', $number);
            $function = Strings::getClassBaseName($trace['function']);
            $stackTrace .= "<div>$parsedNumber at $class$function ($file:$line)</div>";
            $number--;
        }

        if (__CONSOLE__) {

            cout("An error has occurred while processing your command.", [], LogLevel::EMERGENCY);
            cout("Error message: {0}", ["{0}" => $message], LogLevel::EMERGENCY);
            cout("Error code: {0}", ["{0}" => $code], LogLevel::EMERGENCY);

            die();

        } else {

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
    }
}
