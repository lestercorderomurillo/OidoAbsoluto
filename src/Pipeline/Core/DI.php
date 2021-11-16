<?php

namespace Pipeline\Core;

class DI
{
    private static array $dependencies = [];

    public static function getDependency(string $id)
    {
        if (isset(self::$dependencies[$id])) {
            if (self::$dependencies[$id]["lifetime"] == Lifetime::ContextScoped) {
                $class = self::$dependencies[$id]["class"];
                return new $class(...self::$dependencies[$id]["parameters"]);
            } else if (self::$dependencies[$id]["lifetime"] == Lifetime::RequestScoped) {
                return self::$dependencies[$id]["instance"];
            }
        }
        return null;
    }

    public static function inject(int $scope, string $class, array $parameters = [], string $id = null): void
    {
        $id = ($id == null) ? $class : $id;

        if (!isset(self::$dependencies[$id])) {
            self::$dependencies[$id] = [];
        }

        self::$dependencies[$id]["lifetime"] = $scope;
        self::$dependencies[$id]["class"] = $class;
        self::$dependencies[$id]["parameters"] = $parameters;

        if ($scope == Lifetime::ContextScoped) {
            self::$dependencies[$id]["instance"] = null;
        } else if ($scope == Lifetime::RequestScoped) {
            self::$dependencies[$id]["instance"] = new $class(...$parameters);
        }
    }

    public static function autowire(string $class_name): ?object
    {
        $dependencies = [];
        $class = new \ReflectionClass($class_name);

        $constructor = $class->getConstructor();
        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {

            $type = $parameter->getType();
            $dependency = self::getDependency($type);

            if ($dependency == null) {
                die("error on injection?");
            }
            $dependencies[] = $dependency;
        }

        return new $class_name(...$dependencies);
    }
}
