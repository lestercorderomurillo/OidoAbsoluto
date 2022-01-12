<?php

namespace Cosmic\Core\Bootstrap;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Utilities\Pattern;

/**
 * The basic class used for logging.
 */
class Logger extends AbstractLogger
{
    /**
     * Logs a new message. This will write into the file system.
     * If logging is enabled, the message will be stored as a  file in the file system.
     * 
     * @param LogLevel $level Severity of the message being transmitted.
     * @param string $message The string that will be logged. Accepts tokens as in {number} format, starting from 0.
     * @param mixed[] $context Tokens that will be replaced. This argument is optional.
     * 
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $date = date("Y.m.d");
        $time = date("H:i:s");

        $message = vsprintf($message, $context);
        
        $file = new File("app/Logs/$level.$date.log");

        if ($level == LogLevel::ERROR || $level == LogLevel::EMERGENCY || $level == LogLevel::CRITICAL) {
            
            if (app()->hasErrorLoggingEnabled()) {
                FileSystem::write($file, "$time >> $message");
            }

        }

        if (app()->hasDevelopmentLoggingEnabled()) {
            FileSystem::write($file, "$time >> $message");
        }
    }
}
