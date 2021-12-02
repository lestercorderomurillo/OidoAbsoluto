<?php

namespace Cosmic\Reload;

use Cosmic\Core\Interfaces\ResultInterface;
use Cosmic\Core\Types\JSON;
use Cosmic\Reload\ChangeDetector;
use Cosmic\Core\Result\JSONResult;
use function Cosmic\Kernel\App;

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
