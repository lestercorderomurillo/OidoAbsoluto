<?php

namespace Pipeline\Adapter;

use Exception;
use Pipeline\Adapter\Adapter;
use Pipeline\Database\SQL\QueryResult;
use Pipeline\Exceptions\SQLFailureException;
use Pipeline\HTTP\Server\ServerResponse;

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
            ServerResponse::create(503)->sendAndExit();
        }
    }

    public function closeConnection(): void
    {
        $this->pdo = NULL;
    }

    public function executePDO(string $prepared_query, array $values): QueryResult
    {
        $data = [];
        try {
            $handler = $this->pdo->prepare($prepared_query, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $handler->execute($values);
            $data = $handler->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new SQLFailureException($e->getMessage());
        }

        return new QueryResult($data);
    }
}
