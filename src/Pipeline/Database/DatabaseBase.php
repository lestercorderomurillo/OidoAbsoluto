<?php

namespace Pipeline\Database;

use Pipeline\Adapter\Adapter;
use Pipeline\Core\Boot\ModelBase;
use Pipeline\Core\Exceptions\SQLFailureException;
use Pipeline\Database\Common\Query;
use Pipeline\Database\Common\ConnectionString;
use Pipeline\Database\SQL\QueryResult;
use function Pipeline\Kernel\fatal;

abstract class DatabaseBase
{
    private array $queries;
    protected Adapter $adapter;

    public abstract function find(string $model_class_name, array $where = []);
    public abstract function findAll(string $model_class_name, array $where = [], string $append = "");
    public abstract function save(ModelBase $model): void;
    public abstract function delete(ModelBase $model): void;
    public abstract function saveAll(array $models): void;
    public abstract function deleteAll(array $models): void;

    public function __construct(Adapter $adapter, ConnectionString $connection_string)
    {
        $this->queries = [];
        $this->adapter = $adapter;
        $this->adapter->setConnectionString($connection_string);
    }

    public function addQuery(string $query, array $query_data = []): void
    {
        $this->queries[] = new Query(trim($query), $query_data);
    }

    public function commit(): QueryResult
    {
        return $this->executeBatch();
    }

    private function executeBatch(): QueryResult
    {
        $results = [];
        $this->adapter->openConnection();

        try {
            foreach ($this->queries as $query) {
                $temp = $this->adapter->executePDO($query->getStatement(), $query->getData());
                $results[] = $temp;
            }
        } catch (SQLFailureException $e) {
            fatal("SQL Failure: " . $e->getMessage());
        }

        $this->queries = [];
        $this->adapter->closeConnection();

        $results = (count($results) == 1) ? $results[0] : $results;

        return new QueryResult($results);
    }
}
