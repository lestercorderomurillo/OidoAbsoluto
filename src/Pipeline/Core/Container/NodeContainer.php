<?php

namespace Pipeline\Core\Container;

use Pipeline\Core\Node;
use function Pipeline\Kernel\safeGet;

class NodeContainer
{
    private array $objects;

    public function __construct(array $objects = null)
    {
        $this->objects = [];
        if ($objects != null) {
            foreach ($objects as $object) {
                $this->add($object);
            }
        }
    }

    public function get(string $id)
    {
        return safeGet($this->objects[$id]);
    }

    public function set(string $id, $object): NodeContainer
    {
        $this->objects[$id] = $object;
        return $this;
    }

    public function has(string $id): bool
    {
        return (isset($this->objects[$id]));
    }

    public function exposeArray(): array
    {
        return $this->objects;
    }

    public function add($object, ?string $custom_id = null): void
    {
        if ($custom_id == null) {
            if ($object instanceof Node) {
                $this->set($object->getId(), $object);
            } else {
                $this->set(get_class($object), $object);
            }
        } else {
            $this->set($custom_id, $object);
        }
    }
}