<?php

namespace VIP\Core;

abstract class BaseController extends BaseObject
{
    private static string $controller_name;

    public function __construct($controller_name)
    {
        BaseController::$controller_name = $controller_name;
    }

    public static function setSystemController(){
        BaseController::$controller_name = "System";
    }

    public static function getCurrentControllerName(){
        return BaseController::$controller_name;
    }
}
