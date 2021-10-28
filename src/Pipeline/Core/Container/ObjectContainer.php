<?php

namespace Pipeline\Core\Container;

use Pipeline\Core\Node;
use Pipeline\Traits\DefaultAccessorTrait;

class ObjectContainer
{
    use DefaultAccessorTrait;

    private array $objects;

    public function __construct(array $array_base_objects = NULL)
    {
        $this->objects = [];
        if ($array_base_objects != NULL) {
            foreach ($array_base_objects as $object) {
                $this->add($object);
            }
        }
    }

    public function get(string $id)
    {
        return $this->tryGet($this->objects[$id]);
    }

    public function set(string $id, $object): ObjectContainer
    {
        $this->objects[$id] = $object;
        return $this;
    }

    public function has(string $id): bool
    {
        return (isset($this->objects[$id]));
    }

    public function expose(): array
    {
        return $this->objects;
    }

    public function add($object, string $forced_id = ""): void
    {
        if ($forced_id == "") {
            if ($object instanceof Node) {
                $this->set($object->getId(), $object);
            } else {
                $this->set(get_class($object), $object);
            }
        } else {
            $this->set($forced_id, $object);
        }
    }
}

/*
namespace Pipeline\Core\Container;

use Pipeline\Core\Node;
use Pipeline\Core\Interface\ContainerInterface;
use Pipeline\Traits\DefaultAccessorTrait;

class ObjectContainer implements ContainerInterface
{
    use DefaultAccessorTrait;

    private array $objects;

    public function __construct(array $array_base_objects = NULL)
    {
        $this->objects = [];
        if ($array_base_objects != NULL) {
            foreach ($array_base_objects as $object) {
                $this->add($object);
            }
        }
    }

    public function get(string $id)
    {
        return $this->tryGet($this->objects[$id]);
    }

    public function set(string $id, $object): ObjectContainer
    {
        $this->objects[$id] = $object;
        return $this;
    }

    public function has(string $id): bool
    {
        return (isset($this->objects[$id]));
    }

    public function expose(): array
    {
        return $this->objects;
    }

    public function add($object, string $forced_id = ""): void
    {
        if ($forced_id == "") {
            if ($object instanceof Node) {
                $this->set($object->getId(), $object);
            } else {
                $this->set(get_class($object), $object);
            }
        }else{
            $this->set($forced_id, $object);
        }
    }
}
*/