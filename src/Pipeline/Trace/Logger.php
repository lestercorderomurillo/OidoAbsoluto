<?php

namespace Pipeline\Trace;

use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\Utilities\Vector;

use function Pipeline\Kernel\app;

class Logger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        $date = date("Y.m.d");
        $time = date("H:i:s");

        $string = Vector::parameterReplace($message, $context, "{", "}");
        $path = new Path(SystemPath::LOGS, "$level.$date", "log");

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
