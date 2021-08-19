<?php

namespace Pipeline\Logger;

use Pipeline\App\App;
use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\Utilities\ArrayHelper;

use function Pipeline\Accessors\App;

class Logger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        $date = date("Y.m.d");
        $time = date("H:i:s");

        $string = ArrayHelper::parameterReplace($message, $context, "{", "}");
        $path = new FilePath(BasePath::DIR_LOGS, "$level.$date", "log");

        if ($level == LogLevel::ERROR || $level == LogLevel::EMERGENCY || $level == LogLevel::CRITICAL) {
            if (App()->getRuntimeEnvironment()->hasErrorLoggingEnabled()) {
                FileSystem::writeToDisk($path, "$time >> $string");
            }
        }

        if (App()->getRuntimeEnvironment()->hasDevelopmentLoggingEnabled()) {
            FileSystem::writeToDisk($path, "$time >> $string");
        }
    }
}
