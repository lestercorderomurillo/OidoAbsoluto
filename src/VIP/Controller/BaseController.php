<?php

namespace VIP\Controller;

use Psr\Log\LoggerAwareTrait;
use VIP\Core\BaseObject;
use VIP\Hotswap\ChangeDetector;

use function VIP\Core\Logger;

abstract class BaseController extends BaseObject
{
    use LoggerAwareTrait;

    private static string $_controller_name;
    private static string $controller_name;
    private static string $middleware_name;

    public function __construct($controller_name)
    {
        self::$_controller_name = $controller_name;
        self::$controller_name = $controller_name;
        $this->setLogger(Logger());
    }

    public static function setSystemController(): void
    {
        self::$controller_name = "System";
    }

    public static function setCurrentMiddleware(string $name): void
    {
        self::$middleware_name = $name;
    }

    public static function getCallbackControllerName(): string
    {
        return self::$_controller_name;
    }

    public static function getCurrentControllerName(): string
    {
        return self::$controller_name;
    }

    public static function getFQCurrentMiddlewareName(): string
    {
        return self::$middleware_name;
    }

    public static function getFQCurrentControllerName(): string
    {
        if (!isset(self::$controller_name)) {
            return self::getFQCurrentMiddlewareName();
        }
        return "App\\Controllers\\" . self::$controller_name . "Controller";
    }
}
