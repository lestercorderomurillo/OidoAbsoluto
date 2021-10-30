<?php

namespace Pipeline\Database\SQL;

use Pipeline\Core\Facade\ContainerInterface;
use Pipeline\Core\Exceptions\ReadOnlyException;
use function Pipeline\Kernel\safe;

class QueryResult implements ContainerInterface
{
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
        return safe($this->data["$id"], null);
    }

    public function exposeArray()
    {
        return $this->data;
    }
}
