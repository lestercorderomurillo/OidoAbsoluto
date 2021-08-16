<?php

namespace VIP\Controller;

use Psr\Log\LoggerAwareTrait;
use VIP\Core\BaseObject;

use function VIP\Core\Logger;

abstract class BaseController extends BaseObject
{
    use LoggerAwareTrait;

    private static string $controller_name;
    private static string $middleware_name;

    public function __construct($controller_name)
    {
        BaseController::$controller_name = $controller_name;
        $this->setLogger(Logger());
    }

    public static function setSystemController(): void
    {
        BaseController::$controller_name = "System";
    }

    public static function setCurrentMiddleware(string $name): void
    {
        BaseController::$middleware_name = $name;
    }

    public static function getCurrentControllerName(): string
    {
        return BaseController::$controller_name;
    }

    public static function getFQCurrentMiddlewareName(): string
    {
        return BaseController::$middleware_name;
    }

    public static function getFQCurrentControllerName(): string
    {
        if (!isset(BaseController::$controller_name)) {
            return self::getFQCurrentMiddlewareName();
        }
        return "App\\Controllers\\" . BaseController::$controller_name . "Controller";
    }
}
