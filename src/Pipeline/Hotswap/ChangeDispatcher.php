<?php

namespace Pipeline\Hotswap;

use Pipeline\Core\ResultInterface;
use Pipeline\Core\Types\JSON;
use Pipeline\Hotswap\ChangeDetector;
use Pipeline\Result\JSONResult;

use function Pipeline\Accessors\App;

class ChangeDispatcher
{
    public static function requested(string $page, string $timestamp) : ResultInterface
    {
        ChangeDetector::saveTimestamps();

        $json = new JSON(
            [
                "needUpdate" => ChangeDetector::compareTimestamps($page, $timestamp),
                "isEnabled" => App()->getRuntimeEnvironment()->hasHotswapEnabled()
            ]
        );

        return new JSONResult($json);

    }
}
