<?php

namespace VIP\Database;

use Psr\Log\LoggerAwareTrait;
use VIP\Core\InternalResult;
use VIP\Model\AbstractModel;
use VIP\Service\AbstractService;
use VIP\Adapter\Adapter;
use VIP\Database\Common\Query;
use VIP\Database\Common\ConnectionString;

use function VIP\Core\Logger;

abstract class AbstractDatabase extends AbstractService
{
    use LoggerAwareTrait;
    protected Adapter $adapter;
    private array $queries;

    public function __construct(Adapter $adapter, ConnectionString $connection_string, string $service_id)
    {
        parent::__construct($service_id);
        $this->adapter = $adapter;
        $this->adapter->setConnectionString($connection_string);
        $this->queries = [];
        $this->setLogger(Logger());
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

    public abstract function save(AbstractModel $model): void;

    public abstract function delete(AbstractModel $model): void;

    public abstract function findAll(string $class_name, array $where, string $append): InternalResult;

    public abstract function saveAll(array $models): void;

    public abstract function deleteAll(array $models): void;
}
