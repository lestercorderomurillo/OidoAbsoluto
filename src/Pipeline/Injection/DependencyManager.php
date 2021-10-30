<?php

namespace Pipeline\Injection;

use Pipeline\Core\Container\Container;
use Pipeline\Core\Container\NodeContainer;

class DependencyManager
{
    private static Container $lifecycles;
    private static NodeContainer $dependencies;

    const TYPE_TRANSIENT = 1;
    const TYPE_SCOPED    = 2;

    public function __construct()
    {
        self::$lifecycles = new Container();
        self::$dependencies = new NodeContainer();
    }

    public function add(string $id, $object): void
    {
        self::$dependencies->add($object, $id);
    }

    public function addFutureVersion(int $type = 1, string $id, $object): void
    {
        self::$lifecycles->set($type, $id);
        self::$dependencies->add($object, $id);
    }

    public function getContainer(): NodeContainer
    {
        return self::$dependencies;
    }
}
