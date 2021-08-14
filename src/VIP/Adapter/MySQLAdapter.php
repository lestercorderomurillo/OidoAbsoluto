<?php

namespace VIP\Adapter;

use Exception;
use VIP\Core\InternalResult;
use VIP\Adapter\Adapter;
use VIP\Factory\ResponseFactory;

class MySQLAdapter extends Adapter
{
    public function openConnection(): void
    {
        try {
            $host = $this->connection_string->getHost();
            $db_name = $this->connection_string->getDatabaseName();
            $this->pdo = new \PDO(
                "mysql:host=$host;dbname=$db_name",
                $this->connection_string->getUser(),
                $this->connection_string->getPassword()
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            ResponseFactory::createError(503);
        }
    }

    public function closeConnection(): void
    {
        $this->pdo = NULL;
    }

    public function executePDO(string $prepared_query, array $values): InternalResult
    {
        $status = InternalResult::SUCCESS;
        $result = [];
        try {
            $handler = $this->pdo->prepare($prepared_query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $handler->execute($values);
            $result = $handler->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $status = InternalResult::FAILURE;
        }

        return new InternalResult($result, $status);
    }
}
