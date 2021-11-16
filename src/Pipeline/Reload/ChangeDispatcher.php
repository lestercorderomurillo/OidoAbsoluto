<?php

namespace Pipeline\Reload;

use Pipeline\Core\Interfaces\ResultInterface;
use Pipeline\Core\Types\JSON;
use Pipeline\Reload\ChangeDetector;
use Pipeline\Core\Result\JSONResult;
use function Pipeline\Kernel\App;

class ChangeDispatcher
{
    public static function requested(string $page, string $timestamp) : ResultInterface
    {
        ChangeDetector::saveTimestamps();

        $json = new JSON(
            [
                "needUpdate" => ChangeDetector::compareTimestamps($page, $timestamp),
                "isEnabled" => app()->getRuntimeEnvironment()->hasReloadEnabled()
            ]
        );

        return new JSONResult($json);

    }
}
