<?php

namespace VIP\Hotswap;

use VIP\App\App;
use VIP\Hotswap\ChangeDetector;
use VIP\HTTP\Server\Response\JSON;

class VM
{
    public static function requested(string $page, string $timestamp)
    {
        ChangeDetector::saveTimestamps();

        return (new JSON(
            [
                "needUpdate" => ChangeDetector::compareTimestamps($page, $timestamp),
                "isEnabled" => App::$app->hasHotswapEnabled()
            ]
        ))->toString();
    }
}
