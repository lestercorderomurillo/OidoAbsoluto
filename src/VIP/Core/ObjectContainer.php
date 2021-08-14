<?php

namespace VIP\Core;

use VIP\Core\BaseObject;

class ObjectContainer extends BaseObject implements ContainerInterface
{
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

    public function set(string $id, $object): void
    {
        $this->objects[$id] = $object;
    }

    public function has(string $id): bool
    {
        return (isset($this->objects[$id]));
    }

    public function expose(): array
    {
        return $this->objects;
    }

    public function add(BaseObject $object): void
    {
        $this->set($object->getObjectID(), $object);
    }
}
