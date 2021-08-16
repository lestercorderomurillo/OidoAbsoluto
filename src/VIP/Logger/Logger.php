<?php

namespace VIP\Logger;

use Psr\Log\AbstractLogger;
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
        $path = new FilePath(FilePath::DIR_LOGS, "$level.$date", "log");
        FileSystem::writeToDisk($path, "$time >> $string");
    }
}
