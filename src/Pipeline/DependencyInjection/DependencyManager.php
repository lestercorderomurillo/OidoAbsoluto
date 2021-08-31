<?php

namespace Pipeline\DependencyInjection;

use Pipeline\Core\Container\Container;
use Pipeline\Core\Container\ObjectContainer;

class DependencyManager
{
    private static Container $lifecycles;
    private static ObjectContainer $dependencies;

    const TYPE_TRANSIENT = 1;
    const TYPE_SCOPED    = 2;

    public function __construct()
    {
        self::$lifecycles = new Container();
        self::$dependencies = new ObjectContainer();
    }

    public function add(string $id, $object): void
    {
        self::$dependencies->add($object, $id);
    }

    public function addFutureVersion(int $type, string $id, $object): void
    {
        self::$lifecycles->set($type, $id);
        self::$dependencies->add($object, $id);
    }

    public function getContainer(): ObjectContainer
    {
        return self::$dependencies;
    }
}
