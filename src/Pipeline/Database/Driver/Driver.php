<?php

namespace Pipeline\Database\Driver;

use Pipeline\Database\SQL\QueryResult;
use Pipeline\Database\Common\ConnectionString;

abstract class Driver
{
    protected $pdo;
    protected ConnectionString $connection_string;

    public function setConnectionString(ConnectionString $connection_string)
    {
        $this->connection_string = $connection_string;
    }

    public abstract function openConnection(): void;
    public abstract function closeConnection(): void;
    public abstract function executePDO(string $prepared_query, array $values): QueryResult;
}
