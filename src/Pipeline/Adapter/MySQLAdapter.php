<?php

namespace Pipeline\Adapter;

use Exception;
use Pipeline\Core\InternalResult;
use Pipeline\Adapter\Adapter;
use Pipeline\Factory\ResponseFactory;

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
            ResponseFactory::createServerResponse(503)->sendAndDiscard();
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
            $result = ["error" => $e->getMessage()];
        }

        return new InternalResult($result, $status);
    }
}
