<?php

namespace Pipeline\Database;

use Pipeline\Core\InternalResult;
use Pipeline\Model\Model;
use Pipeline\Adapter\Adapter;
use Pipeline\Database\Common\Query;
use Pipeline\Database\Common\ConnectionString;

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
        $this->queries[] = new Query($query, $query_data);
    }

    public function execute(): InternalResult
    {
        $results = [];
        $this->adapter->openConnection();

        foreach ($this->queries as $query) {
            $temp = $this->adapter->executePDO($query->getQuery(), $query->getData());
            if ($temp->getStatus() == InternalResult::FAILURE) {
                return new InternalResult([], InternalResult::FAILURE);
            }
            $results[] = $temp;
        }

        $this->adapter->closeConnection();
        $this->queries = [];

        if (count($results) == 1) {
            $results = $results[0];
        }

        return new InternalResult($results, InternalResult::SUCCESS);
    }

    public function commit(): InternalResult
    {
        return $this->execute();
    }

    public abstract function find(string $class_name, array $where): InternalResult;

    public abstract function save(Model $model): void;

    public abstract function delete(Model $model): void;

    public abstract function findAll(string $class_name, array $where, string $append = ""): InternalResult;

    public abstract function saveAll(array $models): void;

    public abstract function deleteAll(array $models): void;
}
