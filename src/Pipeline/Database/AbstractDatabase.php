<?php

namespace Pipeline\Database;

use Pipeline\Core\Model;
use Pipeline\Adapter\Adapter;
use Pipeline\Database\Common\Query;
use Pipeline\Database\Common\ConnectionString;
use Pipeline\Database\SQL\QueryResult;
use Pipeline\Exceptions\SQLFailureException;
use Pipeline\HTTP\Server\ServerResponse;

abstract class AbstractDatabase
{
    private array $queries;
    protected Adapter $adapter;

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

    public function execute(): QueryResult
    {
        $results = [];
        $this->adapter->openConnection();

        try {
            foreach ($this->queries as $query) {
                $temp = $this->adapter->executePDO($query->getStatement(), $query->getData());
                $results[] = $temp;
            }
        } catch (SQLFailureException $e) {
            ServerResponse::create(500, "SQL Failure: " . $e->getMessage())->sendAndExit();
        }

        $this->adapter->closeConnection();
        $this->queries = [];

        if (count($results) == 1) {
            $results = $results[0];
        }

        return new QueryResult($results);
    }

    public function commit(): QueryResult
    {
        return $this->execute();
    }

    public abstract function find(string $model_class_name, array $where);
    public abstract function findAll(string $model_class_name, array $where, string $append = "");
    public abstract function save(Model $model): void;
    public abstract function delete(Model $model): void;
    public abstract function saveAll(array $models): void;
    public abstract function deleteAll(array $models): void;
}
