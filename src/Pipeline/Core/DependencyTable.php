<?php

namespace Pipeline\Core;

use Pipeline\Core\Exceptions\UnavailableDependencyException;

class DependencyTable
{
    const MemoryScoped  = 1;
    const RequestScoped = 2;
    const ContextScoped = 3;

    private static array $dependencies = [];

    public static function getDependency(string $id)
    {
        if (isset(self::$dependencies[$id])) {
            if (self::$dependencies[$id]["lifetime"] == self::ContextScoped) {
                $class = self::$dependencies[$id]["class"];
                return new $class(...self::$dependencies[$id]["parameters"]);
            } else if (self::$dependencies[$id]["lifetime"] == self::RequestScoped) {
                return self::$dependencies[$id]["instance"];
            }
        }
        return null;
    }

    public static function addInjectable(int $scope, string $class, array $parameters = [], string $id = null): void
    {
        $id = ($id == null) ? $class : $id;

        if (!isset(self::$dependencies[$id])) {
            self::$dependencies[$id] = [];
        }

        self::$dependencies[$id]["lifetime"] = $scope;
        self::$dependencies[$id]["class"] = $class;
        self::$dependencies[$id]["parameters"] = $parameters;

        if ($scope == self::ContextScoped) {
            self::$dependencies[$id]["instance"] = null;
        } else if ($scope == self::RequestScoped) {
            self::$dependencies[$id]["instance"] = new $class(...$parameters);
        }
    }
}
