<?php

namespace Cosmic\ORM\Driver;

use Cosmic\Utilities\Text;
use Cosmic\ORM\Bootstrap\Driver;
use Cosmic\ORM\Common\QueryResult;
use Cosmic\ORM\Exceptions\QueryExecutionException;
use Cosmic\ORM\Exceptions\UnreacheableDatabaseException;

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
    public function execute(string $preparedQuery, array $values): QueryResult
    {
        $result = new QueryResult($preparedQuery);

        try {

            $handler = $this->pdo->prepare($preparedQuery, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
            $handler->execute($values);

            $result->setLastestInsertedId($this->pdo->lastInsertId());

            if (str_starts_with($preparedQuery, "SELECT")) {

                $data = $handler->fetchAll(\PDO::FETCH_ASSOC);

                $result->setDatabaseResponse($data);
                $result->setLastestUpdatedRows(count($data));

            }

        } catch (\Exception $e) {

            throw new QueryExecutionException($e->getMessage());
        }

        return $result;
    }
}
