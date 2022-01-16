<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Driver;

use Cosmic\ORM\Abstracts\Driver;
use Cosmic\ORM\Common\QueryResult;
use Cosmic\ORM\Exceptions\QueryExecutionException;

/**
 * Represents a driver for a SQL database.
 */
class MySQLDriver extends Driver
{
    /**
     * @inheritdoc
     */
    public function execute(string $preparedQuery, array $values): QueryResult
    {
        $result = new QueryResult($preparedQuery);

        try {

            $handler = $this->getConnection()->getStream()->prepare($preparedQuery, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
            $handler->execute($values);

            $result->setLastestInsertedId($this->getConnection()->getStream()->lastInsertId());

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
