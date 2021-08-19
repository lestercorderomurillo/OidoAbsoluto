<?php

namespace Pipeline\Database\Common;

class Query
{
    private string $statement;
    private array $data;

    public function __construct(string $statement, array $data = [])
    {
        $this->statement = trim($statement);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStatement()
    {
        return $this->statement;
    }

}
