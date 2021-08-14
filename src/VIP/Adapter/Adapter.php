<?php

namespace VIP\Adapter;

use VIP\Core\BaseObject;
use VIP\Core\InternalResult;
use VIP\Database\Common\ConnectionString;

abstract class Adapter extends BaseObject
{
    protected $pdo;
    protected ConnectionString $connection_string;

    public function setConnectionString(ConnectionString $connection_string)
    {
        $this->connection_string = $connection_string;
    }

    public abstract function openConnection(): void;
    public abstract function closeConnection(): void;
    public abstract function executePDO(string $prepared_query, array $values): InternalResult;
}
