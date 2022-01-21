<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Cosmic\FileSystem\FS;

/**
 * The basic class used for logging.
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * Logs a new message. This will write into the file system.
     * If logging is enabled, the message will be stored as a  file in the file system.
     * 
     * @param LogLevel $level Severity of the message being transmitted.
     * @param string $message The string that will be logged. 
     * 
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $date = date("Y.m.d");
        $time = date("H:i:s");

        $message = trim(strtr("[$date-$time] $message", $context));

        if ($level == LogLevel::ERROR || $level == LogLevel::EMERGENCY || $level == LogLevel::CRITICAL) {

            echo (trim("\033[31m$message \033[0m"));

            if (configuration("framework.log.error") == true) {
                FS::write("app/Logs/stderr.$date.log", trim($message) . "\n");
            }
        } else if (configuration("framework.log.dev")) {

            if ($level == LogLevel::WARNING) {
                echo (trim("\033[33m$message \033[0m"));
            } else if ($level == LogLevel::INFO) {
                echo (trim("\033[36m$message \033[0m"));
            } else {
                echo (trim($message));
            }

            FS::write("app/Logs/stdout.$date.log", trim($message) . "\n");
        }

        echo ("\n");
    }
}
