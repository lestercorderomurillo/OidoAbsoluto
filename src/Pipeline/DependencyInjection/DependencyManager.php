<?php

namespace Pipeline\DependencyInjection;

use Pipeline\Core\Container\ObjectContainer;

class DependencyManager
{
    private static ObjectContainer $dependencies;

    public function __construct()
    {
        self::$dependencies = new ObjectContainer();
    }

    public function add(string $id, $object): void
    {
        self::$dependencies->add($object, $id);
    }

    public function getContainer(): ObjectContainer
    {
        return self::$dependencies;
    }
}
