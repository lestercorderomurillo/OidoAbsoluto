<?php

namespace Cosmic\Database\Driver;

use Cosmic\Database\Driver\Driver;
use Cosmic\Database\Common\QueryResult;
use Cosmic\Database\Exceptions\QueryExecutionException;
use Cosmic\Database\Exceptions\UnreacheableDatabaseException;

/**
 * Represents a driver for a SQL database.
 */
class MySQLDriver extends Driver
{
    /**
     * @inheritdoc
     */
    public function openConnection(): void
    {
        try {

            $host = $this->connectionString->getHost();
            $dbName = $this->connectionString->getDatabaseName();

            $this->pdo = new \PDO(
                "mysql:host=$host;dbname=$dbName",
                $this->connectionString->getUsername(),
                $this->connectionString->getPassword()
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        } catch (\Exception $e) {
            
            throw new UnreacheableDatabaseException($e->getMessage);
        }
    }

    /**
     * @inheritdoc
     */
    public function closeConnection(): void
    {
        $this->pdo = null;
    }

    /**
     * @inheritdoc
     */
    public function executeBatch(string $preparedQuery, array $values): QueryResult
    {
        $data = [];

        try {

            $handler = $this->pdo->prepare($preparedQuery, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $handler->execute($values);
            $data = $handler->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            
            throw new QueryExecutionException($e->getMessage());
        }

        return new QueryResult($data);
    }
}
