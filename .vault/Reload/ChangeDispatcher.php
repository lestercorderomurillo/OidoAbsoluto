<?php

namespace Cosmic\Reload;

use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\Core\Types\JSON;
use Cosmic\Reload\ChangeDetector;
use Cosmic\Core\Result\JSONResult;
use function Cosmic\Kernel\App;

class ChangeDispatcher
{
    public static function requested(string $page, string $timestamp) : ResultGeneratorInterface
    {
        ChangeDetector::saveTimestamps();

        $json = new JSON(
            [
                "needUpdate" => ChangeDetector::compareTimestamps($page, $timestamp),
                "isEnabled" => app()->getRuntimeEnvironment()->hasInstantReloadEnabled()
            ]
        );

        return new JSONResult($json);

    }
}
