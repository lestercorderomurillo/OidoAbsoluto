<?php

namespace Pipeline\Core\Container;

use Pipeline\Core\Boot\NodeBase;
use function Pipeline\Kernel\safe;

class NodeContainer
{
    private array $nodes;

    public function __construct(array $nodes = null)
    {
        $this->nodes = [];
        if ($nodes != null) {
            foreach ($nodes as $object) {
                $this->add($object);
            }
        }
    }

    public function get(string $id)
    {
        return safe($this->nodes[$id]);
    }

    public function set(string $id, $object): NodeContainer
    {
        $this->nodes[$id] = $object;
        return $this;
    }

    public function has(string $id): bool
    {
        return (isset($this->nodes[$id]));
    }

    public function exposeArray(): array
    {
        return $this->nodes;
    }

    public function add($object, ?string $custom_id = null): void
    {
        if ($custom_id == null) {
            if ($object instanceof NodeBase) {
                $this->set($object->getId(), $object);
            } else {
                $this->set(get_class($object), $object);
            }
        } else {
            $this->set($custom_id, $object);
        }
    }
}