<?php

namespace VIP\Core;

use VIP\Core\BaseObject;

class Container extends BaseObject implements ContainerInterface
{
    private array $data;

    public function __construct(array $anything_array = NULL)
    {
        $this->data = [];
        if ($anything_array != NULL) {
            foreach ($anything_array as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    public function get(string $id)
    {
        return $this->tryGet($this->data[$id]);
    }

    public function set(string $id, $object): void
    {
        $this->data[$id] = $object;
    }

    public function has(string $id): bool
    {
        return (isset($this->data[$id]));
    }

    public function expose(): array
    {
        return $this->data;
    }
}
