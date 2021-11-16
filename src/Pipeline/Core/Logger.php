<?php

namespace Pipeline\Core;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\Utilities\Pattern;

use function Pipeline\Kernel\app;

class Logger extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        $date = date("Y.m.d");
        $time = date("H:i:s");

        $string = Pattern::substituteTokens($message, $context, "{", "}");
        $path = new Path(ServerPath::LOGS, "$level.$date", "log");

        if ($level == LogLevel::ERROR || $level == LogLevel::EMERGENCY || $level == LogLevel::CRITICAL) {
            if (app()->getRuntimeEnvironment()->hasErrorLoggingEnabled()) {
                FileSystem::writeToDisk($path, "$time >> $string");
            }
        }

        if (app()->getRuntimeEnvironment()->hasDevelopmentLoggingEnabled()) {
            FileSystem::writeToDisk($path, "$time >> $string");
        }
    }
}
