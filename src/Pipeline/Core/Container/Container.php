<?php

namespace Pipeline\Core\Container;

use Pipeline\Core\Facade\ContainerInterface;
use function Pipeline\Kernel\safeGet;

class Container implements ContainerInterface
{

    private array $data;

    public function __construct(array $anything_array = null)
    {
        $this->data = [];
        if ($anything_array != null) {
            foreach ($anything_array as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    public function get(string $id)
    {
        return safeGet($this->data[$id]);
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

    public function &exposeArray(): array
    {
        return $this->data;
    }
}
