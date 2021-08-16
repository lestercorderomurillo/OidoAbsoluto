<?php

namespace VIP\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use VIP\App\App;
use VIP\FileSystem\BasePath;
use VIP\FileSystem\FilePath;
use VIP\FileSystem\FileSystem;
use VIP\Utilities\ArrayHelper;

class Logger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        $date = date("Y.m.d");
        $time = date("H:i:s");
        $string = ArrayHelper::parameterReplace($message, $context, "{", "}");
        $path = new FilePath(BasePath::DIR_LOGS, "$level.$date", "log");

        if ($level == LogLevel::ERROR || $level == LogLevel::EMERGENCY || $level == LogLevel::CRITICAL) {
            if (App::$app->shouldLogErrorEvents()) {
                FileSystem::writeToDisk($path, "$time >> $string");
            }
        }
        
        if (App::$app->shouldLogNormalEvents()) {
            FileSystem::writeToDisk($path, "$time >> $string");
        }
    }
}
