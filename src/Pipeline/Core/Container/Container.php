<?php

namespace Pipeline\Core\Container;

use Pipeline\Traits\DefaultAccessorTrait;

class Container implements ContainerInterface
{
    use DefaultAccessorTrait;
    
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

    public function &set(string $id, $anything): Container
    {
        $this->data["$id"] = $anything;
        return $this;
    }

    public function has(string $id): bool
    {
        return (isset($this->data[$id]));
    }

    public function remove(string $id): void
    {
        unset($this->data[$id]);
    }

    public function &expose(): array
    {
        return $this->data;
    }
}
