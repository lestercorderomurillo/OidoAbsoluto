<?php

namespace VIP\Service;

use VIP\Core\ObjectContainer;

class ServiceManager
{
    private static ObjectContainer $services;

    public function __construct()
    {
        self::$services = new ObjectContainer();
    }

    public static function getContainer(): ObjectContainer
    {
        return self::$services;
    }

    public static function runForAllServices(array $functions = ["onStaticLoad", "onInstanceLoad"]): void
    {
        foreach (self::$services->expose() as $service) {
            foreach ($functions as $function) {
                if (method_exists($service, "$function")) {
                    $service->$function();
                }
            }
        }
    }
}
