<?php

namespace Pipeline\Database\SQL;

use Pipeline\Traits\DefaultAccessorTrait;
use Pipeline\Core\Container\ContainerInterface;

class QueryResult implements ContainerInterface
{
    use DefaultAccessorTrait;

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function set(string $id, $value): void
    {
    }

    public function has(string $id): bool
    {
        return isset($this->data["$id"]);
    }

    public function get(string $id)
    {
        return $this->tryGet($this->data["$id"], NULL);
    }

    public function expose(): array
    {
        return $this->data;
    }
}
