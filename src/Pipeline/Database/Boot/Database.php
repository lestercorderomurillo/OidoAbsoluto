<?php

namespace Pipeline\Database\Boot;

use Pipeline\Core\Boot\Model;
use Pipeline\Core\Exceptions\SQLFailureException;
use Pipeline\Database\Driver\Driver;
use Pipeline\Database\SQL\QueryResult;
use Pipeline\Database\Common\Query;
use Pipeline\Database\Common\ConnectionString;
use function Pipeline\Kernel\fatal;

abstract class Database
{
    private array $queries;
    protected Driver $Driver;

    public abstract function find(string $model_class_name, array $where = []);
    public abstract function findAll(string $model_class_name, array $where = [], string $append = "");
    public abstract function save(Model $model): void;
    public abstract function delete(Model $model): void;
    public abstract function saveAll(array $models): void;
    public abstract function deleteAll(array $models): void;

    public function __construct(Driver $Driver, ConnectionString $connection_string)
    {
        $this->queries = [];
        $this->Driver = $Driver;
        $this->Driver->setConnectionString($connection_string);
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
        $this->Driver->openConnection();

        try {
            foreach ($this->queries as $query) {
                $temp = $this->Driver->executePDO($query->getStatement(), $query->getData());
                $results[] = $temp;
            }
        } catch (SQLFailureException $e) {
            fatal("SQL Failure: " . $e->getMessage());
        }

        $this->queries = [];
        $this->Driver->closeConnection();

        $results = (count($results) == 1) ? $results[0] : $results;

        return new QueryResult($results);
    }
}
