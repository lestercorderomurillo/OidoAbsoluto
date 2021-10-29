<?php

namespace Pipeline\Database\SQL;

use Pipeline\Core\Facade\ContainerInterface;
use Pipeline\Traits\DefaultAccessorTrait;
use Pipeline\Core\Exceptions\ReadOnlyException;

class QueryResult implements ContainerInterface
{
    use DefaultAccessorTrait;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function set(string $id, $value): QueryResult
    {
        throw new ReadOnlyException();
    }

    public function has(string $id): bool
    {
        return isset($this->data["$id"]);
    }

    public function get(string $id)
    {
        return $this->tryGet($this->data["$id"], null);
    }

    public function exposeArray()
    {
        return $this->data;
    }
}
